<?php
/**
 * Template Name: 副読本 — ハブ（トップ）
 * Template Post Type: page
 *
 * /recommend/ に割り当てる副読本ハブページ。
 * ミッションカード・章順インデックス・リンク集・ポリシーを静的に持つ。
 * モック: _preview/Recommend.html
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* rec-shell を body クラスに追加（recommend.css のスコープ指定に必要） */
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'rec-shell';
	return $classes;
} );

get_header();

/* ---------- ヘルパー：副読本配下ページのパーマリンク取得 ---------- */
$rec_url = function ( $path ) {
	$page = get_page_by_path( $path );
	return $page ? get_permalink( $page ) : home_url( '/' . trim( $path, '/' ) . '/' );
};

$contact_url = get_theme_mod( 'lc_chat_fallback_url', home_url( '/contact/' ) );
$archive_url = post_type_exists( 'fudo' ) ? get_post_type_archive_link( 'fudo' ) : home_url( '/fudo/' );
?>

<!-- ===== パンくず ===== -->
<div class="lc-crumb-bar">
	<div class="lc-crumb-bar__inner">
		<?php lc_breadcrumb(); ?>
	</div>
</div>

<?php while ( have_posts() ) : the_post(); ?>

<!-- ===== A · サブヘッダー ===== -->
<section class="rec-subhead" aria-labelledby="rec-hub-title">
	<div class="rec-subhead__inner">
		<p class="rec-subhead__eyebrow">RECOMMEND</p>
		<h1 class="rec-subhead__ttl" id="rec-hub-title">
			<?php the_title(); ?>
			<small>SUPPLEMENTARY READINGS</small>
		</h1>
		<p class="rec-subhead__lead">
			契約・引越し・暮らしの「最初に知っておきたいこと」を、短くまとめています。<br>
			個別の判断や手続きの最終確認は、各窓口・専門家にお任せいただく前提で、その手前にある<b>見取り図</b>と<b>公的案内への入口</b>をお届けします。
		</p>
		<div class="rec-subhead__meta">
			<span>
				<b><?php esc_html_e( '最終レビュー', 'syn-ownd-child' ); ?></b>
				<time datetime="<?php echo esc_attr( get_the_modified_date( 'Y-m' ) ); ?>">
					<?php echo esc_html( get_the_modified_date( 'Y年n月' ) ); ?>
				</time>
			</span>
			<span><b>各リンク確認日</b>章末に記載</span>
			<span><b>更新の目安</b>四半期ごとに棚卸し</span>
		</div>
	</div>
</section>

<!-- ===== MAIN ===== -->
<main class="rec-main rec-main--wide" id="main">

	<!-- ===== B · このコーナーでできること ===== -->
	<section class="rec-sec" aria-labelledby="rec-disclaimer">
		<header class="rec-sec__head">
			<span class="rec-sec__num" aria-hidden="true">01</span>
			<h2 class="rec-sec__ttl" id="rec-disclaimer">
				<?php esc_html_e( 'このコーナーでできること、できないこと', 'syn-ownd-child' ); ?>
				<small>SCOPE</small>
			</h2>
		</header>

		<div class="rec-twin" style="gap:14px;">
			<div class="rec-callout rec-callout--ok" role="note" style="margin:0;">
				<span class="rec-callout__icon" aria-hidden="true">✓</span>
				<div class="rec-callout__body">
					<b>できること</b>
					<ul style="margin:0; padding-left:18px; font-size:14px; line-height:1.85;">
						<li>手続きの順序や、契約までの見取り図を示す</li>
						<li>用語の最小限の意味を1〜2文で示す</li>
						<li>稚内市・公的窓口・専門サイトへの入口をまとめる</li>
					</ul>
				</div>
			</div>
			<div class="rec-callout rec-callout--danger" role="note" style="margin:0;">
				<span class="rec-callout__icon" aria-hidden="true">!</span>
				<div class="rec-callout__body">
					<b>できないこと</b>
					<ul style="margin:0; padding-left:18px; font-size:14px; line-height:1.85;">
						<li>個別の契約・物件に関する最終判断</li>
						<li>税務・登記・学区・原状回復の確定的な解釈</li>
						<li>法令の解説（法的助言）</li>
					</ul>
				</div>
			</div>
		</div>

		<p style="margin-top:14px;">
			最終判断は専門家・公的窓口へ。気になる点は
			<a href="<?php echo esc_url( $contact_url ); ?>#contact">お問い合わせ</a>からもどうぞ。
		</p>
	</section>

	<!-- ===== C · いまの状況から探す（ミッションカード） ===== -->
	<section class="rec-hub" aria-labelledby="rec-mission">
		<h2 class="rec-hub__ttl" id="rec-mission">
			いまの状況から探す
			<small>BY MISSION</small>
		</h2>
		<p class="rec-hub__desc">「これから〇〇する」に近いものを選んでみてください。</p>

		<div class="rec-mission-grid">
			<a class="rec-mission" href="<?php echo esc_url( $rec_url( 'recommend/moving' ) ); ?>">
				<span class="rec-mission__step">STEP 01</span>
				<p class="rec-mission__ttl">これから引越しをする</p>
				<p class="rec-mission__desc">タイムライン・役所・ライフライン・稚内ブロックの早見。</p>
				<span class="rec-mission__arrow" aria-hidden="true">→</span>
			</a>
			<a class="rec-mission" href="<?php echo esc_url( $rec_url( 'recommend/contract-rent' ) ); ?>">
				<span class="rec-mission__step">STEP 02</span>
				<p class="rec-mission__ttl">賃貸の契約をひかえている</p>
				<p class="rec-mission__desc">下見チェック、申込〜契約の流れ、初期費用の用語。</p>
				<span class="rec-mission__arrow" aria-hidden="true">→</span>
			</a>
			<a class="rec-mission" href="<?php echo esc_url( $rec_url( 'recommend/buying' ) ); ?>">
				<span class="rec-mission__step">STEP 03</span>
				<p class="rec-mission__ttl">家を買うか検討中</p>
				<p class="rec-mission__desc">「難しい」と感じる理由と、段階の地図。専門家への接続。</p>
				<span class="rec-mission__arrow" aria-hidden="true">→</span>
			</a>
			<a class="rec-mission" href="<?php echo esc_url( $rec_url( 'recommend/money' ) ); ?>">
				<span class="rec-mission__step">STEP 04</span>
				<p class="rec-mission__ttl">お金まわりが不安</p>
				<p class="rec-mission__desc">賃貸費目・売買諸経費・ローンの読みどころ（入口）。</p>
				<span class="rec-mission__arrow" aria-hidden="true">→</span>
			</a>
			<a class="rec-mission" href="<?php echo esc_url( $rec_url( 'recommend/trouble' ) ); ?>">
				<span class="rec-mission__step">STEP 05</span>
				<p class="rec-mission__ttl">住んでからの困りごと</p>
				<p class="rec-mission__desc">安全と相談先がさき。タイプ別は短く論点と窓口だけ。</p>
				<span class="rec-mission__arrow" aria-hidden="true">→</span>
			</a>
			<a class="rec-mission" href="<?php echo esc_url( $rec_url( 'recommend/family-faq' ) ); ?>">
				<span class="rec-mission__step">STEP 06</span>
				<p class="rec-mission__ttl">ファミリーで稚内へ転居</p>
				<p class="rec-mission__desc">補完Q&amp;A（5〜8問）。学区・税は公式へ。</p>
				<span class="rec-mission__arrow" aria-hidden="true">→</span>
			</a>
		</div>
	</section>

	<!-- ===== D · 章順に読む ／ E · 関連リンク集 ===== -->
	<div class="rec-twin">

		<div class="rec-hub" aria-labelledby="rec-order-ttl">
			<h2 class="rec-hub__ttl" id="rec-order-ttl">
				章順に読む
				<small>BY ORDER</small>
			</h2>
			<p class="rec-hub__desc">読みもの感のあるインデックス。短編集として上から。</p>

			<ol class="rec-order">
				<li>
					<div class="rec-order__main">
						<strong><a href="<?php echo esc_url( $rec_url( 'recommend/moving' ) ); ?>">引越し・転入・新生活</a></strong>
						<span>引越し前〜当日のタイムライン／稚内の連絡先一覧。</span>
					</div>
					<span class="rec-order__date"><time datetime="2026-05-20">2026.05.20</time></span>
				</li>
				<li>
					<div class="rec-order__main">
						<strong><a href="<?php echo esc_url( $rec_url( 'recommend/contract-rent' ) ); ?>">賃貸・契約前に押さえること</a></strong>
						<span>下見・申込〜契約の流れ・初期費用の用語。</span>
					</div>
					<span class="rec-order__date"><time datetime="2026-05-20">2026.05.20</time></span>
				</li>
				<li>
					<div class="rec-order__main">
						<strong><a href="<?php echo esc_url( $rec_url( 'recommend/buying' ) ); ?>">売買の流れ（難しさの地図）</a></strong>
						<span>関係者・書類の多さを地図化、深掘りはしません。</span>
					</div>
					<span class="rec-order__date"><time datetime="2026-05-23">2026.05.23</time></span>
				</li>
				<li>
					<div class="rec-order__main">
						<strong><a href="<?php echo esc_url( $rec_url( 'recommend/money' ) ); ?>">お金・税金・ローン（入口級）</a></strong>
						<span>費目の名前と「いつ・誰に」。細目は専門家へ。</span>
					</div>
					<span class="rec-order__date"><time datetime="2026-05-23">2026.05.23</time></span>
				</li>
				<li>
					<div class="rec-order__main">
						<strong><a href="<?php echo esc_url( $rec_url( 'recommend/trouble' ) ); ?>">住宅・賃貸トラブル</a></strong>
						<span>相談先がさき。タイプ別は論点＋窓口だけ。</span>
					</div>
					<span class="rec-order__date"><time datetime="2026-05-20">2026.05.20</time></span>
				</li>
			</ol>
		</div>

		<div class="rec-hub" aria-labelledby="rec-res-ttl">
			<h2 class="rec-hub__ttl" id="rec-res-ttl">
				関連リンク集
				<small>RESOURCES</small>
			</h2>
			<p class="rec-hub__desc">公式・解説サイトのキュレーション。各カテゴリ冒頭に編集メモ。</p>

			<ul class="rec-resources" role="list">
				<li>
					<a href="<?php echo esc_url( $rec_url( 'recommend/resources' ) ); ?>">
						<span>不動産・暮らしの一般リンク</span>
						<span class="rec-resources__count">12 件</span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $rec_url( 'recommend/resources' ) . '#cat-glossary' ); ?>">
						<span>用語・学習リソース</span>
						<span class="rec-resources__count">8 件</span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $rec_url( 'recommend/resources' ) . '#cat-local' ); ?>">
						<span>地域・コミュニティ（稚内・北海道）</span>
						<span class="rec-resources__count">14 件</span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $rec_url( 'recommend/resources' ) . '#cat-partners' ); ?>">
						<span>提携・協力コンテンツ</span>
						<span class="rec-resources__count">5 件</span>
					</a>
				</li>
			</ul>

			<p class="rec-meta-foot" style="margin-top:18px;">
				最終リンク確認：<time datetime="2026-05-20">2026年5月20日</time>　／　棚卸し頻度：四半期
			</p>
		</div>

	</div>

	<!-- ===== G · 情報の取り扱い・更新ポリシー ===== -->
	<section class="rec-sec" aria-labelledby="rec-policy">
		<header class="rec-sec__head">
			<span class="rec-sec__num" aria-hidden="true">02</span>
			<h2 class="rec-sec__ttl" id="rec-policy">
				情報の取り扱い・更新ポリシー
				<small>POLICY</small>
			</h2>
		</header>

		<h3>更新のリズム</h3>
		<ul>
			<li><b>四半期</b>に全ガイドの参照リンク（④）を棚卸し。トップに最終レビュー月を表示します。</li>
			<li>新規ページは公開時に <b>4段テンプレ</b>（30秒サマリー／手順・チェック／補足／参照リンク）でそろえます。</li>
			<li>各リンクには <b>確認日</b>を併記。鮮度をご自身で判断いただけるようにします。</li>
		</ul>

		<h3>引用元の責任分界</h3>
		<p>外部リンク先の内容については、各提供者の責任で運用されています。リンク先の更新・移転・廃止により、当ページの記載と齟齬が生じる場合があります。最終的な内容は <b>各窓口の公式案内</b>を優先してください。</p>

		<h3>誤りのご指摘</h3>
		<p>記載に誤りや不明点を見つけられた場合は、<a href="<?php echo esc_url( $contact_url ); ?>">お問い合わせ</a>よりご連絡をお願いします。確認のうえ修正し、修正履歴を当該ページ末尾に残します。</p>

		<div class="rec-callout rec-callout--warn" role="note">
			<span class="rec-callout__icon" aria-hidden="true">!</span>
			<div class="rec-callout__body">
				<b>断定を避ける領域</b>
				法令・税務・登記・学区・原状回復の基準などは、本コーナーでは断定的な解釈を行いません。論点の整理と公的・専門窓口への入口に留めています。
			</div>
		</div>
	</section>

	<!-- ===== H · お問い合わせへの導線 ===== -->
	<section class="rec-back" aria-labelledby="rec-back-ttl" id="contact">
		<p class="rec-back__text" id="rec-back-ttl">
			<b>物件をお探しの方・ご相談のある方へ。</b><br>
			副読本の内容に関するご質問でも、まずはお気軽にどうぞ。
		</p>
		<div class="rec-back__cta">
			<a class="--primary" href="<?php echo esc_url( $contact_url ); ?>">お問い合わせ</a>
			<a class="--ghost" href="<?php echo esc_url( $archive_url ); ?>">賃貸物件を探す</a>
		</div>
	</section>

</main>

<?php endwhile; ?>

<?php get_footer(); ?>
