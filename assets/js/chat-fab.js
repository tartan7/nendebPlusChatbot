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

    /* ---- メッセージ追加 ---- */
    function appendMessage(text, role) {
      var wrap   = document.createElement('div');
      wrap.className = 'lc-chatmsg lc-chatmsg--' + role;
      var bubble = document.createElement('div');
      bubble.className = 'lc-chatmsg__bubble';
      bubble.textContent = text;
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
          messages.removeChild(loader);
          if (data.success) {
            appendMessage(data.data.answer, 'ai');
            if (data.data.conversation_id) {
              conversationId = data.data.conversation_id;
              sessionStorage.setItem('lcChatConvId', conversationId);
            }
          } else {
            appendMessage(
              data.data || 'エラーが発生しました。しばらくしてから再度お試しください。',
              'ai'
            );
          }
        })
        .catch(function () {
          messages.removeChild(loader);
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
