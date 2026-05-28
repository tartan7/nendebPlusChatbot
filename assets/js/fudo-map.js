(function () {
	'use strict';

	var mapEl     = document.getElementById('lcMapView');
	var sectionEl = document.getElementById('lcBukkenSection');
	if (!mapEl || !sectionEl) return;

	var markers = [];
	try { markers = JSON.parse(mapEl.getAttribute('data-markers') || '[]'); } catch (e) {}

	var map         = null;
	var openPopup   = null; // 現在開いている popup

	function escHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function buildCardHtml(m) {
		var parts = [];
		if (m.madori)  parts.push(escHtml(m.madori));
		if (m.menseki) parts.push(escHtml(m.menseki) + 'm²');
		var meta = parts.join(' / ');

		return '<div class="lc-map-card">'
			+ (m.img
				? '<div class="lc-map-card__img"><img src="' + escHtml(m.img) + '" alt="" loading="lazy" /></div>'
				: '')
			+ '<div class="lc-map-card__body">'
				+ '<p class="lc-map-card__title">' + escHtml(m.title) + '</p>'
				+ (m.price   ? '<span class="lc-map-card__price">' + escHtml(m.price) + '</span>' : '')
				+ (meta      ? '<span class="lc-map-card__meta">'  + meta             + '</span>' : '')
				+ (m.address ? '<p class="lc-map-card__addr">'     + escHtml(m.address) + '</p>' : '')
				+ '<a class="lc-map-card__link" href="' + escHtml(m.url) + '">→ 物件詳細を見る</a>'
			+ '</div>'
		+ '</div>';
	}

	function initMap() {
		if (map) return;

		var center = markers.length ? [markers[0].lat, markers[0].lng] : [45.416, 141.673];
		map = L.map('lcMapView').setView(center, 14);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			maxZoom: 19,
		}).addTo(map);

		var icon = L.divIcon({
			className: 'lc-map-pin',
			html: '<span></span>',
			iconSize:    [28, 36],
			iconAnchor:  [14, 36],
			popupAnchor: [0, -38],
		});

		markers.forEach(function (m) {
			var popup = L.popup({
				maxWidth: 240,
				minWidth: 200,
				className: 'lc-map-popup',
			}).setContent(buildCardHtml(m));

			var lm = L.marker([m.lat, m.lng], { icon: icon }).addTo(map);
			lm.bindPopup(popup);

			lm.on('click', function () {
				if (openPopup && openPopup !== popup) {
					openPopup.remove();
				}
				openPopup = popup;
			});
		});

		// 全マーカーが収まるよう表示範囲を調整
		if (markers.length > 1) {
			var bounds = L.latLngBounds(markers.map(function (m) { return [m.lat, m.lng]; }));
			map.fitBounds(bounds, { padding: [40, 40] });
		}
	}

	function onViewChange() {
		if (sectionEl.classList.contains('lc-view--map')) {
			mapEl.removeAttribute('aria-hidden');
			initMap();
			setTimeout(function () { if (map) map.invalidateSize(); }, 250);
		} else {
			mapEl.setAttribute('aria-hidden', 'true');
		}
	}

	new MutationObserver(onViewChange).observe(sectionEl, { attributes: true, attributeFilter: ['class'] });
	onViewChange();
})();
