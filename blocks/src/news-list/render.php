<?php
// Server-side render for lc-news-list
$ppp          = isset( $attributes['postsPerPage'] )    ? max( 1, (int) $attributes['postsPerPage'] ) : 5;
$show_cat     = ! empty( $attributes['displayCategory'] );
$show_date    = ! empty( $attributes['displayDate'] );
$title        = isset( $attributes['title'] )   ? (string) $attributes['title']   : '';
$eyebrow      = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';

$news_q = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => $ppp,
	'no_found_rows'  => true,
) );

if ( ! $news_q->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="lc-news lc-news--block">
	<?php if ( $title || $eyebrow ) : ?>
		<h2 class="lc-section__ttl">
			<?php if ( $eyebrow ) : ?><small><?php echo esc_html( $eyebrow ); ?></small><?php endif; ?>
			<?php echo esc_html( $title ); ?>
		</h2>
	<?php endif; ?>
	<ul class="lc-news__list">
		<?php while ( $news_q->have_posts() ) : $news_q->the_post();
			$cats = get_the_category();
			$cat  = $cats ? $cats[0]->name : 'お知らせ';
			?>
			<li>
				<?php if ( $show_date ) : ?>
					<span class="lc-news__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
				<?php endif; ?>
				<?php if ( $show_cat ) : ?>
					<span class="lc-news__cat"><?php echo esc_html( $cat ); ?></span>
				<?php endif; ?>
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</li>
		<?php endwhile; ?>
	</ul>
</section>
<?php
wp_reset_postdata();
