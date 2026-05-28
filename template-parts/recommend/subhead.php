<?php
/**
 * Subhead — 副読本ガイド共通のページ見出し（ヒーローなし）
 *
 * 期待されるカスタムフィールド（meta_key）:
 *   _rec_eyebrow     : 上部の小さなラベル（例 "GUIDE · MOVING"）
 *   _rec_title_en    : H1 下の英文サブ（例 "MOVING IN & SETTLING"）
 *   _rec_lead        : リード文（HTML 可。<b> 等の最小タグ）
 *   _rec_updated     : 更新日（"YYYY-MM-DD" 形式 / 未指定なら post_modified）
 *   _rec_read_time   : 読了の目安（例 "約6分"）
 *   _rec_related     : 関連リンクの HTML（例 '<a href="...">引越し</a> ／ <a href="...">トラブル</a>'）
 *
 * @package SYN_Ownd_Child
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = get_the_ID();

$eyebrow   = get_post_meta( $post_id, '_rec_eyebrow',   true );
$title_en  = get_post_meta( $post_id, '_rec_title_en',  true );
$lead      = get_post_meta( $post_id, '_rec_lead',      true );
$updated   = get_post_meta( $post_id, '_rec_updated',   true );
$read_time = get_post_meta( $post_id, '_rec_read_time', true );
$related   = get_post_meta( $post_id, '_rec_related',   true );

// 更新日の補完：未指定なら投稿の最終更新を使う
if ( empty( $updated ) ) {
	$updated = get_the_modified_date( 'Y-m-d', $post_id );
}
$updated_label = $updated ? mysql2date( get_option( 'date_format' ), $updated, true ) : '';

// 既定値（メタが空でも壊れない最低限の見栄え）
if ( empty( $eyebrow ) ) { $eyebrow = 'GUIDE'; }
?>
<section class="rec-subhead" aria-labelledby="rec-page-title">
	<div class="rec-subhead__inner">

		<?php if ( $eyebrow ) : ?>
			<p class="rec-subhead__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<h1 class="rec-subhead__ttl" id="rec-page-title">
			<?php the_title(); ?>
			<?php if ( $title_en ) : ?>
				<small><?php echo esc_html( $title_en ); ?></small>
			<?php endif; ?>
		</h1>

		<?php if ( $lead ) : ?>
			<p class="rec-subhead__lead">
				<?php
				// HTML 許可（<b>, <a>, <small>, <em>, <br> 等の最小タグのみ）
				echo wp_kses( $lead, array(
					'b'     => array(),
					'strong'=> array(),
					'em'    => array(),
					'i'     => array(),
					'a'     => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
					'br'    => array(),
					'small' => array(),
				) );
				?>
			</p>
		<?php endif; ?>

		<div class="rec-subhead__meta">
			<?php if ( $updated_label ) : ?>
				<span><b>最終更新</b><time datetime="<?php echo esc_attr( $updated ); ?>"><?php echo esc_html( $updated_label ); ?></time></span>
			<?php endif; ?>
			<?php if ( $read_time ) : ?>
				<span><b>読了の目安</b><?php echo esc_html( $read_time ); ?></span>
			<?php endif; ?>
			<?php if ( $related ) : ?>
				<span><b>関連</b><?php
					echo wp_kses( $related, array(
						'a'  => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
						'br' => array(),
					) );
				?></span>
			<?php endif; ?>
		</div>

	</div>
</section>
