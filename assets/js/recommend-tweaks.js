/* =============================================================
 * SYN Ownd Child — 副読本 (Recommend) Tweaks Panel
 * ------------------------------------------------------------
 * フォント・行間・コンテナ幅などをライブで試すための
 * 簡易パネル。recommend.css 内の CSS 変数（.rec-shell 配下）を
 * 書き換えるシンプルな実装。
 *
 * - 親フレームの「Tweaks」トグルから activate/deactivate を受領
 * - 値は localStorage に保存（ページリロードで復元）
 * - 親への disk 永続化（__edit_mode_set_keys）には依存しない
 * ============================================================= */
(function () {
  'use strict';

  if (window.__recTweaksLoaded) return;
  window.__recTweaksLoaded = true;

  /* ---------- 0. 既定値 ---------- */
  var DEFAULTS = {
    containerMax:   760,                 // px
    bodyFs:         15,                  // px
    bodyLh:         1.95,                // unitless
    displayFont:    'serif',             // serif | sans
    bodyFont:       'sans',              // sans  | serif
    sectionBg:      'white',             // white | offwhite | none
    sectionRadius:  12                   // px
  };

  var STORAGE_KEY = 'lc-rec-tweaks-v1';

  /* ---------- 1. ストレージ ---------- */
  function loadState() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return Object.assign({}, DEFAULTS);
      var v = JSON.parse(raw);
      return Object.assign({}, DEFAULTS, v);
    } catch (e) {
      return Object.assign({}, DEFAULTS);
    }
  }
  function saveState(s) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(s)); } catch (e) {}
  }

  /* ---------- 2. 値 → CSS 反映 ---------- */
  function applyState(s) {
    var shell = document.querySelector('.rec-shell') || document.documentElement;
    var SERIF = '"Noto Serif JP", "Yu Mincho", "Hiragino Mincho ProN", serif';
    var SANS  = '"Noto Sans JP", "Hiragino Kaku Gothic ProN", "Yu Gothic", sans-serif';

    shell.style.setProperty('--rec-container-max',  s.containerMax + 'px');
    shell.style.setProperty('--rec-body-fs',        s.bodyFs + 'px');
    shell.style.setProperty('--rec-body-lh',        String(s.bodyLh));
    shell.style.setProperty('--rec-display-font',   s.displayFont === 'sans' ? SANS : SERIF);
    shell.style.setProperty('--rec-body-font',      s.bodyFont    === 'serif' ? SERIF : SANS);
    shell.style.setProperty('--rec-section-radius', s.sectionRadius + 'px');

    var bg = '#ffffff';
    if (s.sectionBg === 'offwhite') bg = '#FAFCF8';
    else if (s.sectionBg === 'none') bg = 'transparent';
    shell.style.setProperty('--rec-section-bg', bg);
  }

  /* ---------- 3. パネル UI ---------- */
  var panel = null;
  var state = loadState();

  function h(tag, attrs, children) {
    var el = document.createElement(tag);
    if (attrs) {
      Object.keys(attrs).forEach(function (k) {
        if (k === 'class') el.className = attrs[k];
        else if (k === 'html') el.innerHTML = attrs[k];
        else if (k.indexOf('on') === 0 && typeof attrs[k] === 'function') {
          el.addEventListener(k.slice(2), attrs[k]);
        } else if (attrs[k] != null) {
          el.setAttribute(k, attrs[k]);
        }
      });
    }
    (Array.isArray(children) ? children : (children == null ? [] : [children]))
      .forEach(function (c) {
        if (c == null) return;
        el.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
      });
    return el;
  }

  function buildSegmented(name, options) {
    var wrap = h('div', { class: 'rec-tweaks__seg', role: 'radiogroup', 'aria-label': name });
    options.forEach(function (opt) {
      var btn = h('button', {
        type: 'button',
        role: 'radio',
        'data-val': String(opt.value),
        'aria-checked': String(state[name] == opt.value),
        class: 'rec-tweaks__seg-btn' + (state[name] == opt.value ? ' is-active' : ''),
        onclick: function () {
          state[name] = (typeof opt.value === 'number') ? Number(opt.value) : opt.value;
          wrap.querySelectorAll('button').forEach(function (b) {
            var on = b.getAttribute('data-val') === String(opt.value);
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-checked', String(on));
          });
          commit();
        }
      }, opt.label);
      wrap.appendChild(btn);
    });
    return wrap;
  }

  function buildSlider(name, min, max, step, suffix) {
    var input = h('input', {
      type: 'range', min: String(min), max: String(max), step: String(step),
      value: String(state[name]),
      'aria-label': name,
      oninput: function () {
        state[name] = parseFloat(input.value);
        value.textContent = formatVal(state[name], suffix);
        commit();
      }
    });
    var value = h('span', { class: 'rec-tweaks__val' }, formatVal(state[name], suffix));
    var row = h('div', { class: 'rec-tweaks__slider' }, [input, value]);
    return row;
  }

  function formatVal(v, suffix) {
    if (suffix === 'px') return Math.round(v) + 'px';
    return (Math.round(v * 100) / 100).toString();
  }

  function buildField(label, control) {
    return h('div', { class: 'rec-tweaks__field' }, [
      h('label', { class: 'rec-tweaks__label' }, label),
      control
    ]);
  }

  function commit() {
    saveState(state);
    applyState(state);
  }

  function reset() {
    state = Object.assign({}, DEFAULTS);
    saveState(state);
    applyState(state);
    // re-render to refresh control states
    if (panel) {
      var was = panel.classList.contains('is-open');
      panel.remove();
      panel = null;
      build();
      if (was) panel.classList.add('is-open');
    }
  }

  function build() {
    if (panel) return panel;

    var head = h('div', { class: 'rec-tweaks__head' }, [
      h('span', { class: 'rec-tweaks__title' }, 'Tweaks'),
      h('button', {
        type: 'button',
        class: 'rec-tweaks__close',
        'aria-label': '閉じる',
        onclick: function () {
          panel.classList.remove('is-open');
          try { window.parent.postMessage({ type: '__edit_mode_dismissed' }, '*'); } catch (e) {}
        }
      }, '×')
    ]);

    var body = h('div', { class: 'rec-tweaks__body' }, [
      buildField('コンテナ幅', buildSegmented('containerMax', [
        { value: 720, label: '720' },
        { value: 760, label: '760' },
        { value: 840, label: '840' },
        { value: 920, label: '920' }
      ])),

      buildField('本文サイズ', buildSlider('bodyFs', 13, 19, 0.5, 'px')),
      buildField('行間（line-height）', buildSlider('bodyLh', 1.5, 2.3, 0.05)),

      buildField('見出しフォント', buildSegmented('displayFont', [
        { value: 'serif', label: '明朝' },
        { value: 'sans',  label: 'ゴシック' }
      ])),

      buildField('本文フォント', buildSegmented('bodyFont', [
        { value: 'sans',  label: 'ゴシック' },
        { value: 'serif', label: '明朝' }
      ])),

      buildField('セクション背景', buildSegmented('sectionBg', [
        { value: 'white',    label: '白' },
        { value: 'offwhite', label: 'オフ白' },
        { value: 'none',     label: '透明' }
      ])),

      buildField('セクション角丸', buildSlider('sectionRadius', 0, 20, 1, 'px'))
    ]);

    var foot = h('div', { class: 'rec-tweaks__foot' }, [
      h('button', {
        type: 'button',
        class: 'rec-tweaks__reset',
        onclick: reset
      }, '既定値に戻す'),
      h('span', { class: 'rec-tweaks__hint' }, 'ブラウザに保存（このページのみ）')
    ]);

    panel = h('aside', {
      class: 'rec-tweaks',
      role: 'dialog',
      'aria-label': '副読本デザイン Tweaks',
      'aria-modal': 'false'
    }, [head, body, foot]);

    document.body.appendChild(panel);
    return panel;
  }

  /* ---------- 4. スタイル注入（外部CSSを増やさない） ---------- */
  function injectCSS() {
    if (document.getElementById('rec-tweaks-styles')) return;
    var css = ''
      + '.rec-tweaks{ position:fixed; right:100px; bottom:24px; width:336px; max-width:calc(100vw - 48px);'
      + ' background:#fff; border:1px solid #DDE5D6; border-radius:12px;'
      + ' box-shadow:0 16px 40px rgba(8,89,63,.18), 0 2px 6px rgba(8,89,63,.08);'
      + ' z-index:60; display:none; flex-direction:column;'
      + ' font-family:"Noto Sans JP","Hiragino Kaku Gothic ProN","Yu Gothic",sans-serif;'
      + ' color:#1F2A23; }'
      + '.rec-tweaks.is-open{ display:flex; }'
      + '.rec-tweaks__head{ display:flex; align-items:center; justify-content:space-between;'
      + ' padding:12px 16px; border-bottom:1px solid #EDF1E8; background:#FAFCF8; border-radius:12px 12px 0 0; }'
      + '.rec-tweaks__title{ font-family:"Noto Serif JP",serif; font-size:14px; font-weight:600;'
      + ' letter-spacing:.16em; color:#08593F; }'
      + '.rec-tweaks__close{ width:28px; height:28px; border:none; background:transparent;'
      + ' font-size:22px; line-height:1; color:#6B7287; cursor:pointer; border-radius:6px; }'
      + '.rec-tweaks__close:hover{ background:#EAF1E5; color:#08593F; }'
      + '.rec-tweaks__close:focus-visible{ outline:none; box-shadow:0 0 0 3px rgba(178,88,64,.28); }'
      + '.rec-tweaks__body{ padding:14px 16px 6px; display:grid; gap:14px;'
      + ' max-height:calc(100vh - 240px); overflow:auto; }'
      + '.rec-tweaks__field{ display:flex; flex-direction:column; gap:6px; }'
      + '.rec-tweaks__label{ font-size:11px; font-weight:600; color:#08593F; letter-spacing:.08em; }'
      + '.rec-tweaks__seg{ display:flex; gap:4px; background:#F4F7F1; padding:3px; border-radius:8px; }'
      + '.rec-tweaks__seg-btn{ flex:1 1 auto; padding:6px 8px; border:none; background:transparent;'
      + ' font:600 12px/1.4 inherit; color:#3D5446; cursor:pointer; border-radius:6px;'
      + ' letter-spacing:.04em; transition:background .15s ease, color .15s ease; }'
      + '.rec-tweaks__seg-btn.is-active{ background:#0F825D; color:#fff; }'
      + '.rec-tweaks__seg-btn:hover:not(.is-active){ background:#fff; color:#1F2A23; }'
      + '.rec-tweaks__seg-btn:focus-visible{ outline:none; box-shadow:0 0 0 2px rgba(178,88,64,.28); }'
      + '.rec-tweaks__slider{ display:grid; grid-template-columns:1fr 56px; gap:8px; align-items:center; }'
      + '.rec-tweaks__slider input[type=range]{ width:100%; accent-color:#0F825D; }'
      + '.rec-tweaks__val{ font-family:ui-monospace,Menlo,Consolas,monospace; font-size:12px;'
      + ' color:#08593F; text-align:right; font-variant-numeric:tabular-nums; }'
      + '.rec-tweaks__foot{ display:flex; align-items:center; justify-content:space-between;'
      + ' padding:10px 16px 12px; border-top:1px solid #EDF1E8; background:#FAFCF8;'
      + ' border-radius:0 0 12px 12px; }'
      + '.rec-tweaks__reset{ padding:6px 12px; font:600 12px/1.4 inherit; color:#08593F;'
      + ' background:#fff; border:1px solid #BFCDB4; border-radius:6px; cursor:pointer;'
      + ' letter-spacing:.04em; }'
      + '.rec-tweaks__reset:hover{ background:#EAF1E5; }'
      + '.rec-tweaks__reset:focus-visible{ outline:none; box-shadow:0 0 0 3px rgba(178,88,64,.28); }'
      + '.rec-tweaks__hint{ font-size:11px; color:#6B7287; }'
      + '@media (max-width: 640px){'
      + '  .rec-tweaks{ right:12px; left:12px; bottom:90px; width:auto; }'
      + '}';
    var s = document.createElement('style');
    s.id = 'rec-tweaks-styles';
    s.textContent = css;
    document.head.appendChild(s);
  }

  /* ---------- 5. プロトコル（必ずリスナー登録 → available 通知の順） ---------- */
  function activate() {
    if (!panel) build();
    panel.classList.add('is-open');
  }
  function deactivate() {
    if (!panel) return;
    panel.classList.remove('is-open');
  }

  window.addEventListener('message', function (ev) {
    var t = ev.data && ev.data.type;
    if (t === '__activate_edit_mode')   activate();
    if (t === '__deactivate_edit_mode') deactivate();
  });

  /* ---------- 6. 初期化 ---------- */
  function init() {
    applyState(state);
    injectCSS();
    // 親に Tweaks 利用可能を通知（リスナーは登録済み）
    try { window.parent.postMessage({ type: '__edit_mode_available' }, '*'); } catch (e) {}
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
