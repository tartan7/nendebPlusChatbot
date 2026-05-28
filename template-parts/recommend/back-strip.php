<?php
/**
 * Back-to-main strip — 章末の本編戻り CTA
 *
 * メタキー（任意。未指定なら既定文言で表示）:
 *   _rec_back_text   : 左側の文言（HTML 可。<b><br> 等）
 *   _rec_back_cta1_label / _rec_back_cta1_url  : 主CTA（既定: お問い合わせ）
 *   _rec_back_cta2_label / _rec_back_cta2_url  : 副CTA（既定: 賃貸物件を探す）
 *
 * @package SYN_Ownd_Child
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$pid = get_the_ID();

$text = get_post_meta( $pid, '_rec_back_text', true );
if ( ! $text ) {
	$text = '<b>住まいに関するご相談、お気軽にどうぞ。</b><br>稚内のご事情に合わせてご案内します。';
}

$cta1_label = get_post_meta( $pid, '_rec_back_cta1_label', true );
$cta1_url   = get_post_meta( $pid, '_rec_back_cta1_url',   true );
$cta2_label = get_post_meta( $pid, '_rec_back_cta2_label', true );
$cta2_url   = get_post_meta( $pid, '_rec_back_cta2_url',   true );

if ( ! $cta1_label ) { $cta1_label = 'お問い合わせ'; }
if ( ! $cta1_url )   { $cta1_url   = home_url( '/contact/' ); }
if ( ! $cta2_label ) { $cta2_label = '賃貸物件を探す'; }
if ( ! $cta2_url )   { $cta2_url   = home_url( '/bukken/' ); }
?>
<section class="rec-back" id="contact" style="margin-top:24px;">
	<p class="rec-back__text">
		<?php echo wp_kses( $text, array(
			'b' => array(), 'strong' => array(), 'em' => array(), 'br' => array(),
			'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
		) ); ?>
	</p>
	<div class="rec-back__cta">
		<a class="--primary" href="<?php echo esc_url( $cta1_url ); ?>"><?php echo esc_html( $cta1_label ); ?></a>
		<a class="--ghost"   href="<?php echo esc_url( $cta2_url ); ?>"><?php echo esc_html( $cta2_label ); ?></a>
	</div>
</section>
