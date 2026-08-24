// 物件詳細ページ：写真ギャラリーのライトボックス表示
(function () {
	'use strict';
	var gallery = document.querySelector('[data-lc-gallery]');
	if (!gallery) return;

	var photos = [];
	try {
		photos = JSON.parse(gallery.getAttribute('data-photos') || '[]');
	} catch (e) {
		photos = [];
	}
	if (!photos.length) return;

	var lightbox, imgEl, counterEl, prevBtn, nextBtn, closeBtn;
	var currentIndex = 0;
	var touchStartX = null;

	function buildLightbox() {
		lightbox = document.createElement('div');
		lightbox.className = 'lc-lightbox';
		lightbox.setAttribute('role', 'dialog');
		lightbox.setAttribute('aria-modal', 'true');
		lightbox.setAttribute('aria-label', '物件写真');

		var stage = document.createElement('div');
		stage.className = 'lc-lightbox__stage';

		imgEl = document.createElement('img');
		imgEl.className = 'lc-lightbox__img';
		imgEl.alt = '';

		closeBtn = document.createElement('button');
		closeBtn.type = 'button';
		closeBtn.className = 'lc-lightbox__close';
		closeBtn.setAttribute('aria-label', '閉じる');
		closeBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>';

		counterEl = document.createElement('div');
		counterEl.className = 'lc-lightbox__counter';

		stage.appendChild(imgEl);
		lightbox.appendChild(stage);
		lightbox.appendChild(closeBtn);
		lightbox.appendChild(counterEl);

		if (photos.length > 1) {
			prevBtn = document.createElement('button');
			prevBtn.type = 'button';
			prevBtn.className = 'lc-lightbox__prev';
			prevBtn.setAttribute('aria-label', '前の写真');
			prevBtn.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6"/></svg>';

			nextBtn = document.createElement('button');
			nextBtn.type = 'button';
			nextBtn.className = 'lc-lightbox__next';
			nextBtn.setAttribute('aria-label', '次の写真');
			nextBtn.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>';

			lightbox.appendChild(prevBtn);
			lightbox.appendChild(nextBtn);

			prevBtn.addEventListener('click', function (e) { e.stopPropagation(); showPhoto(currentIndex - 1); });
			nextBtn.addEventListener('click', function (e) { e.stopPropagation(); showPhoto(currentIndex + 1); });
		}

		closeBtn.addEventListener('click', close);
		stage.addEventListener('click', function (e) {
			if (e.target === stage) close();
		});
		lightbox.addEventListener('touchstart', function (e) {
			touchStartX = e.changedTouches[0].clientX;
		}, { passive: true });
		lightbox.addEventListener('touchend', function (e) {
			if (touchStartX === null) return;
			var dx = e.changedTouches[0].clientX - touchStartX;
			touchStartX = null;
			if (Math.abs(dx) < 40) return;
			showPhoto(currentIndex + (dx < 0 ? 1 : -1));
		}, { passive: true });

		document.body.appendChild(lightbox);
	}

	function showPhoto(index) {
		currentIndex = (index + photos.length) % photos.length;
		imgEl.src = photos[currentIndex];
		counterEl.textContent = (currentIndex + 1) + ' / ' + photos.length;
	}

	function open(index) {
		if (!lightbox) buildLightbox();
		showPhoto(index);
		lightbox.classList.add('is-open');
		document.body.style.overflow = 'hidden';
		document.addEventListener('keydown', onKeydown);
		closeBtn.focus();
	}

	function close() {
		if (!lightbox) return;
		lightbox.classList.remove('is-open');
		document.body.style.overflow = '';
		document.removeEventListener('keydown', onKeydown);
	}

	function onKeydown(e) {
		if (e.key === 'Escape') close();
		else if (e.key === 'ArrowLeft') showPhoto(currentIndex - 1);
		else if (e.key === 'ArrowRight') showPhoto(currentIndex + 1);
	}

	gallery.querySelectorAll('.lc-gallery__cell').forEach(function (cell) {
		cell.addEventListener('click', function () {
			var index = parseInt(cell.getAttribute('data-index'), 10) || 0;
			open(index);
		});
	});
})();
