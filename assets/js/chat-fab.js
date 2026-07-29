/**
 * SYN Ownd Child – Lumin Coco
 * オリジナルチャットモーダル — FAB トリガー + Dify API 経由送受信
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    var dock         = document.getElementById('lcChatDock');
    var fab          = document.getElementById('lcChatFab');
    var label        = document.getElementById('lcChatLabel');
    var labelClose   = document.getElementById('lcChatLabelClose');
    var modal        = document.getElementById('lcChatModal');
    var overlay      = document.getElementById('lcChatModalOverlay');
    var closeBtn     = document.getElementById('lcChatModalClose');
    var messages     = document.getElementById('lcChatMessages');
    var input        = document.getElementById('lcChatInput');
    var sendBtn      = document.getElementById('lcChatSend');

    if (!dock || !fab) { return; }

    var cfg            = window.lcChat || {};
    var conversationId = sessionStorage.getItem('lcChatConvId') || '';
    var isLoading      = false;

    // 1.2 秒後に吹き出しを表示
    setTimeout(function () { dock.classList.add('is-open'); }, 1200);

    /* ---- モーダル開閉 ---- */
    function openModal() {
      if (!modal) {
        if (cfg.fallback) { window.location.href = cfg.fallback; }
        return;
      }
      modal.removeAttribute('hidden');
      // 次フレームでアニメーションを開始（hidden 解除直後だとトランジションが飛ぶ）
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          modal.classList.add('is-open');
          if (input) { input.focus(); }
        });
      });
      dock.classList.remove('is-open');
    }

    function closeModal() {
      if (!modal) { return; }
      modal.classList.remove('is-open');
      setTimeout(function () { modal.setAttribute('hidden', ''); }, 300);
    }

    /* ---- 簡易 Markdown → HTML 変換（AI応答の整形表示用） ---- */
    function escapeHtml(str) {
      return str.replace(/[&<>"']/g, function (ch) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
      });
    }

    function parseMarkdownTableRow(line) {
      var trimmed = line.trim().replace(/^\|/, '').replace(/\|$/, '');
      return trimmed.split('|').map(function (c) { return c.trim(); });
    }

    function renderMarkdown(text) {
      var html = escapeHtml(text);

      // コードブロック ```...```
      html = html.replace(/```([\s\S]*?)```/g, function (m, code) {
        return '<pre><code>' + code.replace(/^\n+|\n+$/g, '') + '</code></pre>';
      });
      // インラインコード `...`
      html = html.replace(/`([^`\n]+)`/g, '<code>$1</code>');
      // 見出し
      html = html.replace(/^### (.*)$/gm, '<h4>$1</h4>');
      html = html.replace(/^## (.*)$/gm, '<h3>$1</h3>');
      html = html.replace(/^# (.*)$/gm, '<h2>$1</h2>');
      // 太字 / イタリック
      html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
      html = html.replace(/(^|[^*])\*([^*\n]+)\*(?!\*)/g, '$1<em>$2</em>');
      // 画像 ![alt](url)（リンク記法より先に処理しないと ![...] の [...] 部分がリンクとして誤認識される）
      html = html.replace(/!\[([^\]]*)\]\((https?:\/\/[^\s)]+)\)/g, '<img src="$2" alt="$1" loading="lazy" />');
      // リンク [text](url)
      html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
      // 表組み（| a | b |\n| --- | --- |\n| c | d |）
      html = html.replace(/(?:^|\n)(\|[^\n]*\|[ \t]*\n\|[ \t:\-|]+\|[ \t]*\n(?:\|[^\n]*\|[ \t]*(?:\n|$))*)/g, function (m, block) {
        var lines = block.replace(/\n$/, '').split('\n').filter(function (l) { return l.trim() !== ''; });
        var headerCells = parseMarkdownTableRow(lines[0]);
        var rows = lines.slice(2).map(parseMarkdownTableRow);
        var thead = '<thead><tr>' + headerCells.map(function (c) { return '<th>' + c + '</th>'; }).join('') + '</tr></thead>';
        var tbody = '<tbody>' + rows.map(function (r) {
          return '<tr>' + r.map(function (c) { return '<td>' + c + '</td>'; }).join('') + '</tr>';
        }).join('') + '</tbody>';
        return '\n<table>' + thead + tbody + '</table>\n';
      });
      // 箇条書き（連続する - / * 行を <ul> にまとめる）
      html = html.replace(/(?:^|\n)((?:[-*] .*(?:\n|$))+)/g, function (m, block) {
        var items = block.replace(/\n$/, '').split('\n').map(function (line) {
          return '<li>' + line.replace(/^[-*]\s+/, '') + '</li>';
        }).join('');
        return '\n<ul>' + items + '</ul>\n';
      });
      // 番号付きリスト
      html = html.replace(/(?:^|\n)((?:\d+\. .*(?:\n|$))+)/g, function (m, block) {
        var items = block.replace(/\n$/, '').split('\n').map(function (line) {
          return '<li>' + line.replace(/^\d+\.\s+/, '') + '</li>';
        }).join('');
        return '\n<ol>' + items + '</ol>\n';
      });
      // 段落・改行の整形（ul/ol/h2-4/pre/table のブロックは <p> で囲まない）
      html = html.split(/(<(?:ul|ol|h2|h3|h4|pre|table)[\s\S]*?<\/(?:ul|ol|h2|h3|h4|pre|table)>)/g).map(function (part) {
        if (/^<(ul|ol|h2|h3|h4|pre|table)/.test(part)) { return part; }
        return part.split(/\n{2,}/).map(function (block) {
          block = block.replace(/^\n+|\n+$/g, '');
          return block === '' ? '' : '<p>' + block.replace(/\n/g, '<br>') + '</p>';
        }).join('');
      }).join('');

      return html;
    }

    /* ---- メッセージ追加 ---- */
    function appendMessage(text, role) {
      var wrap   = document.createElement('div');
      wrap.className = 'lc-chatmsg lc-chatmsg--' + role;
      var bubble = document.createElement('div');
      bubble.className = 'lc-chatmsg__bubble';
      if (role === 'ai') {
        // renderMarkdown が万一失敗しても、無反応にはせずプレーンテキストで表示する。
        try {
          bubble.innerHTML = renderMarkdown(String(text == null ? '' : text));
        } catch (e) {
          console.error('[LC Chat] renderMarkdown failed, falling back to plain text', e);
          bubble.textContent = String(text == null ? '' : text);
        }
      } else {
        bubble.textContent = text;
      }
      wrap.appendChild(bubble);
      messages.appendChild(wrap);
      messages.scrollTop = messages.scrollHeight;
    }

    function appendLoading() {
      var wrap   = document.createElement('div');
      wrap.className = 'lc-chatmsg lc-chatmsg--ai';
      wrap.innerHTML =
        '<div class="lc-chatmsg__bubble">' +
          '<span class="lc-chatmsg__dots">' +
            '<span></span><span></span><span></span>' +
          '</span>' +
        '</div>';
      messages.appendChild(wrap);
      messages.scrollTop = messages.scrollHeight;
      return wrap;
    }

    /* ---- 送信 ---- */
    function sendMessage() {
      if (isLoading || !input) { return; }
      var query = input.value.trim();
      if (!query) { return; }

      appendMessage(query, 'user');
      input.value = '';
      input.style.height = '';
      isLoading = true;
      if (sendBtn) { sendBtn.disabled = true; }

      var loader = appendLoading();
      // then/catch 双方から呼ばれ得るため、二重削除で例外にならないようガードする。
      function removeLoader() {
        if (loader && loader.parentNode) { loader.parentNode.removeChild(loader); }
      }

      var fd = new FormData();
      fd.append('action',          'lc_dify_chat');
      fd.append('nonce',           cfg.nonce || '');
      fd.append('query',           query);
      fd.append('conversation_id', conversationId);

      fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body:   fd,
        credentials: 'same-origin'
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          removeLoader();
          // 管理者ログイン時のみ data.data.debug が付与される（一般訪問者には出ない）。
          if (data.data && data.data.debug) {
            console.log('[LC Dify Chat debug]', data.data.debug);
          }
          if (data.success) {
            appendMessage(data.data.answer, 'ai');
            if (data.data.conversation_id) {
              conversationId = data.data.conversation_id;
              sessionStorage.setItem('lcChatConvId', conversationId);
            }
          } else {
            var errorText = (data.data && data.data.message) || data.data || 'エラーが発生しました。しばらくしてから再度お試しください。';
            appendMessage(errorText, 'ai');
          }
        })
        .catch(function (err) {
          console.error('[LC Chat] request failed', err);
          removeLoader();
          appendMessage('通信エラーが発生しました。しばらくしてから再度お試しください。', 'ai');
        })
        .finally(function () {
          isLoading = false;
          if (sendBtn) { sendBtn.disabled = false; }
          if (input) { input.focus(); }
        });
    }

    /* ---- イベント ---- */
    fab.addEventListener('click', openModal);

    if (label) {
      label.addEventListener('click', function (e) {
        if (labelClose && (e.target === labelClose || labelClose.contains(e.target))) {
          e.stopPropagation();
          dock.classList.remove('is-open');
          return;
        }
        openModal();
      });
    }

    if (overlay)  { overlay.addEventListener('click', closeModal); }
    if (closeBtn) { closeBtn.addEventListener('click', closeModal); }
    if (sendBtn)  { sendBtn.addEventListener('click', sendMessage); }

    if (input) {
      // textarea 自動伸縮
      input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
      });
      // Enter 送信 / Shift+Enter 改行
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          sendMessage();
        }
      });
    }

    // Escape で閉じる
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) {
        closeModal();
      }
    });
  });
})();
