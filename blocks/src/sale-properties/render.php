<?php
// Server-side render for lc-sale-properties
$items         = isset( $attributes['item'] )        ? max( 1, (int) $attributes['item'] ) : 4;
$sort          = isset( $attributes['sort'] )        ? sanitize_text_field( $attributes['sort'] ) : 'date';
$title         = isset( $attributes['title'] )       ? (string) $attributes['title'] : '';
$button_text   = isset( $attributes['buttonText'] )  ? (string) $attributes['buttonText'] : '物件詳細を見る';
$display_items = isset( $attributes['displayItems'] )? (array)  $attributes['displayItems'] : array();
$newup_days    = isset( $attributes['newup_days'] )  ? max( 0, (int) $attributes['newup_days'] ) : 14;

$bukken_cat = isset( $attributes['bukken_cat'] ) ? (int) $attributes['bukken_cat'] : 0;

$query_args = function_exists( 'lc_build_fudo_query_args' )
	? lc_build_fudo_query_args( array(
		'kbn'            => 'sale',
		'bukken_cat'     => $bukken_cat,
		'orderby'        => $sort,
		'posts_per_page' => $items,
	) )
	: array(
		'post_type'      => 'fudo',
		'posts_per_page' => $items,
		'meta_query'     => array(
			array( 'key' => 'kbn', 'value' => 'sale', 'compare' => '=' ),
		),
	);

$query = new WP_Query( $query_args );

echo '<section class="lc-section-bukken lc-sale-properties">';
if ( $title !== '' ) {
	echo '<h2 class="lc-section__ttl"><small>SALE / 売買</small>' . esc_html( $title ) . '</h2>';
}

if ( $query->have_posts() ) :
	echo '<div id="list_simplepage">';
	while ( $query->have_posts() ) : $query->the_post();
		if ( function_exists( 'lc_render_property_block_card' ) ) {
			lc_render_property_block_card( get_the_ID(), 'tag-sale', '売買', $display_items, $button_text, $newup_days );
		} elseif ( function_exists( 'lc_render_fudo_card' ) ) {
			lc_render_fudo_card( 'tag-sale', '売買' );
		} else {
			echo '<article class="hentry"><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2></article>';
		}
	endwhile;
	echo '</div>';
else :
	echo '<div class="lc-bukken-empty"><p>売買物件は現在ありません。</p></div>';
endif;

echo '</section>';
wp_reset_postdata();
