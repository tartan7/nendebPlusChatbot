<?php
/**
 * Single (post) — Lumin Coco
 *
 * @package SYN_Ownd_Child
 */
get_header();
?>
<div class="lc-crumb-bar">
	<div class="lc-crumb-bar__inner">
		<?php lc_breadcrumb(); ?>
	</div>
</div>

<div class="lc-main">
	<div>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'lc-card-page' ); ?>>
				<header style="margin-bottom:18px;">
					<div style="font-size:12px; color:#3D5446; letter-spacing:.04em; margin-bottom:8px;">
						<span style="font-variant-numeric:tabular-nums;"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
						<?php
						$cats = get_the_category();
						if ( $cats ) {
							echo ' <span class="lc-news__cat" style="margin-left:10px;">' . esc_html( $cats[0]->name ) . '</span>';
						}
						?>
					</div>
					<h1 class="lc-section__ttl" style="font-size:26px;"><?php the_title(); ?></h1>
				</header>
				<?php if ( has_post_thumbnail() ) : ?>
					<div style="margin-bottom:24px;"><?php the_post_thumbnail( 'large', array( 'style' => 'border-radius:10px; width:100%; height:auto;' ) ); ?></div>
				<?php endif; ?>
				<div class="lc-prose entry-content"><?php the_content(); ?></div>
			</article>

			<div class="lc-pageback">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">← ホームへ戻る</a>
			</div>
		<?php endwhile; ?>
	</div>
	<?php get_sidebar(); ?>
</div>

<?php get_footer();
