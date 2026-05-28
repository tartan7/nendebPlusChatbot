<?php
/**
 * Archive (post / category / tag / author etc.) — Lumin Coco
 * 物件アーカイブは archive-fudo.php が優先される。
 *
 * @package SYN_Ownd_Child
 */
get_header();
?>
<section class="lc-pagehead">
	<div class="lc-pagehead__inner">
		<?php lc_breadcrumb(); ?>
		<div class="lc-pagehead__row">
			<h1 class="lc-pagehead__ttl"><small>ARCHIVE</small><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<?php $desc = get_the_archive_description(); if ( $desc ) : ?>
				<div class="lc-pagehead__count"><?php echo wp_kses_post( $desc ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>

<div class="lc-main">
	<div>
		<?php if ( have_posts() ) : ?>
			<div style="display:grid; grid-template-columns:1fr; gap:18px;">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'lc-card-page' ); ?> style="padding:24px 28px;">
						<div style="font-size:12px; color:#3D5446; margin-bottom:8px;">
							<span style="font-variant-numeric:tabular-nums;"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
							<?php $cats = get_the_category(); if ( $cats ) : ?>
								<span class="lc-news__cat" style="margin-left:10px;"><?php echo esc_html( $cats[0]->name ); ?></span>
							<?php endif; ?>
						</div>
						<h2 style="font-family:var(--font-display); font-size:18px; margin:0 0 8px;">
							<a href="<?php the_permalink(); ?>" style="color:var(--color-text);"><?php the_title(); ?></a>
						</h2>
						<p style="font-size:13px; color:#3D5446; line-height:1.8; margin:0;"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 60 ) ); ?></p>
					</article>
				<?php endwhile; ?>
			</div>

			<nav class="lc-pagination" aria-label="ページネーション">
				<?php echo paginate_links( array( 'prev_text' => '‹ 前へ', 'next_text' => '次へ ›', 'mid_size' => 1 ) ); ?>
			</nav>
		<?php else : ?>
			<div class="lc-card-page"><p>該当する記事がまだありません。</p></div>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>

<?php get_footer();
