<?php
/**
 * Header — Lumin Coco
 * 親テーマの header.php を子テーマで全面オーバーライド。
 * wp_head / body_class はそのまま呼び出してプラグイン互換性を維持。
 *
 * @package SYN_Ownd_Child
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<meta name="format-detection" content="telephone=no" />
<?php
// 親テーマの SEO メタが存在すれば使う
if ( locate_template( 'template-parts/seo/head-meta.php' ) ) {
	get_template_part( 'template-parts/seo/head-meta' );
}
wp_head();
?>
</head>
<body id="gotop" <?php body_class( 'lc-app is-nogradation' ); ?>>
<?php do_action( 'theme_body_before' ); ?>

<header class="lc-hdr" role="banner">
	<div class="lc-hdr__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lc-hdr__logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
			<?php
			if ( has_custom_logo() ) {
				$logo_id = (int) get_theme_mod( 'custom_logo' );
				echo wp_get_attachment_image(
					$logo_id,
					'full',
					false,
					array(
						'class'   => 'lc-hdr__logo-img',
						'alt'     => esc_attr( get_bloginfo( 'name', 'display' ) ),
						'loading' => 'eager',
					)
				);
			} else {
				printf(
					'<img class="lc-hdr__logo-img" src="%s" alt="%s" />',
					esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/lumin-coco-wordmark-trans.png' ),
					esc_attr( get_bloginfo( 'name', 'display' ) )
				);
			}

			// 英字サブ表示（カスタマイザー: lc_brand_en_strong / lc_brand_en_sub があれば差し替え）
			$en_strong = get_theme_mod( 'lc_brand_en_strong', 'ROOM IN KOKO' );
			$en_sub    = get_theme_mod( 'lc_brand_en_sub',    'WAKKANAI · HOKKAIDO' );
			if ( $en_strong || $en_sub ) :
				?>
				<span class="lc-hdr__logo-en">
					<?php if ( $en_strong ) : ?><strong><?php echo esc_html( $en_strong ); ?></strong><?php endif; ?>
					<?php if ( $en_sub ) : ?><span><?php echo esc_html( $en_sub ); ?></span><?php endif; ?>
				</span>
			<?php endif; ?>
		</a>

		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_class'     => 'lc-hdr__menu',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => false,
			) );
		} else {
			// メニュー未設定時のフォールバック
			?>
			<ul class="lc-hdr__menu">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">ホーム</a></li>
				<?php
				if ( post_type_exists( 'fudo' ) ) {
					$archive = get_post_type_archive_link( 'fudo' );
					echo '<li><a href="' . esc_url( add_query_arg( 'kbn', 'rent', $archive ) ) . '">賃貸物件検索</a></li>';
					echo '<li><a href="' . esc_url( add_query_arg( 'kbn', 'sale', $archive ) ) . '">売買物件検索</a></li>';
				}
				?>
				<li class="cta"><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a></li>
			</ul>
			<?php
		}
		?>

		<button class="lc-hdr__burger" id="lcNavBurger" type="button" aria-expanded="false" aria-label="メニューを開く">
			<span class="lc-hdr__burger-bar"></span>
			<span class="lc-hdr__burger-bar"></span>
			<span class="lc-hdr__burger-bar"></span>
		</button>
	</div>
	<div class="lc-nav-overlay" id="lcNavOverlay" aria-hidden="true"></div>
</header>
