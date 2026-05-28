/* フィルターパネル：種別(kbn)切り替えで エリア / 間取り / こだわり条件 をスイッチ */
(function () {
	'use strict';

	var PRICE_LABELS = {
		rent: '賃料（月額・万円）',
		sale: '価格（万円）',
	};

	function applyKbn( kbn ) {
		var target = kbn || 'all';

		document.querySelectorAll( '[data-kbn-sec]' ).forEach( function ( sec ) {
			var show = ( sec.dataset.kbnSec === target );
			sec.hidden = ! show;
			sec.querySelectorAll( 'input' ).forEach( function ( inp ) {
				inp.disabled = ! show;
				if ( ! show && ( inp.type === 'checkbox' || inp.type === 'radio' ) ) {
					inp.checked = false;
				}
			} );
		} );

		var lbl = document.getElementById( 'lc-price-label' );
		if ( lbl ) {
			lbl.textContent = PRICE_LABELS[ kbn ] || '価格';
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'lc-filter-form' );
		if ( ! form ) { return; }

		form.querySelectorAll( 'input[name="kbn"]' ).forEach( function ( radio ) {
			radio.addEventListener( 'change', function () {
				applyKbn( this.value );
			} );
		} );

		var checked = form.querySelector( 'input[name="kbn"]:checked' );
		applyKbn( checked ? checked.value : '' );
	} );
}() );
