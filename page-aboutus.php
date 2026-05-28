<?php
/**
 * Template — 会社概要 (about us)
 * 固定ページ slug "aboutus" 用テンプレート。WordPress テンプレート階層により、
 * /aboutus/ 固定ページがあれば自動でこのファイルが採用される。
 *
 * モック: _preview/About.html
 * - DOM 構造は About.html 準拠、クラス名は子テーマ規約に従い lc-* で統一
 * - 会社情報は customizer の lc_company_* / lc_chat_fallback_url を尊重
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

// theme_mod から会社情報を取得（footer.php / single-fudo.php と統一）
$company_addr_raw = get_theme_mod( 'lc_company_address', "〒097-0017\n北海道稚内市栄5丁目7番5号 コーポ サンロード 1F\nTEL：0162-32-8877　FAX：0162-32-8878" );
$company_lic      = get_theme_mod( 'lc_company_license', '宅地建物取引業免許　北海道知事 宗谷(4) 第53号' );
$company_tel_disp = get_theme_mod( 'lc_company_tel', '0162-32-8877' );
$company_tel_num  = preg_replace( '/[^0-9]/', '', $company_tel_disp );
$company_tel_note = get_theme_mod( 'lc_company_tel_note', '受付 9:00-17:30 ／ 日曜定休' );
$contact_url      = get_theme_mod( 'lc_chat_fallback_url', home_url( '/contact/' ) );

// 住所だけ抽出（先頭 〒 行から）
$company_addr_lines = preg_split( '/\r?\n/', $company_addr_raw );
$company_addr       = implode( '　', array_slice( $company_addr_lines, 0, 2 ) ); // 〒…+ 所在地
?>

<div class="lc-crumb-bar">
	<div class="lc-crumb-bar__inner">
		<?php lc_breadcrumb(); ?>
	</div>
</div>

<!-- ===== Hero ===== -->
<section class="lc-about-hero">
	<div class="lc-about-hero__bg" aria-hidden="true"></div>
	<?php
	$silhouette = get_stylesheet_directory() . '/assets/images/logo/room-in-koko-silhouette-white.png';
	if ( file_exists( $silhouette ) ) :
	?>
	<div class="lc-about-hero__art" aria-hidden="true">
		<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/room-in-koko-silhouette-white.png' ); ?>" alt="" />
	</div>
	<?php endif; ?>
	<div class="lc-about-hero__inner">
		<div class="lc-about-hero__eyebrow">ABOUT US</div>
		<h1 class="lc-about-hero__ttl">
			日本最北の街で、<br />暮らしの「ココ」をつなぐ。
			<small>COMPANY OVERVIEW</small>
		</h1>
		<p class="lc-about-hero__lead">
			株式会社ルーミン・ココは、2007年12月に北海道稚内市に設立された不動産仲介会社です。<br />
			賃貸・売買の仲介を軸に、地域に根ざした「お客様の視点でのサービス」をお届けしています。
		</p>
	</div>
</section>

<!-- ===== Main ===== -->
<main class="lc-about-main">

	<!-- Mission strip -->
	<section class="lc-mission">
		<div class="lc-mission__badge">
			<span class="lc-mission__year">2007</span>
			<span class="lc-mission__year-label">SINCE / 創業年</span>
		</div>
		<div class="lc-mission__body">
			<p>「部屋を選ぶ」＝「街を選ぶ」「人生を選ぶ」。稚内のまちと真摯に向き合い、賃貸も売買も、長くお付き合いできる一軒を一緒に探します。</p>
			<p><?php echo esc_html( $company_lic ); ?>　／　設立 2007年12月　／　日曜定休</p>
		</div>
	</section>

	<!-- ============ 会社概要 ============ -->
	<section class="lc-about-section" id="overview">
		<header class="lc-about-section__head">
			<span class="lc-about-section__num">01.</span>
			<h2 class="lc-about-section__ttl">会社概要 <small>COMPANY PROFILE</small></h2>
		</header>

		<table class="lc-info-table" summary="会社概要">
			<tbody>
				<tr>
					<th>会社名（運営事業者）</th>
					<td>株式会社ルーミン・ココ</td>
				</tr>
				<tr>
					<th>所在地</th>
					<td>
						<span class="lc-info-table__row-icon">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
							<?php echo esc_html( $company_addr ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th>電話 / FAX</th>
					<td>
						<span class="lc-info-table__row-icon">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57-.35-.11-.74-.03-1.02.24l-2.2 2.2c-2.83-1.44-5.15-3.75-6.59-6.58l2.2-2.21c.28-.27.36-.66.25-1.01C8.7 6.45 8.5 5.25 8.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.5c0-.55-.45-1-1-1z"/></svg>
							<a href="tel:<?php echo esc_attr( $company_tel_num ); ?>"><strong><?php echo esc_html( $company_tel_disp ); ?></strong></a>　／　FAX 0162-32-8878
						</span>
					</td>
				</tr>
				<tr>
					<th>メールアドレス</th>
					<td>
						<span class="lc-info-table__row-icon">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
							<a href="mailto:roomin-koko@violet.plala.or.jp">roomin-koko@violet.plala.or.jp</a>
						</span>
					</td>
				</tr>
				<tr>
					<th>代表者（運営責任者）</th>
					<td>石本 隆一</td>
				</tr>
				<tr>
					<th>免許番号</th>
					<td><?php echo esc_html( $company_lic ); ?></td>
				</tr>
				<tr>
					<th>営業時間</th>
					<td><?php echo esc_html( $company_tel_note ); ?>　<span class="lc-info-table__pill">日曜定休</span></td>
				</tr>
				<tr>
					<th>設立</th>
					<td>2007年12月</td>
				</tr>
				<tr>
					<th>事業内容</th>
					<td>
						<div class="lc-info-table__biz"><span class="lc-info-table__biz-no">A.</span><span class="lc-info-table__biz-txt">賃貸不動産仲介業務</span></div>
						<div class="lc-info-table__biz"><span class="lc-info-table__biz-no">B.</span><span class="lc-info-table__biz-txt">売買不動産仲介業務</span></div>
						<div class="lc-info-table__biz"><span class="lc-info-table__biz-no">C.</span><span class="lc-info-table__biz-txt"><a href="http://ameblo.jp/atelier-kanon/" target="_blank" rel="noopener">フラワーアレンジメント教室：花音</a></span></div>
					</td>
				</tr>
			</tbody>
		</table>
	</section>

	<!-- ============ 仲介手数料 ============ -->
	<section class="lc-about-section" id="fee">
		<header class="lc-about-section__head">
			<span class="lc-about-section__num">02.</span>
			<h2 class="lc-about-section__ttl">仲介手数料 <small>BROKERAGE FEE</small></h2>
		</header>

		<div class="lc-fee-grid">
			<article class="lc-fee-card">
				<span class="lc-fee-card__no">01</span>
				<span class="lc-fee-card__tier">TIER 1</span>
				<span class="lc-fee-card__range">〜 200万円<small>UNDER 2,000,000 JPY</small></span>
				<div class="lc-fee-card__formula">
					売買価格 × <b>5%</b><span class="lc-fee-card__op">＋ 消費税</span>
				</div>
				<span class="lc-fee-card__note">少額の取引・空き地等におすすめ。</span>
			</article>

			<article class="lc-fee-card">
				<span class="lc-fee-card__no">02</span>
				<span class="lc-fee-card__tier">TIER 2</span>
				<span class="lc-fee-card__range">200万 〜 400万円<small>2,000,001 — 4,000,000 JPY</small></span>
				<div class="lc-fee-card__formula">
					売買価格 × <b>4%</b><span class="lc-fee-card__op">＋ 20,000円 ＋ 消費税</span>
				</div>
				<span class="lc-fee-card__note">小規模住宅・中古マンション帯。</span>
			</article>

			<article class="lc-fee-card">
				<span class="lc-fee-card__no">03</span>
				<span class="lc-fee-card__tier">TIER 3</span>
				<span class="lc-fee-card__range">400万円 〜<small>OVER 4,000,000 JPY</small></span>
				<div class="lc-fee-card__formula">
					売買価格 × <b>3%</b><span class="lc-fee-card__op">＋ 60,000円 ＋ 消費税</span>
				</div>
				<span class="lc-fee-card__note">戸建て・店舗・土地など主力レンジ。</span>
			</article>
		</div>

		<p class="lc-fee-foot">
			上記は宅地建物取引業法に基づく上限額です。実際のご契約時はお見積りいたします。賃貸物件の仲介手数料は別途、契約形態に応じてご案内します。
		</p>
	</section>

	<!-- ============ 社長プロフィール ============ -->
	<section class="lc-about-section" id="ceo">
		<header class="lc-about-section__head">
			<span class="lc-about-section__num">03.</span>
			<h2 class="lc-about-section__ttl">社長プロフィール <small>CEO PROFILE</small></h2>
		</header>

		<div class="lc-profile">
			<?php
			$ceo_img_local = get_stylesheet_directory() . '/assets/images/about/ceo.jpg';
			$ceo_img_url   = file_exists( $ceo_img_local )
				? get_stylesheet_directory_uri() . '/assets/images/about/ceo.jpg'
				: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80';
			?>
			<div class="lc-profile__photo" style="background-image:url('<?php echo esc_url( $ceo_img_url ); ?>');" role="img" aria-label="代表取締役 石本隆一">
				<span class="lc-profile__photo-cap">REPRESENTATIVE DIRECTOR</span>
			</div>
			<div class="lc-profile__body">
				<span class="lc-profile__role">代表取締役 ／ CEO</span>
				<h3 class="lc-profile__name">石本 隆一<small>ISHIMOTO RYUICHI</small></h3>

				<ul class="lc-career-list">
					<li><time>平成 7年 3月</time><strong>札幌学院大学　卒業</strong></li>
					<li><time>平成12年11月</time><strong>宅地建物取引主任者　資格取得</strong></li>
					<li><time>平成14年 1月</time><strong>管理業務主任者　資格取得</strong></li>
					<li><time>平成19年12月</time><strong>株式会社ルーミン・ココ　設立</strong></li>
				</ul>

				<blockquote class="lc-profile__quote">
					他の業者さんに負けない「若さ」と「お客様の視点でのサービス」を提供できるよう、日々がんばっています。稚内のまちのことなら、何でもご相談ください。
					<span class="lc-profile__quote-by">— 代表取締役 石本 隆一</span>
				</blockquote>
			</div>
		</div>
	</section>

	<!-- ============ アクセス ============ -->
	<section class="lc-about-section" id="access">
		<header class="lc-about-section__head">
			<span class="lc-about-section__num">04.</span>
			<h2 class="lc-about-section__ttl">アクセス <small>ACCESS</small></h2>
		</header>

		<?php
		$map_embed = get_theme_mod( 'lc_company_map_embed', '' );
		$map_query = rawurlencode( $company_addr );
		$map_open  = 'https://www.google.com/maps/search/?api=1&query=' . $map_query;
		?>
		<div class="lc-access">
			<div class="lc-access__info">
				<h3>本社オフィス</h3>
				<dl class="lc-access__row">
					<dt>ADDRESS</dt>
					<dd><?php echo nl2br( esc_html( $company_addr_raw ) ); ?></dd>
				</dl>
				<dl class="lc-access__row">
					<dt>TEL</dt>
					<dd><a href="tel:<?php echo esc_attr( $company_tel_num ); ?>"><?php echo esc_html( $company_tel_disp ); ?></a></dd>
				</dl>
				<dl class="lc-access__row">
					<dt>HOURS</dt>
					<dd><?php echo esc_html( $company_tel_note ); ?></dd>
				</dl>
				<dl class="lc-access__row">
					<dt>ACCESS</dt>
					<dd>JR宗谷本線「南稚内駅」より車で約5分／徒歩約30分</dd>
				</dl>
				<div class="lc-access__cta">
					<a href="<?php echo esc_url( $map_open ); ?>" target="_blank" rel="noopener" class="lc-access__btn lc-access__btn--primary">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
						Google マップで開く
					</a>
					<a href="tel:<?php echo esc_attr( $company_tel_num ); ?>" class="lc-access__btn lc-access__btn--ghost">電話で問い合わせ</a>
				</div>
			</div>
			<div class="lc-access__map" aria-label="本社の地図">
				<?php if ( $map_embed && strpos( $map_embed, '<iframe' ) !== false ) : ?>
					<?php echo wp_kses( $map_embed, array(
						'iframe' => array(
							'src' => 1, 'width' => 1, 'height' => 1, 'frameborder' => 1,
							'allowfullscreen' => 1, 'loading' => 1, 'referrerpolicy' => 1, 'style' => 1,
						),
					) ); ?>
				<?php else : ?>
					<div class="lc-access__map-pin">
						<svg viewBox="0 0 24 32" fill="#D63C26"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 20 12 20s12-11 12-20c0-6.6-5.4-12-12-12z"/><circle cx="12" cy="12" r="5" fill="#fff"/></svg>
					</div>
					<span class="lc-access__map-hint">[ Google Maps embed placeholder ]</span>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- ============ Contact band ============ -->
	<section class="lc-contact-band" id="contact">
		<div>
			<h3>稚内の不動産のことなら、ぜひご相談を。</h3>
			<p>賃貸・売買・査定・空き家のご相談まで、まずはお気軽にお問い合わせください。<br />担当者が責任を持ってご案内します。</p>
		</div>
		<div class="lc-contact-band__cta">
			<a href="<?php echo esc_url( $contact_url ); ?>" class="lc-contact-band__btn lc-contact-band__btn--primary">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
				メールで問い合わせ
			</a>
			<a href="tel:<?php echo esc_attr( $company_tel_num ); ?>" class="lc-contact-band__btn lc-contact-band__btn--ghost">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57-.35-.11-.74-.03-1.02.24l-2.2 2.2c-2.83-1.44-5.15-3.75-6.59-6.58l2.2-2.21c.28-.27.36-.66.25-1.01C8.7 6.45 8.5 5.25 8.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.5c0-.55-.45-1-1-1z"/></svg>
				<?php echo esc_html( $company_tel_disp ); ?>
			</a>
		</div>
	</section>

</main>

<?php
get_footer();
