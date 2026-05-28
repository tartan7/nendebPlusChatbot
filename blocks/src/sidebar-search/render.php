<?php
// Server-side render for lc-sidebar-search
// 構造: <form.lc-sidebar-search>
//   <h3.lc-sidebar-search__ttl>      ← 任意（attributes.title）
//   <div.lc-sidebar-search__row>     ← input + button を横並び固定
//     <input.lc-sidebar-search__input>
//     <input type="hidden"> (post_type=fudo)
//     <button.lc-sidebar-search__btn>
//
// 旧構造の .lc-field / class="button" は親テーマの汎用スタイルを拾ってしまい
// 色やレイアウトが上書きされたため、子テーマ独自クラスのみで構成する。

$placeholder = isset( $attributes['placeholder'] ) && $attributes['placeholder'] !== ''
	? $attributes['placeholder']
	: '物件キーワード';

$archive = get_post_type_archive_link( 'fudo' );
if ( ! $archive ) { $archive = home_url( '/' ); }

$val = get_search_query();
?>
<?php /* lc-sidebar-search v2 — DOM/CSS 確認用マーカー */ ?>
<form class="lc-sidebar-search lc-sidebar-search--v2" method="get" action="<?php echo esc_url( $archive ); ?>" role="search">
	<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h3 class="lc-sidebar-search__ttl"><?php echo esc_html( $attributes['title'] ); ?></h3>
	<?php endif; ?>
	<div class="lc-sidebar-search__row">
		<input
			class="lc-sidebar-search__input"
			type="search"
			name="s"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			value="<?php echo esc_attr( $val ); ?>"
		/>
		<input type="hidden" name="post_type" value="fudo" />
		<button class="lc-sidebar-search__btn" type="submit">検索</button>
	</div>
</form>
