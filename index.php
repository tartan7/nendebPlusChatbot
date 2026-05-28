<?php
/**
 * Index — Lumin Coco
 * 他のテンプレートに該当しないクエリのフォールバック。
 *
 * @package SYN_Ownd_Child
 */
get_header();
?>
<div class="lc-main">
	<div>
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'lc-card-page' ); ?> style="margin-bottom:18px;">
					<h2 style="font-family:var(--font-display);"><a href="<?php the_permalink(); ?>" style="color:var(--color-text);"><?php the_title(); ?></a></h2>
					<div class="lc-prose"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
			<nav class="lc-pagination"><?php echo paginate_links( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?></nav>
		<?php else : ?>
			<div class="lc-card-page"><p>記事が見つかりません。</p></div>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php get_footer();
