<?php
/**
 * Sidebar — Lumin Coco
 * 物件一覧／ホームの右カラムで使うウィジェットエリア。
 * 親テーマの 'sidebar-widget' に登録された Gutenberg ブロックを出力。
 *
 * 中身（キーワード検索、タグクラウド、エリアリスト）は子テーマの
 * Gutenberg ブロック（lc-sidebar-search, lc-sidebar-tags, lc-sidebar-areas）
 * として提供される。
 *
 * @package SYN_Ownd_Child
 */
?>
<aside class="lc-sidebar" role="complementary">
	<?php
	if ( is_active_sidebar( 'sidebar-widget' ) ) {
		dynamic_sidebar( 'sidebar-widget' );
	}
	?>
</aside>
