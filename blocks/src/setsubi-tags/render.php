<?php
// Server-side render for lc-setsubi-tags
// fudou プラグインの setsubi メタ（スラッシュ区切り数字コード）を集計し、
// $work_setsubi 辞書で名前に解決してタグクラウド表示。

$limit    = isset( $attributes['itemLimit'] ) ? max( 1, (int) $attributes['itemLimit'] ) : 30;
$show_cnt = ! empty( $attributes['showCount'] );

if ( ! empty( $attributes['title'] ) ) {
	echo '<h3 class="lc-sidebar-tags__ttl">' . esc_html( $attributes['title'] ) . '</h3>';
}

echo '<div class="lc-sidebar-tags lc-setsubi-tags">';

$items = function_exists( 'lc_get_fudo_setsubi_counts' )
	? lc_get_fudo_setsubi_counts( $limit )
	: array();

if ( empty( $items ) ) {
	echo '<p class="lc-sidebar-tags__empty">設備データがまだありません。物件編集画面の「設備・条件」にチェックを入れて保存してください。</p>';
	echo '</div>';
	return;
}

$archive_url = get_post_type_archive_link( 'fudo' );
if ( ! $archive_url ) { $archive_url = home_url( '/' ); }

foreach ( $items as $item ) {
	$url = add_query_arg( array( 'setsubi' => array( (string) $item['code'] ) ), $archive_url );
	echo '<a class="lc-tag" href="' . esc_url( $url ) . '">' . esc_html( $item['name'] );
	if ( $show_cnt ) {
		echo ' <span class="lc-tag__count">(' . esc_html( (int) $item['count'] ) . ')</span>';
	}
	echo '</a> ';
}

echo '</div>';
