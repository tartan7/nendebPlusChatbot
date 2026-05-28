(function () {
	'use strict';
	var burger  = document.getElementById('lcNavBurger');
	var overlay = document.getElementById('lcNavOverlay');
	if (!burger) return;
	var hdr = burger.closest('.lc-hdr');

	function openNav() {
		hdr.classList.add('is-nav-open');
		burger.setAttribute('aria-expanded', 'true');
		burger.setAttribute('aria-label', 'メニューを閉じる');
		document.body.style.overflow = 'hidden';
	}

	function closeNav() {
		hdr.classList.remove('is-nav-open');
		burger.setAttribute('aria-expanded', 'false');
		burger.setAttribute('aria-label', 'メニューを開く');
		document.body.style.overflow = '';
	}

	burger.addEventListener('click', function () {
		hdr.classList.contains('is-nav-open') ? closeNav() : openNav();
	});

	if (overlay) {
		overlay.addEventListener('click', closeNav);
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeNav();
	});

	// メニュー内リンクをタップ後に閉じる
	var menu = hdr.querySelector('.lc-hdr__menu');
	if (menu) {
		menu.addEventListener('click', function (e) {
			if (e.target.tagName === 'A') closeNav();
		});
	}
})();

// フィルターアコーディオン（モバイル）
(function () {
	'use strict';
	var filterToggle = document.querySelector('.lc-filterpanel__toggle');
	if (!filterToggle) return;
	var filterPanel = filterToggle.closest('.lc-filterpanel');
	filterToggle.addEventListener('click', function () {
		var isOpen = filterPanel.classList.toggle('is-open');
		filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		filterToggle.setAttribute('aria-label', isOpen ? '絞り込みを閉じる' : '絞り込みを開く');
	});
})();

// 表示切替（タイル / リスト）
(function () {
	'use strict';
	var viewSwitch = document.getElementById('lcViewSwitch');
	var bukkenSection = document.getElementById('lcBukkenSection');
	if (!viewSwitch || !bukkenSection) return;

	function setView(view) {
		bukkenSection.className = bukkenSection.className.replace(/\blc-view--\S+/g, '').trim();
		bukkenSection.classList.add('lc-view--' + view);
		viewSwitch.querySelectorAll('[data-view]').forEach(function (b) {
			b.classList.toggle('is-active', b.getAttribute('data-view') === view);
			b.setAttribute('aria-pressed', b.getAttribute('data-view') === view ? 'true' : 'false');
		});
		try { sessionStorage.setItem('lcBukkenView', view); } catch (e) {}
	}

	viewSwitch.querySelectorAll('[data-view]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			setView(btn.getAttribute('data-view'));
		});
	});

	// 前回の表示モードを復元
	try {
		var saved = sessionStorage.getItem('lcBukkenView');
		if (saved && viewSwitch.querySelector('[data-view="' + saved + '"]:not([disabled])')) {
			setView(saved);
		}
	} catch (e) {}
})();
