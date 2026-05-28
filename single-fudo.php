<?php
/**
 * Single — Fudou Plugin (post type "fudo")
 * 物件詳細ページ。プラグイン側の DOM (#list_simplepage2 / list_detail / #list_add / #list_other)
 * を可能な限り維持しつつ、価格バナー・ギャラリー・問合せサイドを子テーマで補強。
 *
 * @package SYN_Ownd_Child
 */
get_header();

while ( have_posts() ) : the_post();

	$post_id = get_the_ID();

	// 種別 — bukkenshubetsu (数値、<3000=売買 / ≥3000=賃貸) で判定。旧 kbn/shubetsu もフォールバック。
	$is_sale = lc_is_fudo_sale( $post_id );
	$is_rent = ! $is_sale;
	$kbn_lbl = $is_sale ? '売買' : '賃貸';
	$tag_cls = $is_sale ? 'tag-sale' : 'tag-rent';

	// メタ — FUDOU_REFERENCE.md の主キーを先頭に、旧仕様候補を後続に並べる。
	$price     = lc_get_fudo_meta( $post_id, array( 'kakaku', 'price', 'bukken_kakaku' ) );
	$madori    = lc_get_fudo_madori_label( $post_id );
	$menseki   = lc_get_fudo_meta( $post_id, array( 'menseki', 'area', 'senyu_menseki' ) );
	$chiku     = lc_get_fudo_meta( $post_id, array( 'chikunen', 'chiku', 'built_year', 'kenchiku_nen' ) );
	$kouzou    = lc_get_fudo_meta( $post_id, array( 'kouzou', 'structure' ) );
	$kanrihi   = lc_get_fudo_meta( $post_id, array( 'kanrihi', 'kyoekihi' ) );
	$shikikin  = lc_get_fudo_meta( $post_id, array( 'shikikin' ) );
	$reikin    = lc_get_fudo_meta( $post_id, array( 'reikin' ) );
	$address   = lc_get_fudo_meta( $post_id, array( 'shozaichimeisho', 'shozaichi', 'address', 'jusho' ) );
	$koutsu    = lc_get_fudo_meta( $post_id, array( 'koutsu', 'access' ) );
	$nyukyo    = lc_get_fudo_meta( $post_id, array( 'nyukyo', 'nyukyo_kanou', 'available' ) );
	$torihiki  = lc_get_fudo_meta( $post_id, array( 'torihiki', 'torihiki_taiyou', 'transaction_type', 'transaction' ) );
	$chuusha   = lc_get_fudo_meta( $post_id, array( 'chuusha', 'parking' ) );
	$houi      = lc_get_fudo_meta( $post_id, array( 'houi', 'direction' ) );
	$is_pickup  = (bool) lc_get_fudo_meta( $post_id, array( 'pickup', 'osusume' ) );
	$is_new     = ( time() - get_post_time( 'U' ) ) < ( 30 * DAY_IN_SECONDS );
	$ken_code   = lc_get_fudo_meta( $post_id, array( 'shozaichiken' ) );

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
				<?php if ( $is_pickup ) : ?><span class="tag-bukken tag-pickup">PICK UP</span><?php endif; ?>
				<?php if ( $is_new )    : ?><span class="tag-bukken tag-new">NEW</span><?php endif; ?>
			</div>
			<h1 class="lc-detail-hero__ttl"><?php the_title(); ?></h1>
			<?php if ( $address || $koutsu ) : ?>
				<div class="lc-detail-hero__addr">
					<?php echo esc_html( trim( ( $address ? $address : '' ) . ( $koutsu ? ' ／ ' . $koutsu : '' ) ) ); ?>
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
								<?php if ( $address )  : ?><tr><th>所在地</th><td><?php echo esc_html( $address );  ?></td></tr><?php endif; ?>
								<?php if ( $koutsu )   : ?><tr><th>交通</th>  <td><?php echo esc_html( $koutsu );   ?></td></tr><?php endif; ?>
								<?php if ( $madori )   : ?><tr><th>間取り</th><td><?php echo esc_html( $madori );   ?></td></tr><?php endif; ?>
								<?php if ( $menseki )  : ?><tr><th>専有面積</th><td><?php echo esc_html( $menseki ); ?></td></tr><?php endif; ?>
								<?php if ( $chiku )    : ?><tr><th>築年月</th><td><?php echo esc_html( $chiku );    ?></td></tr><?php endif; ?>
								<?php if ( $kouzou )   : ?><tr><th>構造</th>  <td><?php echo esc_html( $kouzou );   ?></td></tr><?php endif; ?>
								<?php if ( $houi )     : ?><tr><th>方位</th>  <td><?php echo esc_html( $houi );     ?></td></tr><?php endif; ?>
							</tbody>
						</table>
						<table id="list_other">
							<tbody>
								<?php if ( $price )    : ?><tr><th><?php echo $is_rent ? '賃料' : '販売価格'; ?></th><td><?php echo esc_html( lc_format_price( $price ) ); ?></td></tr><?php endif; ?>
								<?php if ( $kanrihi )  : ?><tr><th>管理費</th><td><?php echo esc_html( $kanrihi );  ?></td></tr><?php endif; ?>
								<?php if ( $shikikin || $reikin ) : ?><tr><th>敷金 / 礼金</th><td><?php echo esc_html( ( $shikikin ?: '—' ) . ' ／ ' . ( $reikin ?: '—' ) ); ?></td></tr><?php endif; ?>
								<?php if ( $chuusha )  : ?><tr><th>駐車場</th><td><?php echo esc_html( $chuusha );  ?></td></tr><?php endif; ?>
								<?php if ( $torihiki ) : ?><tr><th>取引態様</th><td><?php echo esc_html( $torihiki );?></td></tr><?php endif; ?>
								<?php if ( $nyukyo )   : ?><tr><th>入居可能</th><td><?php echo esc_html( $nyukyo );  ?></td></tr><?php endif; ?>
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
					?>
					<a class="lc-toi__btn lc-toi__btn--primary" href="<?php echo esc_url( add_query_arg( 'bukken_id', $post_id, $contact_url ) ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						メールで問い合わせ
					</a>
					<a class="lc-toi__btn lc-toi__btn--ghost" href="<?php echo esc_url( add_query_arg( array( 'bukken_id' => $post_id, 'type' => 'visit' ), $contact_url ) ); ?>">
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
