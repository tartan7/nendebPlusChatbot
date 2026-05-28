<?php
// Server-side render for lc-property-search
$archive_url = isset( $attributes['archiveUrl'] ) && $attributes['archiveUrl']
	? $attributes['archiveUrl']
	: get_post_type_archive_link( 'fudo' );

if ( ! $archive_url ) {
	$archive_url = home_url( '/' );
}

$display = isset( $attributes['displayFields'] ) ? (array) $attributes['displayFields'] : array( 'kbn', 'area', 'mado', 'price' );
$title   = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';

// プラグインの fudo_search ショートコードがあればそれを優先（fallback）
if ( shortcode_exists( 'fudo_search' ) || shortcode_exists( 'fudou_search' ) ) {
	$sc = shortcode_exists( 'fudo_search' ) ? '[fudo_search]' : '[fudou_search]';
	echo '<div class="lc-quicksearch lc-property-search">';
	echo '<div class="lc-quicksearch__card">';
	echo '<div style="grid-column:1 / -1;">' . do_shortcode( $sc ) . '</div>';
	echo '</div>';
	echo '</div>';
	return;
}

// エリア候補：fudou プラグインに専用タクソノミーが無いため、
// shozaichimeisho メタを集計して動的生成。
$area_options = ( in_array( 'area', $display, true ) && function_exists( 'lc_get_fudo_areas' ) )
	? lc_get_fudo_areas( 30, true )
	: array();
?>
<div class="lc-quicksearch lc-property-search">
	<?php if ( $title !== '' ) : ?>
		<h3 class="lc-property-search__ttl"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
	<div class="lc-quicksearch__card">
		<form class="lc-qs__inner" action="<?php echo esc_url( $archive_url ); ?>" method="get" style="display:contents;">
			<?php if ( in_array( 'kbn', $display, true ) ) : ?>
				<div class="lc-qs__field">
					<label for="qs-kbn">種別</label>
					<select id="qs-kbn" name="kbn">
						<option value="">すべて</option>
						<option value="rent">賃貸</option>
						<option value="sale">売買</option>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'area', $display, true ) ) : ?>
				<div class="lc-qs__field">
					<label for="qs-area">エリア</label>
					<select id="qs-area" name="area">
						<option value="">すべて</option>
						<?php if ( ! empty( $area_options ) ) :
							foreach ( $area_options as $name => $count ) : ?>
								<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach;
						else :
							foreach ( array( '中央', '末広', '大黒', '潮見', '富岡', '緑' ) as $a ) : ?>
								<option value="<?php echo esc_attr( $a ); ?>"><?php echo esc_html( $a ); ?></option>
							<?php endforeach;
						endif; ?>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'mado', $display, true ) ) : ?>
				<div class="lc-qs__field">
					<label for="qs-mado">間取り</label>
					<select id="qs-mado" name="mado">
						<option value="">すべて</option>
						<option value="1R">1R / 1K</option>
						<option value="1LDK">1LDK / 2K</option>
						<option value="2LDK">2LDK / 3K</option>
						<option value="3LDK">3LDK +</option>
					</select>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'price', $display, true ) ) : ?>
				<div class="lc-qs__field lc-qs__field--price">
					<label>価格（万円）</label>
					<div class="lc-qs__price-row">
						<input id="qs-price-min" type="number" name="price_min" min="0" step="1" placeholder="下限" />
						<span class="lc-qs__price-sep">〜</span>
						<input id="qs-price-max" type="number" name="price_max" min="0" step="1" placeholder="上限" />
					</div>
				</div>
			<?php endif; ?>

			<button type="submit" class="lc-qs__btn">検索</button>
		</form>
	</div>
</div>
<?php
