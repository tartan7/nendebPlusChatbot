<?php
// Server-side render for lc-sidebar-areas
// fudou プラグインには専用エリアタクソノミーが無いため、
// 全 fudo 投稿の 'shozaichimeisho' メタを市区町村ごとに集計してリストを生成。
// Home.html の design に合わせ「[地域] 北海道-稚内市」見出し＋2列グリッドで表示。

$prefecture     = isset( $attributes['prefecture'] )    ? (string) $attributes['prefecture']    : '北海道';
$limit_per_city = isset( $attributes['limitPerCity'] )  ? max( 1, (int) $attributes['limitPerCity'] ) : 30;
$show_count     = ! empty( $attributes['showCount'] );

if ( ! empty( $attributes['title'] ) ) {
	echo '<h3 class="lc-sidebar-areas__ttl">' . esc_html( $attributes['title'] ) . '</h3>';
}

echo '<div class="lc-sidebar-areas">';

$grouped = function_exists( 'lc_get_fudo_areas_grouped' )
	? lc_get_fudo_areas_grouped( $limit_per_city )
	: array();

if ( empty( $grouped ) ) {
	echo '<p class="lc-sidebar-areas__empty">エリア情報がまだありません。</p>';
	echo '</div>';
	return;
}

$archive_url = get_post_type_archive_link( 'fudo' );
if ( ! $archive_url ) { $archive_url = home_url( '/' ); }

foreach ( $grouped as $city => $areas ) {
	$label = '［地域］';
	if ( $prefecture !== '' ) { $label .= $prefecture . '-'; }
	$label .= $city;
	echo '<p class="lc-area-widget__intro">' . esc_html( $label ) . '</p>';

	echo '<ul class="lc-area-widget__list">';
	foreach ( $areas as $name => $count ) {
		$url = add_query_arg( array( 'area' => array( $name ) ), $archive_url );
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $name );
		if ( $show_count ) {
			echo ' <span class="lc-area-widget__count">(' . esc_html( (int) $count ) . ')</span>';
		}
		echo '</a></li>';
	}
	echo '</ul>';
}

echo '</div>';
