<?php
/**
 * Search form — Lumin Coco
 *
 * @package SYN_Ownd_Child
 */
?>
<form role="search" method="get" class="lc-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="s-<?php echo esc_attr( uniqid() ); ?>"><?php esc_html_e( 'キーワード', 'syn-ownd-child' ); ?></label>
	<input type="search" id="s-<?php echo esc_attr( uniqid() ); ?>" name="s" placeholder="<?php esc_attr_e( 'キーワードを入力', 'syn-ownd-child' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
	<button type="submit" aria-label="<?php esc_attr_e( '検索', 'syn-ownd-child' ); ?>">検索</button>
</form>
