<?php
/**
 * Front Page — Lumin Coco
 *
 * Hero と CTA だけを固定で出力。それ以下の本文は完全にウィジェットエリア駆動：
 *
 *   front-before-widget  : Hero と本文の間（親テーマ）
 *   top_widgets          : ホーム本文（fudou プラグイン、再登録済）
 *   sidebar-widget       : 右カラム（親テーマ、再登録済）
 *   front-after-widget   : 本文とフッターの間（親テーマ）
 *
 * top_widgets が未設定の場合、本文領域は空となる。管理者は管理画面 →
 * 外観 → ウィジェット → 「トップページ (ホーム本文)」 に
 * lc-property-search / lc-news-list / lc-rental-properties / lc-sale-properties
 * 等のブロックを配置する必要がある。
 *
 * @package SYN_Ownd_Child
 */
get_header();

$hero_eyebrow = get_theme_mod( 'lc_hero_eyebrow', 'WAKKANAI REAL ESTATE' );
$hero_title   = get_theme_mod( 'lc_hero_title',   '「部屋を選ぶ」＝<br/>「これからの<em>生活</em>を選ぶ」こと' );
$hero_sub     = get_theme_mod( 'lc_hero_sub',     '日本最北の街、稚内で40年。地域密着の不動産会社が、あなたにちょうどいい暮らしをお手伝いします。' );
$hero_cta_lbl = get_theme_mod( 'lc_hero_cta_label', '物件を探す' );
$hero_cta_url = get_theme_mod( 'lc_hero_cta_url',   post_type_exists( 'fudo' ) ? get_post_type_archive_link( 'fudo' ) : home_url( '/' ) );
$hero_image   = get_theme_mod( 'lc_hero_image_url' );
?>

<section class="lc-hero" <?php if ( $hero_image ) : ?>style="--lc-hero-img: url('<?php echo esc_url( $hero_image ); ?>');"<?php endif; ?>>
	<div class="lc-hero__bg"></div>
	<div class="lc-hero__inner">
		<div class="lc-hero__eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></div>
		<h1 class="lc-hero__title"><?php echo wp_kses( $hero_title, array( 'br' => array(), 'em' => array(), 'strong' => array() ) ); ?></h1>
		<?php if ( $hero_sub ) : ?>
			<p class="lc-hero__sub"><?php echo esc_html( $hero_sub ); ?></p>
		<?php endif; ?>
		<a href="<?php echo esc_url( $hero_cta_url ); ?>" class="lc-hero__cta"><?php echo esc_html( $hero_cta_lbl ); ?> →</a>
	</div>
	<?php if ( is_active_sidebar( 'front-before-widget' ) ) : ?>
		<div class="lc-hero__search">
			<?php dynamic_sidebar( 'front-before-widget' ); ?>
		</div>
	<?php endif; ?>
</section>

<div class="lc-main">
	<div class="lc-main__primary">
		<?php
		if ( is_active_sidebar( 'top_widgets' ) ) {
			echo '<div class="lc-top-widgets">';
			dynamic_sidebar( 'top_widgets' );
			echo '</div>';
		} elseif ( current_user_can( 'edit_theme_options' ) ) {
			// 管理者にだけ、ウィジェットエリアの配置マップを表示
			$widgets_url = esc_url( admin_url( 'widgets.php' ) );
			?>
			<div class="lc-admin-notice">
				<p><strong>トップページが未設定です。</strong> <a href="<?php echo $widgets_url; ?>">外観 → ウィジェット</a> で以下を配置してください：</p>

				<table class="lc-admin-notice__map">
					<thead>
						<tr><th>配置エリア</th><th>配置するウィジェット（上から順）</th></tr>
					</thead>
					<tbody>
						<tr>
							<td><code>トップ Hero下 (クイック検索)</code></td>
							<td>LC 物件検索フォーム</td>
						</tr>
						<tr>
							<td><code>トップページ (ホーム本文)</code></td>
							<td>LC 最新情報 → LC 賃貸物件 → LC 売買物件</td>
						</tr>
						<tr>
							<td><code>サイドバー (物件検索)</code></td>
							<td>LC キーワード検索 → LC エリアから探す → LC タグクラウド</td>
						</tr>
					</tbody>
				</table>

				<p style="font-size:.85em;color:#888;margin-top:1em;">※このメッセージは管理者にのみ表示されます。</p>
			</div>
			<?php
		}
		?>
	</div>

	<?php get_sidebar(); ?>
</div>

<?php /* 親テーマ：トップ後ウィジェットエリア */
if ( is_active_sidebar( 'front-after-widget' ) ) : ?>
	<div class="lc-front-after">
		<?php dynamic_sidebar( 'front-after-widget' ); ?>
	</div>
<?php endif; ?>

<?php
get_footer();
