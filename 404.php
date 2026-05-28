<?php
/**
 * 404 — Lumin Coco
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

<section class="lc-404">
	<h1>404</h1>
	<p>お探しのページは見つかりませんでした。<br />URL をご確認のうえ、ホームから探し直してください。</p>
	<p>
		<a class="lc-toi__btn lc-toi__btn--primary" style="display:inline-flex; width:auto; padding:11px 26px;" href="<?php echo esc_url( home_url( '/' ) ); ?>">ホームへ戻る</a>
	</p>
</section>

<?php get_footer();
