<?php
/**
 * Single — Fudou Plugin (post type "fudo")
 * 物件詳細ページ。プラグイン側の DOM (#list_simplepage2 / list_detail / #list_add / #list_other)
 * を可能な限り維持しつつ、価格バナー・ギャラリー・問合せサイドを子テーマで補強。
 *
 * @package SYN_Ownd_Child
 */
get_header();

// fudou プラグイン純正のラベル変換ヘルパー（my_custom_*_print 系、約50関数）を読み込む。
// 関数定義のみのファイルなので副作用はなく、コード値→日本語ラベルの変換をプラグイン任せにできる。
if ( ! function_exists( 'my_custom_torihikitaiyo_print' ) ) {
	$lc_fudo_inc_single = WP_PLUGIN_DIR . '/fudou/inc/inc-single-fudo.php';
	if ( file_exists( $lc_fudo_inc_single ) ) {
		require_once $lc_fudo_inc_single;
	}
}

while ( have_posts() ) : the_post();

	$post_id = get_the_ID();

	// 種別 — bukkenshubetsu (数値、<3000=売買 / ≥3000=賃貸) で判定。旧 kbn/shubetsu もフォールバック。
	$is_sale = lc_is_fudo_sale( $post_id );
	$is_rent = ! $is_sale;
	$kbn_lbl = $is_sale ? '売買' : '賃貸';
	$tag_cls = $is_sale ? 'tag-sale' : 'tag-rent';
	// 物件種別（例: 中古マンション・売地・賃貸アパート）— bukkenshubetsu コードを work-fudo.php マスタで解決
	$bukken_type = lc_fudo_capture_print( 'my_custom_bukkenshubetsu_print', $post_id );

	// メタ — 可能な限り fudou プラグイン純正の my_custom_*_print() でコード値→ラベル変換し、
	// 値が空 or 関数未読込のときのみ旧来の lc_get_fudo_meta() 候補キーへフォールバックする。
	$price     = lc_get_fudo_meta( $post_id, array( 'kakaku', 'price', 'bukken_kakaku' ) );
	$madori    = lc_get_fudo_madori_label( $post_id );
	$menseki   = lc_get_fudo_meta( $post_id, array( 'tatemonomenseki', 'menseki', 'area', 'senyu_menseki' ) );
	$chiku     = lc_get_fudo_meta( $post_id, array( 'tatemonochikunenn', 'chikunen', 'chiku', 'built_year', 'kenchiku_nen' ) );
	$kouzou    = lc_fudo_capture_print( 'my_custom_tatemonokozo_print', $post_id ) ?: lc_get_fudo_meta( $post_id, array( 'kouzou', 'structure' ) );
	$kanrihi   = lc_get_fudo_meta( $post_id, array( 'kanrihi', 'kyoekihi' ) );
	// 敷金・礼金等は「0」だと非表示にするのがプラグインの既定動作（*_print_zero_view フィルタ既定 false）。
	// テーマ側でも同じ規約に合わせ、生メタが '' または '0' のときは呼び出さない。
	$lc_fudo_cost = function ( $meta_key, $print_fn ) use ( $post_id ) {
		$raw = get_post_meta( $post_id, $meta_key, true );
		if ( $raw === '' || $raw === '0' ) { return ''; }
		return lc_fudo_capture_print( $print_fn, $post_id );
	};
	$shikikin  = $lc_fudo_cost( 'kakakushikikin', 'my_custom_kakakushikikin_print' ) ?: lc_get_fudo_meta( $post_id, array( 'shikikin' ) );
	$reikin    = $lc_fudo_cost( 'kakakureikin', 'my_custom_kakakureikin_print' ) ?: lc_get_fudo_meta( $post_id, array( 'reikin' ) );
	$address   = lc_get_fudo_meta( $post_id, array( 'shozaichimeisho', 'shozaichi', 'address', 'jusho' ) );
	$koutsu    = trim( lc_fudo_capture_print( 'my_custom_koutsu1_print', $post_id ) . ' ' . lc_fudo_capture_print( 'my_custom_koutsu2_print', $post_id ) . ' ' . get_post_meta( $post_id, 'koutsusonota', true ) ) ?: lc_get_fudo_meta( $post_id, array( 'koutsu', 'access' ) );
	$torihiki  = lc_fudo_capture_print( 'my_custom_torihikitaiyo_print', $post_id ) ?: lc_get_fudo_meta( $post_id, array( 'torihiki', 'torihiki_taiyou', 'transaction_type', 'transaction' ) );
	$chuusha   = lc_fudo_capture_print( 'my_custom_chushajo_print', $post_id ) ?: lc_get_fudo_meta( $post_id, array( 'chuusha', 'parking' ) );
	$houi      = lc_fudo_capture_print( 'my_custom_heyamuki_print', $post_id ) ?: lc_get_fudo_meta( $post_id, array( 'houi', 'direction' ) );
	$is_pickup  = (bool) lc_get_fudo_meta( $post_id, array( 'pickup', 'osusume' ) );
	$is_new     = ( time() - get_post_time( 'U' ) ) < ( 30 * DAY_IN_SECONDS );
	$ken_code   = lc_get_fudo_meta( $post_id, array( 'shozaichiken' ) );

	// 建物詳細（プラグイン純正フィールド。my_custom_*_print が使えない環境では空のまま非表示）
	$shinchiku       = lc_fudo_capture_print( 'my_custom_tatemonoshinchiku_print', $post_id ); // 新築/中古
	$menseki_hoshiki = lc_fudo_capture_print( 'my_custom_tatemonohosiki_print', $post_id );     // 面積の測定方式
	$balcony         = get_post_meta( $post_id, 'heyabarukoni', true );                          // バルコニー面積
	$kaisu1          = get_post_meta( $post_id, 'tatemonokaisu1', true );                        // 地上階数
	$kaisu2          = get_post_meta( $post_id, 'tatemonokaisu2', true );                        // 地下階数
	$heya_kaisu      = get_post_meta( $post_id, 'heyakaisu', true );                             // 部屋階数
	$bukken_naiyo    = get_post_meta( $post_id, 'bukkennaiyo', true );                           // 部屋/区画番号
	$bukken_soukosu  = get_post_meta( $post_id, 'bukkensoukosu', true );                         // 総戸数/区画数
	$bukken_meikoukai = get_post_meta( $post_id, 'bukkenmeikoukai', true );
	// 物件名（マンション名等）— bukkenmeikoukai が '0'（非公開）でない限り表示する
	$bukken_mei      = ( $bukken_meikoukai !== '0' ) ? get_post_meta( $post_id, 'bukkenmei', true ) : '';
	$zentai_menseki  = get_post_meta( $post_id, 'tatemonozentaimenseki', true );                 // 敷地全体面積
	$nobeyuka_menseki = get_post_meta( $post_id, 'tatemononobeyukamenseki', true );              // 延べ床面積
	$kentiku_menseki = get_post_meta( $post_id, 'tatemonokentikumenseki', true );                // 建築面積

	// 管理情報
	$kanrikeitai = lc_fudo_capture_print( 'my_custom_kanrikeitai_print', $post_id );
	$kanrininn   = lc_fudo_capture_print( 'my_custom_kanrininn_print', $post_id );
	$kanrikumiai = lc_fudo_capture_print( 'my_custom_kanrikumiai_print', $post_id );

	// 間取り詳細
	$madorinaiyo = lc_fudo_capture_print( 'my_custom_madorinaiyo_print', $post_id ); // 各部屋の内訳
	$madoribiko  = get_post_meta( $post_id, 'madoribiko', true );

	// 住宅保険・修繕積立金
	$hoken       = lc_fudo_capture_print( 'my_custom_kakakuhoken_print', $post_id );
	$hoken_kikan = get_post_meta( $post_id, 'kakakuhokenkikan', true );
	$tsumitate   = get_post_meta( $post_id, 'kakakutsumitate', true );

	// 費用内訳（賃貸: 敷/礼に加え保証金・権利金・更新料・敷引）
	$hoshoukin = $lc_fudo_cost( 'kakakuhoshoukin', 'my_custom_kakakuhoshoukin_print' );
	$kenrikin  = $lc_fudo_cost( 'kakakukenrikin', 'my_custom_kakakukenrikin_print' );
	$koushin   = $lc_fudo_cost( 'kakakukoushin', 'my_custom_kakakukoushin_print' );
	$shikibiki = $lc_fudo_cost( 'kakakushikibiki', 'my_custom_kakakushikibiki_print' );

	// 費用内訳（売買・収益: 坪単価・共益費・利回り・借地料）
	$tsubotanka    = lc_fudo_capture_print( 'my_custom_kakakutsubo_print', $post_id );
	$kyouekihi     = get_post_meta( $post_id, 'kakakukyouekihi', true );
	$hyomen_rimawari   = get_post_meta( $post_id, 'kakakuhyorimawari', true );
	$jisshitsu_rimawari = get_post_meta( $post_id, 'kakakurimawari', true );
	$shakuchi      = lc_fudo_capture_print( 'my_custom_shakuchi_print', $post_id );

	// 取引情報
	$genkyo          = lc_fudo_capture_print( 'my_custom_nyukyogenkyo_print', $post_id ); // 現況
	$nyukyo_jiki_txt = lc_fudo_capture_print( 'my_custom_nyukyojiki_print', $post_id );
	$nyukyo_nengetsu = get_post_meta( $post_id, 'nyukyonengetsu', true );
	$nyukyo_syun_txt = lc_fudo_capture_print( 'my_custom_nyukyosyun_print', $post_id );
	$nyukyo          = trim( $nyukyo_jiki_txt . ' ' . $nyukyo_nengetsu . ' ' . $nyukyo_syun_txt );

	// 周辺環境
	$shougaku       = get_post_meta( $post_id, 'shuuhenshougaku', true );
	$chuugaku       = get_post_meta( $post_id, 'shuuhenchuugaku', true );
	$shuuhen_sonota = get_post_meta( $post_id, 'shuuhensonota', true );

	// 土地情報（売地・土地物件で使用。戸建・マンションでは通常すべて空）
	$tochichimoku       = lc_fudo_capture_print( 'my_custom_tochichimoku_print', $post_id );
	$tochiyouto         = lc_fudo_capture_print( 'my_custom_tochiyouto_print', $post_id );
	$tochikeikaku       = lc_fudo_capture_print( 'my_custom_tochikeikaku_print', $post_id );
	$tochichisei        = lc_fudo_capture_print( 'my_custom_tochichisei_print', $post_id );
	$tochikukaku        = get_post_meta( $post_id, 'tochikukaku', true );
	$tochisokutei       = lc_fudo_capture_print( 'my_custom_tochisokutei_print', $post_id );
	$tochishido         = get_post_meta( $post_id, 'tochishido', true );
	$tochisetback       = lc_fudo_capture_print( 'my_custom_tochisetback_print', $post_id );
	$tochisetback2      = get_post_meta( $post_id, 'tochisetback2', true );
	$tochikenpei        = get_post_meta( $post_id, 'tochikenpei', true );
	$tochiyoseki        = get_post_meta( $post_id, 'tochiyoseki', true );
	$tochikenri         = lc_fudo_capture_print( 'my_custom_tochikenri_print', $post_id );
	$tochisetsudo       = lc_fudo_capture_print( 'my_custom_tochisetsudo_print', $post_id );
	$setsudo_houko1     = lc_fudo_capture_print( 'my_custom_tochisetsudohouko1_print', $post_id );
	$setsudo_maguchi1   = get_post_meta( $post_id, 'tochisetsudomaguchi1', true );
	$setsudo_shurui1    = lc_fudo_capture_print( 'my_custom_tochisetsudoshurui1_print', $post_id );
	$setsudo_fukuin1    = get_post_meta( $post_id, 'tochisetsudofukuin1', true );
	$setsudo_ichishitei1 = lc_fudo_capture_print( 'my_custom_tochisetsudoichishitei1_print', $post_id );
	$setsudo_houko2     = lc_fudo_capture_print( 'my_custom_tochisetsudohouko2_print', $post_id );
	$setsudo_maguchi2   = get_post_meta( $post_id, 'tochisetsudomaguchi2', true );
	$setsudo_shurui2    = lc_fudo_capture_print( 'my_custom_tochisetsudoshurui2_print', $post_id );
	$setsudo_fukuin2    = get_post_meta( $post_id, 'tochisetsudofukuin2', true );
	$setsudo_ichishitei2 = lc_fudo_capture_print( 'my_custom_tochisetsudoichishitei2_print', $post_id );
	$tochikokudohou     = lc_fudo_capture_print( 'my_custom_tochikokudohou_print', $post_id );

	// その他
	$target_url    = lc_fudo_capture_print( 'my_custom_targeturl_print', $post_id );
	$shikibesu     = get_post_meta( $post_id, 'shikibesu', true );      // 物件番号
	$keisaikigenbi = get_post_meta( $post_id, 'keisaikigenbi', true );  // 掲載期限日
	$koukaijisha   = lc_fudo_capture_print( 'my_custom_koukaijisha_print', $post_id );
	$jyoutai       = lc_fudo_capture_print( 'my_custom_jyoutai_print', $post_id );
	$tokkinotices  = get_post_meta( $post_id, 'tokkinotices', true );

	// ギャラリー — fudoimg1〜fudoimg30 を解決。0枚ならフィーチャー画像にフォールバック。
	$gallery_urls = lc_get_fudo_gallery_urls( $post_id, 30 );
	$gallery_count = count( $gallery_urls );
	?>

	<div class="lc-crumb-bar">
		<div class="lc-crumb-bar__inner">
			<?php lc_breadcrumb(); ?>
		</div>
	</div>

	<section class="lc-detail-hero">
		<div class="lc-detail-hero__inner">
			<div class="lc-bukken-tags">
				<span class="tag-bukken <?php echo esc_attr( $tag_cls ); ?>"><?php echo esc_html( $kbn_lbl ); ?></span>
				<?php if ( $bukken_type ) : ?><span class="tag-bukken tag-type"><?php echo esc_html( $bukken_type ); ?></span><?php endif; ?>
				<?php if ( $is_pickup ) : ?><span class="tag-bukken tag-pickup">PICK UP</span><?php endif; ?>
				<?php if ( $is_new )    : ?><span class="tag-bukken tag-new">NEW</span><?php endif; ?>
			</div>
			<h1 class="lc-detail-hero__ttl"><?php the_title(); ?></h1>
			<?php if ( $address || $koutsu ) : ?>
				<div class="lc-detail-hero__addr">
					<?php
					// $koutsu は my_custom_koutsu1_print()/koutsu2_print() 由来で <span> を含み得るため wp_kses_post() を使用。
					echo esc_html( $address );
					echo ( $address && $koutsu ) ? ' ／ ' : '';
					echo wp_kses_post( $koutsu );
					?>
				</div>
			<?php endif; ?>

			<?php if ( $gallery_count > 0 ) : ?>
			<div class="lc-gallery lc-gallery--count-<?php echo min( 5, (int) $gallery_count ); ?>">
				<?php
				// 表示は最大 5 枚（main 1 + サブ 4）、それ以外は最後のセルに「すべて見る」ボタンを重ねて隠す。
				$shown_max = min( 5, $gallery_count );
				for ( $i = 0; $i < $shown_max; $i++ ) :
					$cell_cls = ( $i === 0 ) ? 'lc-gallery__cell lc-gallery__cell--main' : 'lc-gallery__cell';
				?>
					<div class="<?php echo esc_attr( $cell_cls ); ?>" style="background-image:url('<?php echo esc_url( $gallery_urls[ $i ] ); ?>');">
						<?php if ( $i === 4 && $gallery_count > 5 ) : ?>
							<button class="lc-gallery__more" type="button" aria-label="すべての写真を見る">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
									<rect x="3" y="3" width="7" height="7" rx="1"/>
									<rect x="14" y="3" width="7" height="7" rx="1"/>
									<rect x="3" y="14" width="7" height="7" rx="1"/>
									<rect x="14" y="14" width="7" height="7" rx="1"/>
								</svg>
								すべての写真を見る（<?php echo (int) $gallery_count; ?>枚）
							</button>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<div class="lc-main lc-main--detail">

		<main id="list_simplepage2">

			<div class="lc-price-card <?php echo $is_rent ? '' : 'is-sale'; ?>">
				<div class="lc-price-card__price">
					<span class="lc-price-card__label"><?php echo esc_html( ( $is_rent ? 'RENT / 月額賃料' : 'SALE / 販売価格' ) ); ?></span>
					<?php if ( $price ) :
						$price_fmt = lc_format_price( $price );
						?>
						<span class="lc-price-card__amount"><?php echo esc_html( $price_fmt ); ?>
							<?php if ( $kanrihi || $shikikin || $reikin ) : ?>
								<sub><?php echo esc_html( implode( ' ／ ', array_filter( array(
									$kanrihi  ? '管理費 ' . $kanrihi : '',
									$shikikin ? '敷 ' . $shikikin   : '',
									$reikin   ? '礼 ' . $reikin     : '',
								) ) ) ); ?></sub>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</div>
				<div class="lc-price-card__specs">
					<div class="lc-spec-cell">
						<span class="lc-spec-cell__label">間取り</span>
						<span class="lc-spec-cell__value"><?php echo esc_html( $madori ?: '—' ); ?></span>
					</div>
					<div class="lc-spec-cell">
						<span class="lc-spec-cell__label">専有面積</span>
						<span class="lc-spec-cell__value">
							<?php echo esc_html( $menseki ?: '—' ); ?>
							<?php if ( $menseki && strpos( (string) $menseki, 'm' ) === false ) : ?><small>m²</small><?php endif; ?>
						</span>
					</div>
					<div class="lc-spec-cell">
						<span class="lc-spec-cell__label">築年</span>
						<span class="lc-spec-cell__value"><?php echo esc_html( $chiku ?: '—' ); ?></span>
					</div>
					<div class="lc-spec-cell">
						<span class="lc-spec-cell__label">構造</span>
						<span class="lc-spec-cell__value"><?php echo esc_html( $kouzou ?: '—' ); ?></span>
					</div>
				</div>
			</div>

			<?php if ( get_the_content() ) : ?>
				<section class="lc-det-section">
					<h2>物件紹介 <small>PROPERTY OVERVIEW</small></h2>
					<div class="lc-prose"><?php the_content(); ?></div>
				</section>
			<?php endif; ?>

			<section class="lc-det-section">
				<h2>物件概要 <small>SPECIFICATIONS</small></h2>
				<div class="list_detail">
					<div class="twocol">
						<table id="list_add">
							<tbody>
								<?php if ( $address )  : ?>
								<tr><th>所在地</th><td><?php echo esc_html( trim( $address . ( $bukken_mei ? '　' . $bukken_mei : '' ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $koutsu )   : ?><tr><th>交通</th>  <td><?php echo wp_kses_post( $koutsu ); // <span class="koutsubus"> 等のHTMLを含み得る ?></td></tr><?php endif; ?>
								<?php if ( $madori )   : ?><tr><th>間取り</th><td><?php echo esc_html( $madori );   ?></td></tr><?php endif; ?>
								<?php if ( $menseki )  : ?>
								<tr><th>専有面積</th><td><?php echo esc_html( $menseki . ( strpos( (string) $menseki, 'm' ) === false ? 'm²' : '' ) . ( $menseki_hoshiki ? '（' . $menseki_hoshiki . '）' : '' ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $chiku )    : ?><tr><th>築年月</th><td><?php echo esc_html( $chiku );    ?></td></tr><?php endif; ?>
								<?php if ( $shinchiku ): ?><tr><th>新築 / 中古</th><td><?php echo esc_html( $shinchiku ); ?></td></tr><?php endif; ?>
								<?php if ( $kouzou )   : ?><tr><th>建物構造</th><td><?php echo esc_html( $kouzou );   ?></td></tr><?php endif; ?>
								<?php if ( $houi )     : ?><tr><th>向き</th>  <td><?php echo esc_html( $houi );     ?></td></tr><?php endif; ?>
								<?php if ( $balcony )  : ?><tr><th>バルコニー</th><td><?php echo esc_html( $balcony . 'm²' ); ?></td></tr><?php endif; ?>
								<?php if ( $kaisu1 || $kaisu2 || $heya_kaisu ) : ?>
								<tr><th>建物階数</th><td><?php echo esc_html( trim( implode( ' ', array_filter( array(
									$kaisu1     ? '地上' . $kaisu1 . '階' : '',
									$kaisu2     ? '地下' . $kaisu2 . '階' : '',
									$heya_kaisu ? '（' . $heya_kaisu . '階部分）' : '',
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( ( $bukken_naiyo && $bukken_meikoukai !== '0' ) || $bukken_soukosu ) : ?>
								<tr><th>部屋番号 / 総戸数</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array(
									( $bukken_naiyo && $bukken_meikoukai !== '0' ) ? $bukken_naiyo : '',
									$bukken_soukosu ? '総戸数' . $bukken_soukosu : '',
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $zentai_menseki || $nobeyuka_menseki ) : ?>
								<tr><th>敷地面積 / 延床面積</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array(
									$zentai_menseki   ? $zentai_menseki . 'm²' : '',
									$nobeyuka_menseki ? $nobeyuka_menseki . 'm²' : '',
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $kentiku_menseki ) : ?><tr><th>建築面積</th><td><?php echo esc_html( $kentiku_menseki . 'm²' ); ?></td></tr><?php endif; ?>
								<?php if ( $kanrikeitai || $kanrininn || $kanrikumiai ) : ?>
								<tr><th>管理形態</th><td><?php echo esc_html( trim( implode( ' ', array_filter( array( $kanrikeitai, $kanrininn, $kanrikumiai ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $madorinaiyo || $madoribiko ) : ?>
								<tr><th>間取り詳細</th><td><?php
									echo wp_kses_post( $madorinaiyo ); // my_custom_madorinaiyo_print() は <span> 付きHTMLを返すため esc_html() 不可
									if ( $madorinaiyo && $madoribiko ) { echo '<br>'; }
									echo esc_html( $madoribiko );
								?></td></tr>
								<?php endif; ?>
							</tbody>
						</table>
						<table id="list_other">
							<tbody>
								<?php if ( $price )    : ?><tr><th><?php echo $is_rent ? '賃料' : '販売価格'; ?></th><td><?php echo esc_html( lc_format_price( $price ) ); ?></td></tr><?php endif; ?>
								<?php if ( $kanrihi )  : ?><tr><th>管理費</th><td><?php echo esc_html( $kanrihi );  ?></td></tr><?php endif; ?>
								<?php if ( $shikikin || $reikin ) : ?><tr><th>敷金 / 礼金</th><td><?php echo esc_html( ( $shikikin ?: '—' ) . ' ／ ' . ( $reikin ?: '—' ) ); ?></td></tr><?php endif; ?>
								<?php if ( $hoshoukin || $kenrikin ) : ?>
								<tr><th>保証金 / 権利金</th><td><?php echo esc_html( ( $hoshoukin ?: '—' ) . ' ／ ' . ( $kenrikin ?: '—' ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $koushin || $shikibiki ) : ?>
								<tr><th>更新料 / 償却・敷引</th><td><?php echo esc_html( ( $koushin ?: '—' ) . ' ／ ' . ( $shikibiki ?: '—' ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tsubotanka ) : ?><tr><th>坪単価</th><td><?php echo esc_html( $tsubotanka ); ?></td></tr><?php endif; ?>
								<?php if ( $kyouekihi ) : ?><tr><th>管理費 / 共益費</th><td><?php echo esc_html( apply_filters( 'fudou_number_format', $kyouekihi ) . '円' ); ?></td></tr><?php endif; ?>
								<?php if ( $hyomen_rimawari || $jisshitsu_rimawari ) : ?>
								<tr><th>表面利回り / 実質利回り</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array(
									$hyomen_rimawari    ? $hyomen_rimawari . '%'    : '',
									$jisshitsu_rimawari ? $jisshitsu_rimawari . '%' : '',
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $shakuchi ) : ?><tr><th>借地条件</th><td><?php echo esc_html( $shakuchi ); ?></td></tr><?php endif; ?>
								<?php if ( $hoken || $hoken_kikan || $tsumitate ) : ?>
								<tr><th>住宅保険 / 修繕積立金</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array(
									trim( $hoken . ( $hoken_kikan ? $hoken_kikan . '年' : '' ) ),
									$tsumitate ? '修繕積立金 ' . apply_filters( 'fudou_number_format', $tsumitate ) . '円' : '',
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $chuusha )  : ?><tr><th>駐車場</th><td><?php echo esc_html( $chuusha );  ?></td></tr><?php endif; ?>
								<?php if ( $torihiki ) : ?><tr><th>取引態様</th><td><?php echo esc_html( $torihiki );?></td></tr><?php endif; ?>
								<?php if ( $genkyo )   : ?><tr><th>現況</th>    <td><?php echo esc_html( $genkyo );  ?></td></tr><?php endif; ?>
								<?php if ( $nyukyo )   : ?><tr><th>引渡 / 入居時期</th><td><?php echo esc_html( $nyukyo );  ?></td></tr><?php endif; ?>

								<?php if ( $tochichimoku || $tochiyouto || $tochikeikaku || $tochichisei || $tochikukaku || $tochishido || $tochisetback || $tochisetback2 || $tochikenpei || $tochiyoseki || $tochikenri || $tochisetsudo || $setsudo_houko1 || $setsudo_houko2 || $tochikokudohou ) : ?>
								<tr class="lc-table-divider"><td colspan="2">土地情報</td></tr>
								<?php if ( $tochichimoku || $tochiyouto ) : ?>
								<tr><th>地目 / 用途地域</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $tochichimoku, $tochiyouto ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tochikeikaku || $tochichisei ) : ?>
								<tr><th>都市計画 / 地勢</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $tochikeikaku, $tochichisei ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tochikukaku || $tochisokutei ) : ?>
								<tr><th>区画面積 / 測定方式</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $tochikukaku ? $tochikukaku . 'm²' : '', $tochisokutei ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tochishido ) : ?><tr><th>私道負担面積</th><td><?php echo esc_html( $tochishido . 'm²' ); ?></td></tr><?php endif; ?>
								<?php if ( $tochisetback || $tochisetback2 ) : ?>
								<tr><th>セットバック</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $tochisetback, $tochisetback2 ? $tochisetback2 . 'm²' : '' ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tochikenpei || $tochiyoseki ) : ?>
								<tr><th>建ぺい率 / 容積率</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $tochikenpei ? $tochikenpei . '%' : '', $tochiyoseki ? $tochiyoseki . '%' : '' ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tochikenri || $tochisetsudo ) : ?>
								<tr><th>土地権利 / 接道状況</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $tochikenri, $tochisetsudo ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $setsudo_houko1 || $setsudo_maguchi1 || $setsudo_shurui1 || $setsudo_fukuin1 || $setsudo_ichishitei1 ) : ?>
								<tr><th>接道1</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array(
									$setsudo_houko1,
									$setsudo_maguchi1 ? '間口' . $setsudo_maguchi1 . 'm' : '',
									$setsudo_shurui1,
									$setsudo_fukuin1  ? '幅員' . $setsudo_fukuin1 . 'm' : '',
									$setsudo_ichishitei1,
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $setsudo_houko2 || $setsudo_maguchi2 || $setsudo_shurui2 || $setsudo_fukuin2 || $setsudo_ichishitei2 ) : ?>
								<tr><th>接道2</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array(
									$setsudo_houko2,
									$setsudo_maguchi2 ? '間口' . $setsudo_maguchi2 . 'm' : '',
									$setsudo_shurui2,
									$setsudo_fukuin2  ? '幅員' . $setsudo_fukuin2 . 'm' : '',
									$setsudo_ichishitei2,
								) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $tochikokudohou ) : ?><tr><th>国土法届出</th><td><?php echo esc_html( $tochikokudohou ); ?></td></tr><?php endif; ?>
								<?php endif; ?>

								<?php if ( $shikibesu || $keisaikigenbi || $koukaijisha || $jyoutai || $target_url || $tokkinotices ) : ?>
								<tr class="lc-table-divider"><td colspan="2">その他</td></tr>
								<?php if ( $shikibesu || $keisaikigenbi ) : ?>
								<tr><th>物件番号 / 掲載期限日</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $shikibesu, $keisaikigenbi ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $koukaijisha || $jyoutai ) : ?>
								<tr><th>自社物 / 状態</th><td><?php echo esc_html( trim( implode( ' ／ ', array_filter( array( $koukaijisha, $jyoutai ) ) ) ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( $target_url ) : ?><tr><th>関連URL</th><td><?php echo wp_kses_post( $target_url ); ?></td></tr><?php endif; ?>
								<?php if ( $tokkinotices ) : ?><tr><th>特記事項</th><td><?php echo esc_html( $tokkinotices ); ?></td></tr><?php endif; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</section>

			<?php
			// 設備 — メタ 'setsubi'（スラッシュ区切り数値コード）を $work_setsubi マスタで名前に解決
			$setsubi_items = lc_get_fudo_setsubi_names( $post_id );
			if ( $setsubi_items ) :
				?>
				<section class="lc-det-section">
					<h2>設備・条件 <small>FACILITIES</small></h2>
					<div class="lc-facility">
						<?php foreach ( $setsubi_items as $s ) : ?>
							<span class="is-on"><?php echo esc_html( $s['name'] ); ?></span>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			// 地図埋め込み: 'map_embed' に Google Maps の埋め込み HTML or URL
			$map_embed = get_post_meta( $post_id, 'map_embed', true );
			$lat       = get_post_meta( $post_id, 'lat', true );
			$lng       = get_post_meta( $post_id, 'lng', true );
			?>
			<section class="lc-det-section">
				<h2>周辺・アクセス <small>MAP & SURROUNDINGS</small></h2>
				<?php
				$lc_pref_map = array(
					1=>'北海道',  2=>'青森県',  3=>'岩手県',  4=>'宮城県',  5=>'秋田県',
					6=>'山形県',  7=>'福島県',  8=>'茨城県',  9=>'栃木県', 10=>'群馬県',
					11=>'埼玉県', 12=>'千葉県', 13=>'東京都', 14=>'神奈川県',15=>'新潟県',
					16=>'富山県', 17=>'石川県', 18=>'福井県', 19=>'山梨県', 20=>'長野県',
					21=>'岐阜県', 22=>'静岡県', 23=>'愛知県', 24=>'三重県', 25=>'滋賀県',
					26=>'京都府', 27=>'大阪府', 28=>'兵庫県', 29=>'奈良県', 30=>'和歌山県',
					31=>'鳥取県', 32=>'島根県', 33=>'岡山県', 34=>'広島県', 35=>'山口県',
					36=>'徳島県', 37=>'香川県', 38=>'愛媛県', 39=>'高知県', 40=>'福岡県',
					41=>'佐賀県', 42=>'長崎県', 43=>'熊本県', 44=>'大分県', 45=>'宮崎県',
					46=>'鹿児島県',47=>'沖縄県',
				);
				$pref_name   = isset( $lc_pref_map[ (int) $ken_code ] ) ? $lc_pref_map[ (int) $ken_code ] : '';
				$map_address = $pref_name . $address;
				?>
				<?php if ( $map_embed && strpos( $map_embed, '<iframe' ) !== false ) : ?>
					<div class="lc-map-wrap"><?php echo wp_kses( $map_embed, array( 'iframe' => array( 'src' => 1, 'width' => 1, 'height' => 1, 'frameborder' => 1, 'allowfullscreen' => 1, 'loading' => 1, 'referrerpolicy' => 1, 'style' => 1 ) ) ); ?></div>
				<?php elseif ( $lat && $lng ) : ?>
					<div class="lc-map-wrap">
						<iframe src="https://www.google.com/maps?q=<?php echo esc_attr( $lat ); ?>,<?php echo esc_attr( $lng ); ?>&output=embed" width="100%" height="100%" frameborder="0" loading="lazy" style="border:0; min-height:380px;"></iframe>
					</div>
				<?php elseif ( $map_address ) : ?>
					<div class="lc-map-wrap">
						<iframe src="https://www.google.com/maps?q=<?php echo rawurlencode( $map_address ); ?>&output=embed" width="100%" height="100%" frameborder="0" loading="lazy" style="border:0; min-height:380px;"></iframe>
					</div>
				<?php else : ?>
					<div class="lc-map-wrap" aria-label="地図プレースホルダ">
						<div class="lc-map-wrap__pin">
							<svg viewBox="0 0 24 32" fill="#D63C26"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 20 12 20s12-11 12-20c0-6.6-5.4-12-12-12z"/><circle cx="12" cy="12" r="5" fill="#fff"/></svg>
						</div>
						<span class="lc-map-wrap__hint">[ Google Maps embed placeholder ]</span>
					</div>
				<?php endif; ?>

				<?php
				// 校区プラグイン（不動産プラグイン拡張、未導入時は何も出力されない）
				ob_start();
				do_action( 'kouku_print', $post_id );
				$kouku_html = trim( ob_get_clean() );
				?>
				<?php if ( $kouku_html || $shougaku || $chuugaku || $shuuhen_sonota ) : ?>
					<div class="lc-prose lc-surroundings">
						<?php echo wp_kses_post( $kouku_html ); ?>
						<?php if ( $shougaku )       : ?><p><?php echo esc_html( $shougaku ); ?></p><?php endif; ?>
						<?php if ( $chuugaku )       : ?><p><?php echo esc_html( $chuugaku ); ?></p><?php endif; ?>
						<?php if ( $shuuhen_sonota ) : ?><p><?php echo esc_html( $shuuhen_sonota ); ?></p><?php endif; ?>
					</div>
				<?php endif; ?>
			</section>

			<?php
			// 関連物件 — 不動産プラグインの専用ウィジェット（おすすめ物件・新着物件など）を配置するエリア。
			// 管理画面：外観 → ウィジェット →「物件詳細：関連物件」にプラグイン提供ウィジェットをドラッグ。
			if ( is_active_sidebar( 'lc-single-fudo-related' ) ) :
			?>
				<section class="lc-related">
					<div class="lc-related__inner">
						<?php dynamic_sidebar( 'lc-single-fudo-related' ); ?>
					</div>
				</section>
			<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
				<section class="lc-related">
					<div class="lc-related__inner">
						<div class="lc-related__notice" role="note">
							<strong>関連物件エリアが未設定です。</strong><br />
							<small>外観 → ウィジェット →「物件詳細：関連物件」に、不動産プラグインの「おすすめ物件」「新着物件」等のウィジェットを配置してください。<br />（このメッセージは編集権限者にのみ表示されます）</small>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<div class="lc-pageback">
				<?php $back = post_type_exists( 'fudo' ) ? get_post_type_archive_link( 'fudo' ) : home_url( '/' ); ?>
				<a href="<?php echo esc_url( $back ); ?>">← 一覧へ戻る</a>
			</div>

		</main>

		<aside class="lc-detail-side">
			<div id="toiawasesaki">
				<div class="lc-toi__head">
					<small>CONTACT</small>
					<h3>この物件について<br />お問い合わせ</h3>
				</div>
				<div class="lc-toi__body">
					<?php
					$contact_url = get_theme_mod( 'lc_chat_fallback_url', home_url( '/contact/' ) );
					$tel         = preg_replace( '/[^0-9]/', '', get_theme_mod( 'lc_company_tel', '01623288 77' ) );
					$tel_display = get_theme_mod( 'lc_company_tel', '0162-32-8877' );

					// 問い合わせフォーム（Contact Form 7）へ物件情報を渡す。
					// bukken_title / bukken_no は hidden フィールド（"default:get"）でメール本文へ、
					// your-subject / your-message は表示フィールドへの事前入力として使う。
					// add_query_arg() は値を自動エスケープしないため、http_build_query() で組み立てる。
					// 出力時は esc_url() を使わないこと — esc_url() は %0d/%0a を無条件で除去するため、
					// メッセージ本文に含めた改行（%0A）が消えてしまう。ここは自前で組み立てた安全な
					// URL（home_url() 由来のベースURL + http_build_query() の値のみ）なので esc_attr() で足りる。
					$bukken_title = get_the_title( $post_id );
					$contact_sep  = ( strpos( $contact_url, '?' ) === false ) ? '?' : '&';

					$mail_message = "物件名：{$bukken_title}\n物件管理番号：{$shikibesu}\n\nお問い合わせ内容：\n";
					$mail_params  = array(
						'bukken_title' => $bukken_title,
						'bukken_no'    => $shikibesu,
						'your-subject' => $bukken_title,
						'your-message' => $mail_message,
					);
					$mail_url = $contact_url . $contact_sep . http_build_query( $mail_params );

					// 内見・来店予約はメッセージ本文に希望日時の記入欄をあらかじめ差し込む。
					$visit_message = "物件名：{$bukken_title}\n物件管理番号：{$shikibesu}"
						. "\n\n【内見希望日時】\n第一希望：\n第二希望：\n\n【その他ご要望】\n";
					$visit_params  = array(
						'bukken_title' => $bukken_title,
						'bukken_no'    => $shikibesu,
						'your-subject' => $bukken_title . '（内見予約）',
						'your-message' => $visit_message,
					);
					$visit_url = $contact_url . $contact_sep . http_build_query( $visit_params );
					?>
					<a class="lc-toi__btn lc-toi__btn--primary" href="<?php echo esc_attr( $mail_url ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						メールで問い合わせ
					</a>
					<a class="lc-toi__btn lc-toi__btn--ghost" href="<?php echo esc_attr( $visit_url ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 11H1l8-8v6h8l-8 8v-6z"/></svg>
						内見・来店予約
					</a>
					<div class="lc-toi__tel">
						<small>電話でのお問い合わせ</small>
						<a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $tel_display ); ?></a>
						<div class="lc-toi__tel-note"><?php echo esc_html( get_theme_mod( 'lc_company_tel_note', '受付 9:00-18:00 ／ 日曜定休' ) ); ?></div>
					</div>
				</div>
			</div>
		</aside>
	</div>

<?php
endwhile;

get_footer();
