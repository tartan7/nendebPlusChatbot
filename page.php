<?php
/**
 * Page — Lumin Coco
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

<div class="lc-main lc-main--full">
	<div>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'lc-card-page' ); ?>>
				<header>
					<h1 class="lc-section__ttl"><?php the_title(); ?></h1>
				</header>
				<div class="lc-prose entry-content">
					<?php the_content(); ?>
					<?php
					wp_link_pages( array(
						'before' => '<nav class="lc-pagination">',
						'after'  => '</nav>',
					) );
					?>
				</div>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile; ?>
	</div>
</div>

<?php get_footer();
