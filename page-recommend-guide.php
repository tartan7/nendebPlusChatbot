<?php
/**
 * Template Name: 副読本 — ガイド（4段テンプレ）
 * Template Post Type: page
 *
 * /recommend/guide/<slug>/ など、副読本配下のページに割り当てて使う
 * 共通テンプレ。本文はエディタで「①30秒サマリー／②手順・チェック／
 * ③補足／④参照リンク」の4見出しで書く前提。
 *
 * 必要なメタは「副読本」ボックスから入力（inc/recommend.php で登録）。
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header(); ?>

<!-- ===== Breadcrumb ===== -->
<div class="lc-crumb-bar">
	<div class="lc-crumb-bar__inner">
		<?php lc_breadcrumb(); ?>
	</div>
</div>

<?php while ( have_posts() ) : the_post(); ?>

	<?php get_template_part( 'template-parts/recommend/subhead' ); ?>

	<main class="rec-main">

		<?php lc_legacy_origin_banner(); /* ?from=recom11 等で来た方へのバナー */ ?>

		<article <?php post_class( 'rec-article' ); ?>>
			<div class="entry-content">
				<?php
				/*
				 * 本文はエディタで4セクションを記述する。
				 * 各セクションは記事内ショートコード [rec_section] か、
				 * <section class="rec-sec"> を直書きする。
				 * 例:
				 *   [rec_section num="①" title="30秒サマリー" en="SUMMARY"]
				 *   …本文…
				 *   [/rec_section]
				 *
				 *   [rec_callout type="warn" title="各自治体で異なります"]
				 *   …本文…
				 *   [/rec_callout]
				 */
				the_content();

				wp_link_pages( array(
					'before' => '<nav class="rec-pagination">',
					'after'  => '</nav>',
				) );
				?>
			</div>
		</article>

		<?php get_template_part( 'template-parts/recommend/nextprev' ); ?>
		<?php get_template_part( 'template-parts/recommend/back-strip' ); ?>

	</main>

<?php endwhile; ?>

<?php
get_footer();
