<?php
/**
 * Next / Prev — 副読本ガイドの隣接ナビ
 *
 * メタキー:
 *   _rec_prev_url  / _rec_prev_label
 *   _rec_next_url  / _rec_next_label
 *
 * いずれかが未設定なら、その側はリンクなしのプレースホルダーを表示。
 *
 * @package SYN_Ownd_Child
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$pid = get_the_ID();
$prev_url   = get_post_meta( $pid, '_rec_prev_url',   true );
$prev_label = get_post_meta( $pid, '_rec_prev_label', true );
$next_url   = get_post_meta( $pid, '_rec_next_url',   true );
$next_label = get_post_meta( $pid, '_rec_next_label', true );

// すべて空ならナビ自体を出さない
if ( empty( $prev_url ) && empty( $next_url ) ) { return; }
?>
<nav class="rec-nextprev" aria-label="副読本ナビゲーション">
	<?php if ( $prev_url ) : ?>
		<a class="--prev" href="<?php echo esc_url( $prev_url ); ?>">
			<span class="dir">← BACK</span>
			<span class="ttl"><?php echo esc_html( $prev_label ? $prev_label : '前のページ' ); ?></span>
		</a>
	<?php else : ?>
		<span class="--prev --disabled" aria-hidden="true"><span class="dir">← BACK</span><span class="ttl">—</span></span>
	<?php endif; ?>

	<?php if ( $next_url ) : ?>
		<a class="--next" href="<?php echo esc_url( $next_url ); ?>">
			<span class="dir">NEXT →</span>
			<span class="ttl"><?php echo esc_html( $next_label ? $next_label : '次のページ' ); ?></span>
		</a>
	<?php else : ?>
		<span class="--next --disabled" aria-hidden="true"><span class="dir">NEXT →</span><span class="ttl">—</span></span>
	<?php endif; ?>
</nav>
