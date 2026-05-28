<?php
// Server-side render for lc-sidebar-tags
// fudou プラグインで実在するタクソノミーは 'bukken_tag'（物件投稿タグ）と 'bukken'（物件カテゴリ）。
// 旧来の 'fudo_tag' を保存している既存ウィジェットインスタンス互換のため、
// 設定値が存在しなければ自動で 'bukken_tag' にフォールバックする。

$requested = isset( $attributes['taxonomy'] ) ? sanitize_key( $attributes['taxonomy'] ) : 'bukken_tag';
$limit     = isset( $attributes['itemLimit'] ) ? max( 1, (int) $attributes['itemLimit'] ) : 20;

// フォールバック順：設定値 → bukken_tag → bukken
$candidates = array( $requested, 'bukken_tag', 'bukken' );
$tax = '';
foreach ( $candidates as $cand ) {
	if ( $cand && taxonomy_exists( $cand ) ) { $tax = $cand; break; }
}

if ( ! empty( $attributes['title'] ) ) {
	echo '<h3 class="lc-sidebar-tags__ttl">' . esc_html( $attributes['title'] ) . '</h3>';
}

echo '<div class="lc-sidebar-tags">';

if ( $tax === '' ) {
	echo '<p class="lc-sidebar-tags__empty">物件タグ用タクソノミーが見つかりません（bukken_tag / bukken どちらも未登録）。</p>';
	echo '</div>';
	return;
}

$terms = function_exists( 'lc_get_fudo_terms' )
	? lc_get_fudo_terms( $tax, array( 'number' => $limit ) )
	: get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'number' => $limit ) );

if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	foreach ( $terms as $t ) {
		$link = get_term_link( $t );
		if ( is_wp_error( $link ) ) { continue; }
		echo '<a class="lc-tag" href="' . esc_url( $link ) . '">' . esc_html( $t->name ) . '</a> ';
	}
} else {
	echo '<p class="lc-sidebar-tags__empty">タグがまだ登録されていません。</p>';
}
echo '</div>';
