<?php
/**
 * Archive — Fudou Plugin (post type "fudo")
 * 不動産プラグインの物件一覧（賃貸／売買アーカイブ）テンプレ。
 * lc_build_fudo_query_args() で構築したクエリを WP_Query へ渡し、
 * 並び替え・絞り込み・ページネーションを子テーマで完結させる。
 *
 * モック: _preview/Archive.html
 *
 * @package SYN_Ownd_Child
 */
get_header();

$queried = get_queried_object();

// kbn パラメータ — 未指定なら全件モード（ラベル「物件一覧」、カードタグは投稿ごと自動判定）
$has_kbn_filter = isset( $_GET['kbn'] ) && in_array( $_GET['kbn'], array( 'rent', 'sale' ), true );
$kbn     = $has_kbn_filter ? $_GET['kbn']                                  : '';
$kbn_lbl = $has_kbn_filter ? ( $kbn === 'sale' ? '売買' : '賃貸' )         : '';
$tag_cls = $has_kbn_filter ? ( $kbn === 'sale' ? 'tag-sale' : 'tag-rent' ) : '';

// フィルター取得 → クエリ引数
$filters = lc_get_fudo_filters_from_request();
if ( empty( $filters['kbn'] ) && isset( $_GET['kbn'] ) ) {
	$filters['kbn'] = $kbn;
}
if ( empty( $filters['posts_per_page'] ) ) {
	$filters['posts_per_page'] = (int) get_option( 'posts_per_page', 12 );
}

$query_args = lc_build_fudo_query_args( $filters );

// タームアーカイブ(bukken / bukken_tag)の場合、現在のタームを保持
if ( is_tax( 'bukken' ) || is_tax( 'bukken_tag' ) ) {
	$current = get_queried_object();
	if ( $current && isset( $current->taxonomy, $current->slug ) ) {
		if ( empty( $query_args['tax_query'] ) ) {
			$query_args['tax_query'] = array( 'relation' => 'AND' );
		}
		$query_args['tax_query'][] = array(
			'taxonomy' => $current->taxonomy,
			'field'    => 'slug',
			'terms'    => array( $current->slug ),
		);
	}
}

$lc_query = new WP_Query( $query_args );
$total    = (int) $lc_query->found_posts;
$per_page = (int) $filters['posts_per_page'];
$cur_page = max( 1, (int) $filters['paged'] );
$range_lo = $total > 0 ? ( ( $cur_page - 1 ) * $per_page + 1 ) : 0;
$range_hi = $total > 0 ? min( $cur_page * $per_page, $total ) : 0;

// 地図ビュー用マーカーデータ（lat/lng を持つ物件のみ収集）
$lc_map_markers = array();
foreach ( $lc_query->posts as $map_post ) {
	$pid = $map_post->ID;
	$lat = (float) get_post_meta( $pid, 'bukkenido', true );
	$lng = (float) get_post_meta( $pid, 'bukkenkeido', true );
	if ( $lat && $lng ) {
		$p_price   = lc_get_fudo_meta( $pid, array( 'kakaku', 'price', 'bukken_kakaku' ) );
		$p_imgs    = lc_get_fudo_image_urls( $pid, 1 );
		$lc_map_markers[] = array(
			'lat'     => $lat,
			'lng'     => $lng,
			'title'   => $map_post->post_title,
			'url'     => get_permalink( $map_post ),
			'price'   => lc_format_price( $p_price ),
			'img'     => ! empty( $p_imgs ) ? $p_imgs[0] : '',
			'madori'  => lc_get_fudo_madori_label( $pid ),
			'menseki' => lc_get_fudo_meta( $pid, array( 'menseki', 'senyu_menseki', 'area' ) ),
			'address' => lc_get_fudo_meta( $pid, array( 'shozaichimeisho', 'shozaichi', 'address', 'jusho' ) ),
		);
	}
}

// 適用中の絞り込みチップ
$active_chips = array();
$chip_specs = array(
	'kbn'         => '種別',
	'area'        => 'エリア',
	'mado'        => '間取り',
	'price_min'   => '価格下限',
	'price_max'   => '価格上限',
	'menseki_min' => '面積下限',
	'menseki_max' => '面積上限',
	'setsubi'     => '設備',
	'tag'         => 'タグ',
	's'           => 'キーワード',
);
foreach ( $chip_specs as $k => $label ) {
	if ( empty( $_GET[ $k ] ) ) { continue; }
	$raw = wp_unslash( $_GET[ $k ] );
	if ( is_array( $raw ) ) {
		foreach ( $raw as $i => $v ) {
			if ( $v === '' ) { continue; }
			$active_chips[] = array(
				'label'      => $label . '：' . sanitize_text_field( $v ),
				'key'        => $k,
				'value'      => sanitize_text_field( $v ),
				'index'      => $i,
				'array_mode' => true,
			);
		}
	} else {
		$active_chips[] = array(
			'label'      => $label . '：' . sanitize_text_field( $raw ),
			'key'        => $k,
			'value'      => sanitize_text_field( $raw ),
			'array_mode' => false,
		);
	}
}

// 設備候補：fudou プラグインの登録済み設備マスタから上位 12 件を取得
$setsubi_options  = function_exists( 'lc_get_fudo_setsubi_counts' ) ? lc_get_fudo_setsubi_counts( 12 ) : array();
$selected_setsubi = array_filter( array_map( 'sanitize_text_field', (array) ( $_GET['setsubi'] ?? array() ) ) );
?>

<?php /* 親テーマ：一覧ページ前ウィジェットエリア */
if ( is_active_sidebar( 'archive-before-widget' ) ) : ?>
	<div class="lc-archive-before">
		<?php dynamic_sidebar( 'archive-before-widget' ); ?>
	</div>
<?php endif; ?>

<section class="lc-pagehead">
	<div class="lc-pagehead__inner">
		<?php lc_breadcrumb(); ?>
		<div class="lc-pagehead__row">
			<?php
			// 見出し計算：
			//   タクソノミーアーカイブ (bukken / bukken_tag) → small=PROPERTY、h1=ターム名
			//   ?kbn=rent / ?kbn=sale                       → small=RENT/SALE、h1=賃貸/売買物件一覧
			//   /fudo/ (kbn 無し)                           → small=PROPERTY LIST、h1=物件一覧
			$is_term_archive = ( is_tax( 'bukken' ) || is_tax( 'bukken_tag' ) );
			if ( $is_term_archive ) {
				$small_txt = 'PROPERTY';
				$h1_txt    = wp_strip_all_tags( single_term_title( '', false ) );
			} elseif ( $has_kbn_filter ) {
				$small_txt = strtoupper( $kbn );
				$h1_txt    = $kbn_lbl . '物件一覧';
			} else {
				$small_txt = 'PROPERTY LIST';
				$h1_txt    = '物件一覧';
			}
			?>
			<h1 class="lc-pagehead__ttl">
				<small><?php echo esc_html( $small_txt ); ?></small>
				<?php echo esc_html( $h1_txt ); ?>
			</h1>
			<div class="lc-pagehead__count">該当物件 <b><?php echo esc_html( number_format( $total ) ); ?></b> 件</div>
		</div>
	</div>
</section>

<?php /* 絞り込みバー — チップ無しでもソート／表示切替を常時表示（モック準拠） */ ?>
<div class="lc-refine">
	<div class="lc-refine__inner">
		<div class="lc-refine__chips">
			<?php if ( $active_chips ) : ?>
				<?php foreach ( $active_chips as $chip ) :
					if ( ! empty( $chip['array_mode'] ) ) {
						$current = isset( $_GET[ $chip['key'] ] ) ? (array) wp_unslash( $_GET[ $chip['key'] ] ) : array();
						unset( $current[ $chip['index'] ] );
						$current = array_values( array_filter( $current, fn( $v ) => $v !== '' ) );
						$remove_url = $current
							? add_query_arg( array( $chip['key'] => $current ) )
							: remove_query_arg( $chip['key'] );
					} else {
						$remove_url = remove_query_arg( $chip['key'] );
					}
					?>
					<span class="lc-refine__chip">
						<?php echo esc_html( $chip['label'] ); ?>
						<a href="<?php echo esc_url( $remove_url ); ?>" aria-label="解除">×</a>
					</span>
				<?php endforeach; ?>
				<a class="lc-refine__clear" href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>">すべてクリア</a>
			<?php endif; ?>
		</div>
		<div class="lc-refine__right">
			<div class="lc-refine__sort">
				<span>並び替え</span>
				<form method="get" action="" style="display:inline;">
					<?php foreach ( $_GET as $gk => $gv ) :
						if ( $gk === 'orderby' ) { continue; }
						if ( is_array( $gv ) ) {
							foreach ( $gv as $gvv ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $gk ); ?>[]" value="<?php echo esc_attr( $gvv ); ?>" />
							<?php endforeach;
						} else { ?>
							<input type="hidden" name="<?php echo esc_attr( $gk ); ?>" value="<?php echo esc_attr( $gv ); ?>" />
						<?php }
					endforeach; ?>
					<select name="orderby" onchange="this.form.submit()">
						<option value="date"       <?php selected( $filters['orderby'], 'date' ); ?>>新着順</option>
						<option value="price_asc"  <?php selected( $filters['orderby'], 'price_asc' ); ?>>価格が安い順</option>
						<option value="price_desc" <?php selected( $filters['orderby'], 'price_desc' ); ?>>価格が高い順</option>
					</select>
				</form>
			</div>
			<?php /* 表示切替 — タイル / リスト（地図は準備中） */ ?>
			<div class="lc-refine__view" id="lcViewSwitch" role="group" aria-label="表示切替">
				<button type="button" class="is-active" data-view="tile" aria-pressed="true">タイル</button>
				<button type="button" data-view="list" aria-pressed="false">リスト</button>
				<button type="button" data-view="map" aria-pressed="false">地図</button>
			</div>
		</div>
	</div>
</div>

<div class="lc-main lc-main--archive">

	<aside class="lc-filterpanel" aria-label="絞り込み">
		<?php
		if ( shortcode_exists( 'fudo_search' ) ) {
			echo do_shortcode( '[fudo_search]' );
		} elseif ( shortcode_exists( 'fudou_search' ) ) {
			echo do_shortcode( '[fudou_search]' );
		} else {
			// kbn 別のエリア／間取り／こだわり条件を事前計算
			$fallback_areas    = array( '中央', '末広', '大黒', '潮見', '富岡', '緑', '萩見', '朝日' );
			$all_area_options  = function_exists( 'lc_get_fudo_areas' ) ? lc_get_fudo_areas( 30, true ) : array();
			$rent_area_options = function_exists( 'lc_get_fudo_areas_by_kbn' ) ? lc_get_fudo_areas_by_kbn( 'rent', 30, true ) : $all_area_options;
			$sale_area_options = function_exists( 'lc_get_fudo_areas_by_kbn' ) ? lc_get_fudo_areas_by_kbn( 'sale', 30, true ) : $all_area_options;

			$fallback_mado     = array( '1R', '1K', '1DK', '1LDK', '2K', '2DK', '2LDK', '3DK', '3LDK', '4LDK', '4LDK+' );
			$all_mado_options  = function_exists( 'lc_get_fudo_madori_by_kbn' ) ? lc_get_fudo_madori_by_kbn( '' )     : $fallback_mado;
			$rent_mado_options = function_exists( 'lc_get_fudo_madori_by_kbn' ) ? lc_get_fudo_madori_by_kbn( 'rent' ) : $fallback_mado;
			$sale_mado_options = function_exists( 'lc_get_fudo_madori_by_kbn' ) ? lc_get_fudo_madori_by_kbn( 'sale' ) : $fallback_mado;

			$rent_setsubi_opts = function_exists( 'lc_get_fudo_setsubi_counts_by_kbn' ) ? lc_get_fudo_setsubi_counts_by_kbn( 'rent', 12 ) : $setsubi_options;
			$sale_setsubi_opts = function_exists( 'lc_get_fudo_setsubi_counts_by_kbn' ) ? lc_get_fudo_setsubi_counts_by_kbn( 'sale', 12 ) : $setsubi_options;

			$selected_area = (array) ( $_GET['area'] ?? array() );
			$selected_mado = (array) ( $_GET['mado'] ?? array() );

			// PHP 初期状態（JS DOMContentLoaded でも適用するが、フラッシュ防止のため先行設定）
			$vis = array(
				'all'  => $has_kbn_filter ? ' hidden' : '',
				'rent' => ( $has_kbn_filter && $kbn === 'rent' ) ? '' : ' hidden',
				'sale' => ( $has_kbn_filter && $kbn === 'sale' ) ? '' : ' hidden',
			);

			// エリアのバリアント定義（area / mado / setsubi 共通パターン）
			$area_variants = array(
				'all'  => $all_area_options  ?: array_fill_keys( $fallback_areas, 0 ),
				'rent' => $rent_area_options ?: ( $all_area_options ?: array_fill_keys( $fallback_areas, 0 ) ),
				'sale' => $sale_area_options ?: ( $all_area_options ?: array_fill_keys( $fallback_areas, 0 ) ),
			);
			$mado_variants = array(
				'all'  => $all_mado_options,
				'rent' => $rent_mado_options,
				'sale' => $sale_mado_options,
			);
			$setsubi_variants = array(
				'all'  => $setsubi_options,
				'rent' => $rent_setsubi_opts,
				'sale' => $sale_setsubi_opts,
			);

			// 価格ラベル初期値
			$price_label_init = $kbn === 'sale' ? '価格（万円）' : ( $kbn === 'rent' ? '賃料（月額・万円）' : '価格' );
			?>
			<div class="lc-filterpanel__hd">
				<h3>絞り込み</h3>
				<button class="lc-filterpanel__toggle" type="button" aria-expanded="false" aria-label="絞り込みを開く">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
				</button>
			</div>
			<form method="get" action="" id="lc-filter-form">
				<?php /* 種別 — 選択で各 data-kbn-sec セクションを切り替え */ ?>
				<div class="lc-filterpanel__sec">
					<h4>種別</h4>
					<div class="lc-filterpanel__opts">
						<label><input type="radio" name="kbn" value="rent" <?php checked( $kbn, 'rent' ); ?> /><span>賃貸</span></label>
						<label><input type="radio" name="kbn" value="sale" <?php checked( $kbn, 'sale' ); ?> /><span>売買</span></label>
					</div>
				</div>

				<?php /* エリア — all / rent / sale の 3 バリアント */ ?>
				<?php foreach ( $area_variants as $vk => $opts ) :
					$active = ( $vk === $kbn ) || ( $vk === 'all' && ! $has_kbn_filter ); ?>
				<div class="lc-filterpanel__sec" data-kbn-sec="<?php echo esc_attr( $vk ); ?>"<?php echo $vis[ $vk ]; ?>>
					<h4>エリア</h4>
					<div class="lc-filterpanel__opts">
						<?php foreach ( $opts as $name => $count ) : ?>
							<label>
								<input type="checkbox" name="area[]" value="<?php echo esc_attr( $name ); ?>"
									<?php if ( $active ) { checked( in_array( $name, $selected_area, true ) ); } ?> />
								<span><?php echo esc_html( $name ); ?><?php if ( $count ) : ?><small>（<?php echo esc_html( (int) $count ); ?>）</small><?php endif; ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>

				<?php /* 間取り — all / rent / sale の 3 バリアント */ ?>
				<?php foreach ( $mado_variants as $vk => $opts ) :
					$active = ( $vk === $kbn ) || ( $vk === 'all' && ! $has_kbn_filter ); ?>
				<div class="lc-filterpanel__sec" data-kbn-sec="<?php echo esc_attr( $vk ); ?>"<?php echo $vis[ $vk ]; ?>>
					<h4>間取り</h4>
					<div class="lc-filterpanel__opts">
						<?php foreach ( $opts as $m ) : ?>
							<label>
								<input type="checkbox" name="mado[]" value="<?php echo esc_attr( $m ); ?>"
									<?php if ( $active ) { checked( in_array( $m, $selected_mado, true ) ); } ?> />
								<span><?php echo esc_html( $m ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>

				<?php /* 価格 — ラベルのみ JS で切り替え */ ?>
				<div class="lc-filterpanel__sec">
					<h4 id="lc-price-label"><?php echo esc_html( $price_label_init ); ?></h4>
					<div class="lc-filterpanel__range">
						<input type="text" name="price_min" placeholder="下限" value="<?php echo esc_attr( $filters['price_min'] ); ?>" />
						<span>〜</span>
						<input type="text" name="price_max" placeholder="上限" value="<?php echo esc_attr( $filters['price_max'] ); ?>" />
					</div>
				</div>

				<div class="lc-filterpanel__sec">
					<h4>面積（m²）</h4>
					<div class="lc-filterpanel__range">
						<input type="text" name="menseki_min" placeholder="下限" value="<?php echo esc_attr( $filters['menseki_min'] ); ?>" />
						<span>〜</span>
						<input type="text" name="menseki_max" placeholder="上限" value="<?php echo esc_attr( $filters['menseki_max'] ); ?>" />
					</div>
				</div>

				<?php /* こだわり条件 — all / rent / sale の 3 バリアント */ ?>
				<?php foreach ( $setsubi_variants as $vk => $opts ) :
					if ( empty( $opts ) ) { continue; }
					$active = ( $vk === $kbn ) || ( $vk === 'all' && ! $has_kbn_filter ); ?>
				<div class="lc-filterpanel__sec" data-kbn-sec="<?php echo esc_attr( $vk ); ?>"<?php echo $vis[ $vk ]; ?>>
					<h4>こだわり条件</h4>
					<div class="lc-filterpanel__check">
						<?php foreach ( $opts as $s ) :
							$chk = $active && in_array( $s['code'], $selected_setsubi, true ); ?>
							<label>
								<input type="checkbox" name="setsubi[]" value="<?php echo esc_attr( $s['code'] ); ?>" <?php checked( $chk ); ?> />
								<?php echo esc_html( $s['name'] ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>

				<?php if ( ! empty( $filters['orderby'] ) ) : ?>
					<input type="hidden" name="orderby" value="<?php echo esc_attr( $filters['orderby'] ); ?>" />
				<?php endif; ?>
				<div class="lc-filterpanel__ft">
					<button type="submit" class="lc-filterpanel__apply">この条件で検索する</button>
				</div>
			</form>
			<?php
		}
		?>
	</aside>

	<main>
		<div class="lc-section__bar">
			<h2>物件一覧
				<small>
					<?php if ( $total > 0 ) :
						echo esc_html( sprintf( '%d-%d件 / 全%s件', $range_lo, $range_hi, number_format( $total ) ) );
					else :
						echo esc_html( '全 0 件' );
					endif; ?>
				</small>
			</h2>
		</div>

		<section class="lc-section-bukken archive-fudo lc-view--tile" id="lcBukkenSection">
			<?php if ( $lc_query->have_posts() ) : ?>
				<div id="list_simplepage">
					<?php while ( $lc_query->have_posts() ) : $lc_query->the_post();
						lc_render_fudo_card( $tag_cls, $kbn_lbl );
					endwhile; ?>
				</div>

				<nav class="lc-pagination" aria-label="ページネーション">
					<?php
					$big = 999999999;
					echo paginate_links( array(
						'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, (int) $filters['paged'] ),
						'total'     => $lc_query->max_num_pages,
						'prev_text' => '‹ 前へ',
						'next_text' => '次へ ›',
						'mid_size'  => 1,
						'add_args'  => array_filter( array(
							'kbn'         => $filters['kbn'] ?: false,
							'area'        => ! empty( $filters['area'] ) ? $filters['area'] : false,
							'mado'        => ! empty( $filters['mado'] ) ? $filters['mado'] : false,
							'price_min'   => $filters['price_min'] ?: false,
							'price_max'   => $filters['price_max'] ?: false,
							'menseki_min' => $filters['menseki_min'] ?: false,
							'menseki_max' => $filters['menseki_max'] ?: false,
							'setsubi'     => ! empty( $filters['setsubi'] ) ? $filters['setsubi'] : false,
							'tag'         => $filters['tag'] ?: false,
							'orderby'     => $filters['orderby'] !== 'date' ? $filters['orderby'] : false,
							's'           => $filters['s'] ?: false,
						) ),
					) );
					?>
				</nav>
			<?php else : ?>
				<div class="lc-card-page">
					<p>条件に合う物件がまだありません。エリアや価格帯を広げてみてください。</p>
					<p><a class="lc-refine__clear" href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>">すべての条件をクリア</a></p>
				</div>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>

			<div class="lc-map-view" id="lcMapView"
				data-markers="<?php echo esc_attr( wp_json_encode( $lc_map_markers ) ); ?>"
				aria-hidden="true">
			</div>
		</section>
	</main>

</div>

<?php /* 親テーマ：一覧ページ後ウィジェットエリア */
if ( is_active_sidebar( 'archive-after-widget' ) ) : ?>
	<div class="lc-archive-after">
		<?php dynamic_sidebar( 'archive-after-widget' ); ?>
	</div>
<?php endif; ?>

<?php get_footer();
