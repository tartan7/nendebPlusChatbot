<?php
/**
 * SYN Ownd Child – Lumin Coco
 *
 * 株式会社ルーミン・ココ向け SYN-Ownd 子テーマ。
 * - 親テーマの CSS 変数を上書きしブランドカラー (緑＋赤 CTA) を適用
 * - 不動産プラグイン (fudou) のテンプレ／DOM をそのまま使い、視覚だけ刷新
 * - Dify チャットボットをフッターに自動埋め込み
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'LC_THEME_VERSION', '1.0.0' );

// クラシックウィジェット用ラッパー（classic-widgets 有効時に Gutenberg ブロックを
// ウィジェットエリアへ配置できないため、各ブロックを WP_Widget として再公開する）
require_once get_stylesheet_directory() . '/inc/class-lc-widgets.php';

// 副読本（Recommend）— ショートコード／メタボックス／recommend.css 自動 enqueue
require_once get_stylesheet_directory() . '/inc/recommend.php';

// 旧URL（/recom11/, /recom12/, /recommend/link/）の 301 リダイレクト
require_once get_stylesheet_directory() . '/inc/legacy-redirects.php';

/* =============================================================
 * 1. テーマ初期化 — supports / メニュー / ウィジェット
 * ============================================================= */
add_action( 'after_setup_theme', function () {

	// 親テーマで未設定なら念のため追加
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 320,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );

	// ナビゲーションメニュー
	register_nav_menus( array(
		'primary' => __( 'グローバルナビ（ヘッダー）', 'syn-ownd-child' ),
		'footer'  => __( 'フッターナビ', 'syn-ownd-child' ),
	) );

	// 翻訳
	load_child_theme_textdomain( 'syn-ownd-child', get_stylesheet_directory() . '/languages' );
} );

/* =============================================================
 * 2. ウィジェットエリア — 親テーマ／fudou プラグインの既存エリアを再登録
 *
 *   重複を避けるため、以下を親テーマ／fudou から "上書き再登録"
 *   して .lc-widget / .lc-block-wrap マークアップに統一する：
 *
 *     front-before-widget  ← Hero下クイック検索（親テーマ）
 *     sidebar-widget       ← 右サイドバー（親テーマ）
 *     top_widgets (fudou)  ← ホーム本文
 *
 *   フッター3カラムは子テーマで完結させたいため、親テーマの
 *   ftrnav-widget-{left,center,right} は unregister し、
 *   子テーマ独自ID `lc-ftr-col-{1,2,3}` を新規登録する。
 *
 *   その他の親テーマ既存エリア（front-after / archive-before /
 *   archive-after / page-before / page-after / single-before /
 *   single-after / sidebar-fixed / ftr-head / syousai_widgets）は
 *   そのまま使用可能。
 * ============================================================= */
add_action( 'widgets_init', function () {

	// fudou プラグインは widgets_init 優先度10で top_widgets を登録するので
	// その後（11）で上書きする。
	// ウィジェットエリアごとに before_widget マークアップを分ける：
	//   - サイドバー：.lc-widget でカード装飾（白背景＋枠＋影）
	//   - 本文（top_widgets）／Hero下：.lc-block-wrap で透過（中の各ブロックが
	//     自分のカード装飾を持つため、二重カードを防ぐ）
	//   - フッター3カラム：.lc-ftr-widget でフッター固有スタイル
	$boxed_markup = array(
		'before_widget' => '<div class="lc-widget %2$s" id="%1$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="lc-widget__ttl">',
		'after_title'   => '</h3>',
	);
	$transparent_markup = array(
		'before_widget' => '<div class="lc-block-wrap %2$s" id="%1$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="lc-block-wrap__ttl">',
		'after_title'   => '</h3>',
	);
	$footer_markup = array(
		'before_widget' => '<div class="lc-ftr-widget %2$s" id="%1$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="lc-ftr-widget__ttl">',
		'after_title'   => '</h4>',
	);

	$overrides = array(
		'front-before-widget' => array(
			'name'   => 'トップ Hero下 (クイック検索)',
			'desc'   => 'Hero と本文の間に表示。「LC 物件検索フォーム」をここに配置するとフローティングカードとして Hero に重なる。',
			'markup' => $transparent_markup,
		),
		'sidebar-widget' => array(
			'name'   => 'サイドバー (物件検索)',
			'desc'   => '物件一覧・詳細ページ右側に表示。LC キーワード検索／LC エリアから探す／LC タグクラウド 等を配置。',
			'markup' => $boxed_markup,
		),
		'top_widgets' => array(
			'name'   => 'トップページ (ホーム本文)',
			'desc'   => 'ホーム本文（サイドバー左の主領域）。LC 最新情報 / LC 賃貸物件 / LC 売買物件 を上から順に配置。',
			'markup' => $transparent_markup,
		),
	);

	foreach ( $overrides as $id => $p ) {
		unregister_sidebar( $id );
		register_sidebar( array_merge(
			array(
				'id'          => $id,
				'name'        => $p['name'],
				'description' => $p['desc'],
			),
			$p['markup']
		) );
	}

	// 親テーマのフッターナビ3エリアを解除（管理画面で重複表示を防ぐ）
	unregister_sidebar( 'ftrnav-widget-left' );
	unregister_sidebar( 'ftrnav-widget-center' );
	unregister_sidebar( 'ftrnav-widget-right' );

	// 子テーマ独自のフッター3カラム
	$lc_footer_cols = array(
		'lc-ftr-col-1' => array(
			'name' => 'LCフッター列1 (サービス)',
			'desc' => 'フッター3カラムの左列。物件検索・サービス系リンク。',
		),
		'lc-ftr-col-2' => array(
			'name' => 'LCフッター列2 (会社情報)',
			'desc' => 'フッター3カラムの中央列。会社情報・規約・お問い合わせ。',
		),
		'lc-ftr-col-3' => array(
			'name' => 'LCフッター列3 (エリア)',
			'desc' => 'フッター3カラムの右列。稚内市エリアリンク等。',
		),
	);
	foreach ( $lc_footer_cols as $lc_id => $p ) {
		register_sidebar( array_merge(
			array(
				'id'          => $lc_id,
				'name'        => $p['name'],
				'description' => $p['desc'],
			),
			$footer_markup
		) );
	}

	// 物件詳細ページ下部「関連物件」エリア（新規）
	// 不動産プラグインの「おすすめ物件」「新着物件」などの専用ウィジェットを配置する想定
	register_sidebar( array_merge(
		array(
			'id'          => 'lc-single-fudo-related',
			'name'        => '物件詳細：関連物件',
			'description' => '物件詳細ページ下部に表示。不動産プラグインの「おすすめ物件」「新着物件」等の専用ウィジェットを配置。',
		),
		$transparent_markup
	) );
}, 11 );

/* =============================================================
 * 3. CSS / JS 読み込み
 *    親テーマ → 子 style.css → 子 theme.css (レイアウト) → chat-fab.js
 * ============================================================= */
add_action( 'wp_enqueue_scripts', function () {
	$ver = LC_THEME_VERSION;

	// 子テーマ自身のファイルは filemtime() を ver にしてキャッシュバスティング
	$stylesheet_path = get_stylesheet_directory();
	$style_css_ver   = file_exists( $stylesheet_path . '/style.css' )           ? (string) filemtime( $stylesheet_path . '/style.css' )           : $ver;
	$theme_css_ver   = file_exists( $stylesheet_path . '/assets/css/theme.css' ) ? (string) filemtime( $stylesheet_path . '/assets/css/theme.css' ) : $ver;

	// Google Fonts (Noto Sans JP / Noto Serif JP)
	wp_enqueue_style(
		'lc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700&family=Noto+Serif+JP:wght@500;600;700&display=swap',
		array(),
		null
	);

	// 親テーマ CSS
	$parent_handle = 'syn-ownd-parent';
	$parent_css    = get_template_directory() . '/assets/css/index.css';
	if ( file_exists( $parent_css ) ) {
		wp_enqueue_style(
			$parent_handle,
			get_template_directory_uri() . '/assets/css/index.css',
			array(),
			wp_get_theme( get_template() )->get( 'Version' )
		);
	}

	// 子テーマの変数上書き
	wp_enqueue_style(
		'lc-child-tokens',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_handle ),
		$style_css_ver
	);

	// 子テーマのレイアウト/コンポーネント
	wp_enqueue_style(
		'lc-child-theme',
		get_stylesheet_directory_uri() . '/assets/css/theme.css',
		array( 'lc-child-tokens' ),
		$theme_css_ver
	);

	// Chat FAB
	wp_enqueue_script(
		'lc-chat-fab',
		get_stylesheet_directory_uri() . '/assets/js/chat-fab.js',
		array(),
		$ver,
		true
	);
	wp_localize_script( 'lc-chat-fab', 'lcChat', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'lc_dify_chat' ),
		'fallback' => get_theme_mod( 'lc_chat_fallback_url', home_url( '/contact/' ) ),
	) );

	// ハンバーガーナビゲーション
	wp_enqueue_script(
		'lc-nav',
		get_stylesheet_directory_uri() . '/assets/js/nav.js',
		array(),
		(string) filemtime( get_stylesheet_directory() . '/assets/js/nav.js' ),
		true
	);

	// 物件一覧の地図ビュー（Leaflet + OpenStreetMap）
	if ( is_post_type_archive( 'fudo' ) || is_tax( array( 'bukken', 'bukken_tag' ) ) ) {
		wp_enqueue_style( 'leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true );
		$map_js = get_stylesheet_directory() . '/assets/js/fudo-map.js';
		wp_enqueue_script(
			'lc-fudo-map',
			get_stylesheet_directory_uri() . '/assets/js/fudo-map.js',
			array( 'leaflet' ),
			file_exists( $map_js ) ? (string) filemtime( $map_js ) : '1',
			true
		);
		$filter_js = get_stylesheet_directory() . '/assets/js/fudo-filter.js';
		wp_enqueue_script(
			'lc-fudo-filter',
			get_stylesheet_directory_uri() . '/assets/js/fudo-filter.js',
			array(),
			file_exists( $filter_js ) ? (string) filemtime( $filter_js ) : '1',
			true
		);
	}
}, 20 );

/* 副読本ページでは親テーマJS（synx-theme-script）を除外する。
 * 親テーマJSは物件DOM（スライダー・タブ等）を前提としており、
 * 副読本ページに存在しない要素へのquerySelectorAll でエラーが出るため。 */
add_action( 'wp_enqueue_scripts', function () {
	$tpl = get_page_template_slug();
	if ( strpos( (string) $tpl, 'recommend' ) !== false ) {
		wp_dequeue_script( 'synx-theme-script' );
		wp_dequeue_script( 'synx-swiper-script' );
	}
}, 25 );

/* =============================================================
 * 4. ブランド既定値 — 親テーマ／カスタマイザーの初期値
 *
 * 親テーマ syn-ownd は add_setting で各カラーに紺系の既定値を持つ：
 *   synx_design_main_color           → #10318A (紺)
 *   synx_design_link_color           → #0069ad (青)
 *   synx_design_text_color           → #000000 (黒)
 * これらは editor-css-vars.php で <style id="custom-css-04"> として
 * wp_head にインライン注入され、子テーマの :root を後勝ち上書きする。
 *
 * 単純な `$v ?: '...'` フィルターでは「親テーマ既定の青」が真値として
 * 通り抜けてしまうので、親テーマの既定カラーセットを検知して
 * Lumin Coco ブランド緑に差し替える方式に変更。
 * （ユーザーがカスタマイザーで明示的に別色を選択している場合は尊重） */
$lc_brand_override = function ( $parent_defaults, $brand ) {
	return function ( $v ) use ( $parent_defaults, $brand ) {
		// カスタマイザーは未保存セッティングの判別用に stdClass センチネルを
		// 既定値として `get_theme_mod()` に渡してくる。文字列・null・空以外
		// （オブジェクトや配列）はそのまま素通りさせる。
		if ( ! is_scalar( $v ) ) { return $v; }
		if ( $v === '' || $v === null || $v === false ) { return $brand; }
		$norm = strtolower( (string) $v );
		foreach ( $parent_defaults as $blue ) {
			if ( strtolower( $blue ) === $norm ) { return $brand; }
		}
		return $v;
	};
};
add_filter( 'theme_mod_synx_design_main_color',           $lc_brand_override( array( '#10318A', '#004a93' ), '#0F825D' ) );
add_filter( 'theme_mod_synx_design_main_gradation_color', $lc_brand_override( array(),                      '#1F9A6F' ) );
add_filter( 'theme_mod_synx_design_link_color',           $lc_brand_override( array( '#0069ad' ),            '#1F9A6F' ) );
add_filter( 'theme_mod_synx_design_text_color',           $lc_brand_override( array( '#000000', '#000' ),    '#08593F' ) );
add_filter( 'theme_mod_synx_design_fontfamily',           fn( $v ) => $v ?: 'ns_sans' );
add_filter( 'theme_mod_synx_design_bg_gradation',         fn( $v ) => ( $v === false || $v === '' ) ? '0' : $v );

// 親テーマの edark/elight 系（カスタマイザー既定が赤/ピンク）は --c-green-* に
// 逃がす方針なので変数はそのまま親テーマ既定でOK。ただし、もし将来
// var(--color-edark01) を直接使うコードを書きたい場合に備え、緑への
// 差し替えも一応用意しておく（明示的に有効化したいときコメントアウトを外す）：
// add_filter( 'theme_mod_synx_editor_color_dark01', $lc_brand_override( array( '#D8462F' ), '#08593F' ) );
// add_filter( 'theme_mod_synx_editor_color_light01', $lc_brand_override( array( '#FFF0F5' ), '#E3F3EC' ) );

/* =============================================================
 * 5. メニュー：URL に "contact" を含む項目に CTA クラスを自動付与
 * ============================================================= */
add_filter( 'nav_menu_css_class', function ( $classes, $item ) {
	if ( ! empty( $item->url ) && strpos( $item->url, 'contact' ) !== false ) {
		$classes[] = 'cta';
	}
	return $classes;
}, 10, 2 );

/* =============================================================
 * 6. ロゴが未設定なら、子テーマのワードマーク PNG をフォールバック表示
 * ============================================================= */
add_filter( 'get_custom_logo', function ( $html ) {
	if ( has_custom_logo() ) { return $html; }

	$src = get_stylesheet_directory_uri() . '/assets/images/logo/lumin-coco-wordmark-trans.png';
	$alt = esc_attr( get_bloginfo( 'name', 'display' ) );

	return sprintf(
		'<a href="%1$s" class="custom-logo-link" rel="home"><img class="custom-logo lc-hdr__logo-img" src="%2$s" alt="%3$s" /></a>',
		esc_url( home_url( '/' ) ),
		esc_url( $src ),
		$alt
	);
} );

/* =============================================================
 * 7. オリジナルチャットモーダル — Dify Chat API プロキシ
 *    wp-config.php で LC_DIFY_API_KEY（管理画面 > API アクセスのキー）と
 *    LC_DIFY_BASE_URL を定義してください。
 *    例: define( 'LC_DIFY_API_KEY',  'app-xxxxxxxxxxxxxxxx' );
 *        define( 'LC_DIFY_BASE_URL', 'https://es-cube2.net' );
 * ============================================================= */

add_action( 'wp_ajax_lc_dify_chat',        'lc_handle_dify_chat' );
add_action( 'wp_ajax_nopriv_lc_dify_chat', 'lc_handle_dify_chat' );

function lc_handle_dify_chat() {
	check_ajax_referer( 'lc_dify_chat', 'nonce' );

	$query           = sanitize_text_field( wp_unslash( $_POST['query'] ?? '' ) );
	$conversation_id = sanitize_text_field( wp_unslash( $_POST['conversation_id'] ?? '' ) );

	if ( empty( $query ) ) {
		wp_send_json_error( __( 'メッセージを入力してください', 'syn-ownd-child' ), 400 );
	}
	if ( empty( LC_DIFY_API_KEY ) ) {
		wp_send_json_error( __( 'チャット機能が現在利用できません', 'syn-ownd-child' ), 503 );
	}

	$payload = array(
		'inputs'        => (object) [],
		'query'         => $query,
		'response_mode' => 'blocking',
		'user'          => 'visitor-' . substr( md5( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ), 0, 8 ),
	);
	if ( $conversation_id ) {
		$payload['conversation_id'] = $conversation_id;
	}

	$request_chat = static function ( $request_payload ) {
		return wp_remote_post(
			trailingslashit( LC_DIFY_BASE_URL ) . 'v1/chat-messages',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . LC_DIFY_API_KEY,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $request_payload ),
				'timeout' => 30,
			)
		);
	};

	$response = $request_chat( $payload );

	if ( is_wp_error( $response ) ) {
		error_log( '[LC Dify Chat] WP_Error: ' . $response->get_error_message() );
		wp_send_json_error( __( '通信エラーが発生しました', 'syn-ownd-child' ) );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$raw  = wp_remote_retrieve_body( $response );
	$body = json_decode( $raw, true );
	$dify_message = '';
	if ( is_array( $body ) ) {
		if ( ! empty( $body['message'] ) ) {
			$dify_message = (string) $body['message'];
		} elseif ( ! empty( $body['error'] ) ) {
			$dify_message = is_scalar( $body['error'] ) ? (string) $body['error'] : wp_json_encode( $body['error'] );
		}
	}

	// Agent Chat App は blocking 非対応のため、該当エラー時のみ streaming へフォールバック。
	if ( $code !== 200 && stripos( $dify_message, 'does not support blocking mode' ) !== false ) {
		$payload['response_mode'] = 'streaming';
		$response                 = $request_chat( $payload );
		if ( is_wp_error( $response ) ) {
			error_log( '[LC Dify Chat] streaming WP_Error: ' . $response->get_error_message() );
			wp_send_json_error( __( '通信エラーが発生しました', 'syn-ownd-child' ) );
		}
		$code = wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );
		$body = json_decode( $raw, true );

		// streaming は SSE 形式なので、"data: {json}" 各行をパースして answer を結合する。
		if ( $code === 200 && ! is_array( $body ) ) {
			$answer_from_stream = '';
			$conv_from_stream   = '';
			$lines              = preg_split( "/\r\n|\n|\r/", (string) $raw );
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( strpos( $line, 'data:' ) !== 0 ) {
					continue;
				}
				$json = trim( substr( $line, 5 ) );
				if ( $json === '' || $json === '[DONE]' ) {
					continue;
				}
				$event = json_decode( $json, true );
				if ( ! is_array( $event ) ) {
					continue;
				}
				if ( ! empty( $event['answer'] ) && is_string( $event['answer'] ) ) {
					$answer_from_stream .= $event['answer'];
				}
				if ( ! empty( $event['conversation_id'] ) && is_string( $event['conversation_id'] ) ) {
					$conv_from_stream = $event['conversation_id'];
				}
			}
			if ( $answer_from_stream !== '' ) {
				wp_send_json_success( array(
					'answer'          => $answer_from_stream,
					'conversation_id' => $conv_from_stream,
				) );
			}
		}
	}

	if ( $code !== 200 || empty( $body['answer'] ) ) {
		$dify_message = '';
		if ( is_array( $body ) ) {
			if ( ! empty( $body['message'] ) ) {
				$dify_message = (string) $body['message'];
			} elseif ( ! empty( $body['error'] ) ) {
				$dify_message = is_scalar( $body['error'] ) ? (string) $body['error'] : wp_json_encode( $body['error'] );
			}
		}
		error_log(
			sprintf(
				'[LC Dify Chat] API error: status=%1$d, message=%2$s, raw=%3$s',
				(int) $code,
				$dify_message ?: '(empty)',
				is_string( $raw ) ? $raw : '(non-string body)'
			)
		);
		$public_error = __( '回答の取得に失敗しました', 'syn-ownd-child' );
		if ( $dify_message ) {
			$public_error .= ' (' . sanitize_text_field( $dify_message ) . ')';
		} else {
			$public_error .= ' (HTTP ' . (int) $code . ')';
		}
		wp_send_json_error( $public_error );
	}

	wp_send_json_success( array(
		'answer'          => $body['answer'],
		'conversation_id' => $body['conversation_id'] ?? '',
	) );
}

/* =============================================================
 * 8. ヘルパー：パンくず HTML を出力（プラグイン CustomBreadcrumb があれば優先）
 * ============================================================= */
function lc_breadcrumb( $extra_class = '' ) {
	if ( is_callable( array( '\SYNX\Utils\CustomBreadcrumb', 'display' ) ) ) {
		\SYNX\Utils\CustomBreadcrumb::display( $extra_class );
		return;
	}
	// フォールバック
	echo '<nav class="lc-breadcrumb ' . esc_attr( $extra_class ) . '" aria-label="パンくずリスト">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">ホーム</a>';
	if ( is_singular() ) {
		echo '<span class="lc-breadcrumb__sep">›</span>';
		echo '<span class="lc-breadcrumb__current">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_archive() ) {
		echo '<span class="lc-breadcrumb__sep">›</span>';
		echo '<span class="lc-breadcrumb__current">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	} elseif ( is_search() ) {
		echo '<span class="lc-breadcrumb__sep">›</span>';
		echo '<span class="lc-breadcrumb__current">検索結果</span>';
	} elseif ( is_404() ) {
		echo '<span class="lc-breadcrumb__sep">›</span>';
		echo '<span class="lc-breadcrumb__current">ページが見つかりません</span>';
	}
	echo '</nav>';
}

/* =============================================================
 * 9. 不動産プラグイン互換：post type "fudo" の判定ヘルパー
 *    プラグインのテンプレ階層は archive-fudo.php / single-fudo.php を
 *    優先するため、別途登録不要。万一 plugin 側で違う名前で出る場合の
 *    フォールバック判定。
 * ============================================================= */
function lc_is_fudo_archive() {
	// fudou プラグインは 'bukken'（物件カテゴリ・階層）と 'bukken_tag'（物件投稿タグ）のみ登録。
	// fudo_category / fudo_tag / fudo_area は本プラグインには存在しない。
	return ( function_exists( 'is_post_type_archive' ) && is_post_type_archive( 'fudo' ) )
		|| ( function_exists( 'is_tax' ) && ( is_tax( 'bukken' ) || is_tax( 'bukken_tag' ) ) );
}
function lc_is_fudo_single() {
	return is_singular( 'fudo' );
}

/* -------------------------------------------------------------
 * 不動産プラグイン (fudou) のテンプレ強制上書きを子テーマ側で再上書き。
 *
 * 問題:
 *   plugin/fudou/fudou.php の get_post_type_single_template_fudou() /
 *   get_post_type_archive_template_fudou() が `template_include` フィルター
 *   （優先度 10 / 11）で `locate_template(['../../plugins/fudou/themes/single-fudo.php', 'single-fudo.php'])`
 *   を返す。第1引数のプラグイン同梱テンプレが常に存在するため、子テーマの
 *   single-fudo.php / archive-fudo.php が WordPress 通常階層で拾われない。
 *
 * 解決:
 *   優先度 99 で同フィルターを後付けし、子テーマに同名ファイルがあれば
 *   そちらを返す。無ければプラグインの戻り値をそのまま通す（後方互換）。
 * ------------------------------------------------------------- */
add_filter( 'template_include', function ( $template ) {
	if ( lc_is_fudo_single() ) {
		$child = get_stylesheet_directory() . '/single-fudo.php';
		if ( file_exists( $child ) ) { return $child; }
	}
	if ( lc_is_fudo_archive() ) {
		$child = get_stylesheet_directory() . '/archive-fudo.php';
		if ( file_exists( $child ) ) { return $child; }
	}
	return $template;
}, 99 );

/* =============================================================
 * 10. 共通: 物件カード描画 (fudou DOM 互換)
 *     fudou プラグインの hentry / list_simple_box / list_picsam_img /
 *     bukken-meta / dpoint1 / dpoint2 / bukken-addr / list_details_button
 *     構造をそのまま出力し、theme.css の #list_simplepage グリッドに乗せる。
 * ============================================================= */
function lc_get_fudo_meta( $post_id, $keys, $default = '' ) {
	foreach ( (array) $keys as $k ) {
		$v = get_post_meta( $post_id, $k, true );
		if ( $v !== '' && $v !== null && $v !== false ) { return $v; }
	}
	return $default;
}

/**
 * fudo 物件の間取りラベルを返す（例: "2LDK"）。
 * fudou プラグインは madorisu（部屋数）と madorisyurui（種別コード）を別々に保存する。
 */
function lc_get_fudo_madori_label( $post_id ) {
	static $syurui_map = array(
		'10' => 'R', '20' => 'K', '25' => 'SK', '30' => 'DK',
		'35' => 'SDK', '40' => 'LK', '45' => 'SLK', '50' => 'LDK', '55' => 'SLDK',
	);
	$su = trim( (string) get_post_meta( $post_id, 'madorisu',     true ) );
	$sy = trim( (string) get_post_meta( $post_id, 'madorisyurui', true ) );
	if ( $su === '' && $sy === '' ) { return ''; }
	$name = isset( $syurui_map[ $sy ] ) ? $syurui_map[ $sy ] : '';
	return $su . $name;
}

function lc_render_fudo_card( $tag_class = 'tag-rent', $tag_label = '賃貸' ) {
	$post_id    = get_the_ID();
	// 引数が空なら bukkenshubetsu メタから投稿ごとに自動判定（/fudo/ 全件モード等）
	if ( $tag_class === '' || $tag_label === '' ) {
		$is_sale   = function_exists( 'lc_is_fudo_sale' ) ? lc_is_fudo_sale( $post_id ) : false;
		$tag_class = $is_sale ? 'tag-sale' : 'tag-rent';
		$tag_label = $is_sale ? '売買'     : '賃貸';
	}
	$image_urls = lc_get_fudo_image_urls( $post_id, 2 );

	$price   = lc_get_fudo_meta( $post_id, array( 'kakaku', 'price', 'bukken_kakaku' ) );
	$madori  = lc_get_fudo_madori_label( $post_id );
	$menseki = lc_get_fudo_meta( $post_id, array( 'menseki', 'area', 'senyu_menseki' ) );
	$address = lc_get_fudo_meta( $post_id, array( 'shozaichimeisho', 'shozaichi', 'address', 'jusho' ) );
	$chiku   = lc_get_fudo_meta( $post_id, array( 'chikunen', 'chiku', 'built_year', 'kenchiku_nen' ) );
	$kouzou  = lc_get_fudo_meta( $post_id, array( 'kouzou', 'structure' ) );
	$houi    = lc_get_fudo_meta( $post_id, array( 'houi', 'direction' ) );
	$is_new  = ( time() - get_post_time( 'U' ) ) < ( 30 * DAY_IN_SECONDS );
	// 価格更新（UP）バッジ — fudou プラグイン側の慣習に合わせ post_meta 'kakaku_up' / 'up' を見る
	$is_up   = (bool) lc_get_fudo_meta( $post_id, array( 'kakaku_up', 'up', 'lc_up' ) );
	?>
	<article class="hentry">
		<div class="list_simple_boxtitle">
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>"><span class="tag-bukken <?php echo esc_attr( $tag_class ); ?>"><?php echo esc_html( $tag_label ); ?></span><?php the_title(); ?></a>
			</h2>
		</div>
		<div class="list_simple_box">
			<div class="list_picsam_img">
				<?php if ( $is_up ) : ?><span class="up_mark">UP</span>
				<?php elseif ( $is_new ) : ?><span class="new_mark">NEW</span><?php endif; ?>
				<div class="list_picsam_img-grid lc-pics-<?php echo (int) count( $image_urls ); ?>">
					<?php foreach ( $image_urls as $img_url ) : ?>
						<img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( $price || $madori || $menseki ) : ?>
				<div class="bukken-meta">
					<?php if ( $price ) : ?>
						<span class="dpoint1"><?php echo esc_html( lc_format_price( $price ) ); ?></span>
					<?php endif; ?>
					<?php if ( $madori || $menseki ) : ?>
						<span class="dpoint2"><?php echo esc_html( trim( ( $madori ? $madori : '' ) . ' ／ ' . ( $menseki ? $menseki : '' ), ' ／' ) ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $address ) : ?>
				<div class="bukken-addr"><?php echo esc_html( $address ); ?></div>
			<?php endif; ?>
			<?php if ( $chiku || $kouzou || $houi ) : ?>
				<div class="bukken-spec">
					<?php if ( $chiku )  : ?><span>築 <b><?php echo esc_html( $chiku ); ?></b></span><?php endif; ?>
					<?php if ( $kouzou ) : ?><span><?php echo esc_html( $kouzou ); ?></span><?php endif; ?>
					<?php if ( $houi )   : ?><span><?php echo esc_html( $houi ); ?></span><?php endif; ?>
				</div>
			<?php endif; ?>
			<?php
			$content_text = wp_strip_all_tags( get_the_content() );
			$content_text = preg_replace( '/\s+/u', ' ', trim( $content_text ) );
			if ( $content_text ) : ?>
				<div class="lc-card-point">
					<span class="lc-card-point__label">この物件のポイント</span>
					<p class="lc-card-point__text"><?php echo esc_html( mb_strimwidth( $content_text, 0, 120, '…' ) ); ?></p>
				</div>
			<?php endif; ?>
			<a href="<?php the_permalink(); ?>"><div class="list_details_button">→ 物件詳細を見る</div></a>
		</div>
	</article>
	<?php
}

/* =============================================================
 * 11. 物件検索ヘルパー — クエリビルダー／価格パーサ／タームキャッシュ
 * ============================================================= */

/**
 * 価格文字列を整数(円単位)に統一する。
 * 例：
 *   "300万円" → 3000000
 *   "3万"     → 30000
 *   "3"       → 3 ( 数値のみ。文脈に応じて呼び出し側で万円換算 )
 *   "1,200"   → 1200
 *
 * @param string|int $str 価格を表す文字列。
 * @return int 円単位の整数。パース不能なら 0。
 */
function lc_parse_price( $str ) {
	if ( is_numeric( $str ) ) {
		return (int) $str;
	}
	if ( ! is_string( $str ) ) { return 0; }

	// 全角→半角
	$s = mb_convert_kana( $str, 'n', 'UTF-8' );
	$s = str_replace( array( ',', ' ', '　' ), '', $s );

	// 「万円」「万」が含まれていれば10000倍
	$has_man = ( strpos( $s, '万' ) !== false );
	$num = preg_replace( '/[^0-9.]/', '', $s );
	if ( $num === '' ) { return 0; }

	$val = (float) $num;
	if ( $has_man ) { $val *= 10000; }
	return (int) $val;
}

/**
 * 日本の住所文字列を「市区町村」と「字・丁目より前の地区名」に分割する。
 *
 *   "稚内市末広2丁目"     → array( 'city' => '稚内市', 'area' => '末広' )
 *   "猿払村浜鬼志別"      → array( 'city' => '猿払村', 'area' => '浜鬼志別' )
 *   "稚内市"              → array( 'city' => '稚内市', 'area' => '' )
 *   "札幌市北区北7条西1丁目" → array( 'city' => '札幌市', 'area' => '北区北7条西' )
 *
 * @param string $addr 住所文字列（fudou プラグインの shozaichimeisho 想定）
 * @return array{city:string, area:string}
 */
function lc_parse_address_jp( $addr ) {
	$addr = trim( (string) $addr );
	if ( $addr === '' ) { return array( 'city' => '', 'area' => '' ); }

	// Step 1: 「○○市/区/町/村」までを city、それ以降を area として分割。
	//         市区町村が無い文字列（例: "大黒1丁目1-1"）は city=空, area=全体 になる。
	$city = '';
	$area = $addr;
	if ( preg_match( '/^(.*?[市区町村])(.*)$/u', $addr, $m ) ) {
		$city = trim( $m[1] );
		$area = trim( $m[2] );
	}

	// Step 2: area から「N丁目」以降と末尾の「N-N」ブロック番号を除去して町名だけ残す。
	//         例:
	//           "大黒1丁目1-1" → "大黒"
	//           "末広5丁目7-5" → "末広"
	//           "中央2-3"      → "中央"
	//           "ノシャップ"   → "ノシャップ"（変化なし）
	$stripped = preg_replace( '/[0-9０-９一二三四五六七八九十]+丁目.*$/u', '', $area );
	$stripped = preg_replace( '/[0-9０-９]+[\-－―ー][0-9０-９\-－―ー]*\s*$/u', '', (string) $stripped );
	$stripped = preg_replace( '/[0-9０-９]+番地?.*$/u', '', (string) $stripped );
	$stripped = trim( (string) $stripped );
	if ( $stripped !== '' ) { $area = $stripped; }

	return array( 'city' => $city, 'area' => $area );
}

/**
 * fudo 物件のエリア一覧を shozaichimeisho メタから集計して返す。
 * fudou プラグインに専用エリアタクソノミーが無いため、メタ値の集計でエリアリストを生成する。
 *
 * @param int  $limit     最大返却件数
 * @param bool $normalize true なら "稚内市" 等の市区プレフィックスと "N丁目..." 以降を取り除き、
 *                        共通の地区名（例：「末広」「潮見」）に正規化してグループ化する。
 * @return array<string,int> 連想配列：エリア名 => 件数
 */
function lc_get_fudo_areas( $limit = 20, $normalize = true ) {
	global $wpdb;

	$cache_key = 'lc_areas_' . (int) $limit . ( $normalize ? '_n' : '_raw' );
	$cached    = wp_cache_get( $cache_key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value AS addr, COUNT(*) AS cnt
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = %s
		   AND pm.meta_value <> ''
		   AND p.post_type = %s
		   AND p.post_status = %s
		 GROUP BY pm.meta_value
		 ORDER BY cnt DESC
		 LIMIT %d",
		'shozaichimeisho', 'fudo', 'publish', max( 1, (int) $limit ) * 4
	) );

	$areas = array();
	foreach ( (array) $rows as $r ) {
		$name = (string) $r->addr;
		if ( $normalize ) {
			// 「○○県」「○○市」「○○区」「○○町」「○○村」までを除去
			$tmp = preg_replace( '/^.*?[都道府県].*?[市区町村]/u', '', $name );
			if ( $tmp === '' || $tmp === null ) {
				$tmp = preg_replace( '/^.*?[市区町村]/u', '', $name );
			}
			// 「N丁目」以降を除去（半角／全角／漢数字）
			$tmp = preg_replace( '/[0-9０-９一二三四五六七八九十]+丁目.*$/u', '', (string) $tmp );
			$tmp = trim( (string) $tmp );
			if ( $tmp !== '' ) { $name = $tmp; }
		}
		if ( ! isset( $areas[ $name ] ) ) { $areas[ $name ] = 0; }
		$areas[ $name ] += (int) $r->cnt;
	}
	arsort( $areas );
	$areas = array_slice( $areas, 0, (int) $limit, true );

	wp_cache_set( $cache_key, $areas, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $areas;
}

/**
 * fudo 物件のエリア一覧を「市区町村」単位でグループ化して返す。
 *   ['稚内市' => ['末広' => 5, '潮見' => 3, ...],
 *    '猿払村' => ['浜鬼志別' => 1, ...]]
 *
 * @param int $limit_per_city 1 市区町村あたりの最大ターム数
 * @return array<string, array<string,int>>
 */
function lc_get_fudo_areas_grouped( $limit_per_city = 30 ) {
	global $wpdb;

	$cache_key = 'lc_areas_grouped_' . (int) $limit_per_city;
	$cached    = wp_cache_get( $cache_key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value AS addr, COUNT(*) AS cnt
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = %s
		   AND pm.meta_value <> ''
		   AND p.post_type = %s
		   AND p.post_status = %s
		 GROUP BY pm.meta_value
		 ORDER BY cnt DESC, pm.meta_value ASC",
		'shozaichimeisho', 'fudo', 'publish'
	) );

	$grouped = array();
	foreach ( (array) $rows as $r ) {
		$parsed = lc_parse_address_jp( (string) $r->addr );
		$city = $parsed['city'];
		$area = $parsed['area'];
		if ( $city === '' ) { $city = '（その他）'; }
		if ( $area === '' ) { continue; }
		if ( ! isset( $grouped[ $city ] ) ) { $grouped[ $city ] = array(); }
		if ( ! isset( $grouped[ $city ][ $area ] ) ) { $grouped[ $city ][ $area ] = 0; }
		$grouped[ $city ][ $area ] += (int) $r->cnt;
	}

	foreach ( $grouped as &$areas_in_city ) {
		arsort( $areas_in_city );
		$areas_in_city = array_slice( $areas_in_city, 0, max( 1, (int) $limit_per_city ), true );
	}
	unset( $areas_in_city );

	wp_cache_set( $cache_key, $grouped, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $grouped;
}

/**
 * fudo 物件の「設備・条件」(setsubi メタ) を集計し、コードと件数を返す。
 * fudou プラグインは setsubi メタにスラッシュ区切りの数字コードを保存し、
 * 各コードの名前は data/work-fudo.php の $work_setsubi グローバル辞書で参照する。
 *
 * @param int $limit 最大返却件数
 * @return array<int, array{code:string, name:string, count:int}>
 */
function lc_get_fudo_setsubi_counts( $limit = 50 ) {
	global $wpdb;

	$cache_key = 'lc_setsubi_counts_' . (int) $limit;
	$cached    = wp_cache_get( $cache_key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value AS codes
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = %s
		   AND pm.meta_value <> ''
		   AND p.post_type = %s
		   AND p.post_status = %s",
		'setsubi', 'fudo', 'publish'
	) );

	$counts = array();
	foreach ( (array) $rows as $r ) {
		$codes = preg_split( '#/+#', (string) $r->codes, -1, PREG_SPLIT_NO_EMPTY );
		foreach ( (array) $codes as $code ) {
			$code = trim( (string) $code );
			if ( $code === '' || ! ctype_digit( $code ) ) { continue; }
			if ( ! isset( $counts[ $code ] ) ) { $counts[ $code ] = 0; }
			$counts[ $code ]++;
		}
	}

	if ( ! $counts ) {
		wp_cache_set( $cache_key, array(), 'lc_fudo', 5 * MINUTE_IN_SECONDS );
		return array();
	}

	// fudou プラグインの設備マスタを取得（未ロード時は初期化関数を呼ぶ）
	global $work_setsubi;
	if ( empty( $work_setsubi ) && function_exists( 'work_setsubi_init_fudou' ) ) {
		work_setsubi_init_fudou();
	}
	if ( ! is_array( $work_setsubi ) ) { $work_setsubi = array(); }

	$result = array();
	foreach ( $counts as $code => $count ) {
		if ( ! isset( $work_setsubi[ $code ]['name'] ) ) { continue; }
		$result[] = array(
			'code'  => (string) $code,
			'name'  => (string) $work_setsubi[ $code ]['name'],
			'count' => (int) $count,
		);
	}

	usort( $result, function ( $a, $b ) { return $b['count'] - $a['count']; } );
	$result = array_slice( $result, 0, max( 1, (int) $limit ) );

	wp_cache_set( $cache_key, $result, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $result;
}

/**
 * fudo 物件のエリア一覧を kbn 別にフィルタして返す。
 *
 * @param string $kbn       'rent' | 'sale' | '' (全件 → lc_get_fudo_areas に委譲)
 * @param int    $limit     最大返却件数
 * @param bool   $normalize true なら市区・丁目を正規化
 * @return array<string,int>
 */
function lc_get_fudo_areas_by_kbn( $kbn = '', $limit = 30, $normalize = true ) {
	$kbn = sanitize_text_field( $kbn );
	if ( $kbn === '' ) {
		return lc_get_fudo_areas( $limit, $normalize );
	}
	global $wpdb;
	$cache_key = 'lc_areas_kbn_' . $kbn . '_' . (int) $limit . ( $normalize ? '_n' : '_r' );
	$cached    = wp_cache_get( $cache_key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	$between = ( $kbn === 'rent' ) ? '3000 AND 3999' : '1000 AND 1999';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $between is hardcoded
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value AS addr, COUNT(*) AS cnt
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 INNER JOIN {$wpdb->postmeta} pm_kbn
		   ON pm_kbn.post_id = pm.post_id AND pm_kbn.meta_key = 'bukkenshubetsu'
		 WHERE pm.meta_key = %s
		   AND pm.meta_value <> ''
		   AND p.post_type = %s
		   AND p.post_status = %s
		   AND (pm_kbn.meta_value + 0) BETWEEN $between
		 GROUP BY pm.meta_value
		 ORDER BY cnt DESC
		 LIMIT %d",
		'shozaichimeisho', 'fudo', 'publish', max( 1, (int) $limit ) * 4
	) );

	$areas = array();
	foreach ( (array) $rows as $r ) {
		$name = (string) $r->addr;
		if ( $normalize ) {
			$tmp = preg_replace( '/^.*?[都道府県].*?[市区町村]/u', '', $name );
			if ( $tmp === '' || $tmp === null ) {
				$tmp = preg_replace( '/^.*?[市区町村]/u', '', $name );
			}
			$tmp = preg_replace( '/[0-9０-９一二三四五六七八九十]+丁目.*$/u', '', (string) $tmp );
			$tmp = trim( (string) $tmp );
			if ( $tmp !== '' ) { $name = $tmp; }
		}
		if ( ! isset( $areas[ $name ] ) ) { $areas[ $name ] = 0; }
		$areas[ $name ] += (int) $r->cnt;
	}
	arsort( $areas );
	$areas = array_slice( $areas, 0, (int) $limit, true );
	wp_cache_set( $cache_key, $areas, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $areas;
}

/**
 * fudo 物件の間取りリストを kbn でフィルタして返す。
 * fudou プラグインは部屋数を madorisu・種別コードを madorisyurui に分けて保存するため、
 * 両キーを JOIN して組み合わせラベル（例: 2LDK）を生成し、公開物件に実在するもののみ返す。
 *
 * madorisyurui コード対応: 10=R, 20=K, 25=SK, 30=DK, 35=SDK, 40=LK, 45=SLK, 50=LDK, 55=SLDK
 *
 * @param string $kbn 'rent' | 'sale' | '' (全件)
 * @return string[]
 */
function lc_get_fudo_madori_by_kbn( $kbn = '' ) {
	$standard = array( '1R', '1K', '1DK', '1LDK', '2K', '2DK', '2LDK', '3DK', '3LDK', '4LDK', '4LDK+' );
	$kbn      = sanitize_text_field( $kbn );

	global $wpdb;
	$cache_key = 'lc_madori_kbn_' . ( $kbn === '' ? 'all' : $kbn );
	$cached    = wp_cache_get( $cache_key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	// fudou の種別コード → 表示名マッピング（jsonmadori_kensaku.php 準拠）
	$syurui_map = array(
		'10' => 'R', '20' => 'K', '25' => 'SK', '30' => 'DK',
		'35' => 'SDK', '40' => 'LK', '45' => 'SLK', '50' => 'LDK', '55' => 'SLDK',
	);

	if ( $kbn === '' ) {
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT pm_su.meta_value AS madorisu, pm_sy.meta_value AS madorisyurui
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_su ON pm_su.post_id = p.ID AND pm_su.meta_key = 'madorisu'
			 INNER JOIN {$wpdb->postmeta} pm_sy ON pm_sy.post_id = p.ID AND pm_sy.meta_key = 'madorisyurui'
			 WHERE p.post_type = %s AND p.post_status = %s
			   AND pm_su.meta_value <> '' AND pm_sy.meta_value <> ''",
			'fudo', 'publish'
		) );
	} else {
		$between = ( $kbn === 'rent' ) ? '3000 AND 3999' : '1000 AND 1999';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $between is hardcoded
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT pm_su.meta_value AS madorisu, pm_sy.meta_value AS madorisyurui
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm_su  ON pm_su.post_id  = p.ID AND pm_su.meta_key  = 'madorisu'
			 INNER JOIN {$wpdb->postmeta} pm_sy  ON pm_sy.post_id  = p.ID AND pm_sy.meta_key  = 'madorisyurui'
			 INNER JOIN {$wpdb->postmeta} pm_kbn ON pm_kbn.post_id = p.ID AND pm_kbn.meta_key = 'bukkenshubetsu'
			 WHERE p.post_type = %s AND p.post_status = %s
			   AND pm_su.meta_value <> '' AND pm_sy.meta_value <> ''
			   AND (pm_kbn.meta_value + 0) BETWEEN $between",
			'fudo', 'publish'
		) );
	}

	// madorisu + syurui_map → ラベル（例: "2LDK"）の集合を構築
	$db_labels = array();
	foreach ( (array) $rows as $r ) {
		$su = trim( (string) $r->madorisu );
		$sy = trim( (string) $r->madorisyurui );
		if ( $su === '' || $sy === '' || ! isset( $syurui_map[ $sy ] ) ) { continue; }
		$db_labels[ strtoupper( $su . $syurui_map[ $sy ] ) ] = true;
	}

	$result = array();
	foreach ( $standard as $m ) {
		if ( isset( $db_labels[ strtoupper( $m ) ] ) ) {
			$result[] = $m;
		}
	}
	if ( empty( $result ) ) { $result = $standard; }
	wp_cache_set( $cache_key, $result, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $result;
}

/**
 * fudo 物件の設備カウントを kbn でフィルタして返す。
 *
 * @param string $kbn   'rent' | 'sale' | '' (全件 → lc_get_fudo_setsubi_counts に委譲)
 * @param int    $limit 最大返却件数
 * @return array<int, array{code:string, name:string, count:int}>
 */
function lc_get_fudo_setsubi_counts_by_kbn( $kbn = '', $limit = 50 ) {
	$kbn = sanitize_text_field( $kbn );
	if ( $kbn === '' ) { return lc_get_fudo_setsubi_counts( $limit ); }

	global $wpdb;
	$cache_key = 'lc_setsubi_kbn_' . $kbn . '_' . (int) $limit;
	$cached    = wp_cache_get( $cache_key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	$between = ( $kbn === 'rent' ) ? '3000 AND 3999' : '1000 AND 1999';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $between is hardcoded
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value AS codes
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 INNER JOIN {$wpdb->postmeta} pm_kbn
		   ON pm_kbn.post_id = pm.post_id AND pm_kbn.meta_key = 'bukkenshubetsu'
		 WHERE pm.meta_key = %s
		   AND pm.meta_value <> ''
		   AND p.post_type = %s
		   AND p.post_status = %s
		   AND (pm_kbn.meta_value + 0) BETWEEN $between",
		'setsubi', 'fudo', 'publish'
	) );

	$counts = array();
	foreach ( (array) $rows as $r ) {
		$codes = preg_split( '#/+#', (string) $r->codes, -1, PREG_SPLIT_NO_EMPTY );
		foreach ( (array) $codes as $code ) {
			$code = trim( (string) $code );
			if ( $code === '' || ! ctype_digit( $code ) ) { continue; }
			if ( ! isset( $counts[ $code ] ) ) { $counts[ $code ] = 0; }
			$counts[ $code ]++;
		}
	}
	if ( ! $counts ) {
		wp_cache_set( $cache_key, array(), 'lc_fudo', 5 * MINUTE_IN_SECONDS );
		return array();
	}

	global $work_setsubi;
	if ( empty( $work_setsubi ) && function_exists( 'work_setsubi_init_fudou' ) ) {
		work_setsubi_init_fudou();
	}
	if ( ! is_array( $work_setsubi ) ) { $work_setsubi = array(); }

	$result = array();
	foreach ( $counts as $code => $count ) {
		if ( ! isset( $work_setsubi[ $code ]['name'] ) ) { continue; }
		$result[] = array(
			'code'  => (string) $code,
			'name'  => (string) $work_setsubi[ $code ]['name'],
			'count' => (int) $count,
		);
	}
	usort( $result, function ( $a, $b ) { return $b['count'] - $a['count']; } );
	$result = array_slice( $result, 0, max( 1, (int) $limit ) );
	wp_cache_set( $cache_key, $result, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $result;
}

/**
 * 物件タクソノミーのターム一覧を取得（一時キャッシュ付き）。
 *
 * @param string $taxonomy タクソノミースラッグ
 * @param array  $args     get_terms() への追加引数
 * @return WP_Term[]
 */
function lc_get_fudo_terms( $taxonomy, $args = array() ) {
	$key = 'lc_terms_' . md5( $taxonomy . wp_json_encode( $args ) );
	$cached = wp_cache_get( $key, 'lc_fudo' );
	if ( is_array( $cached ) ) { return $cached; }

	$defaults = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
	);
	$terms = get_terms( wp_parse_args( $args, $defaults ) );
	if ( is_wp_error( $terms ) ) { $terms = array(); }

	wp_cache_set( $key, $terms, 'lc_fudo', 5 * MINUTE_IN_SECONDS );
	return $terms;
}

/**
 * フィルター配列から WP_Query 用 $args を構築する。
 *
 * 入力例:
 *   array(
 *     'kbn'       => 'rent',     // 'rent' or 'sale'
 *     'area'      => array(...), // 住所キーワード（shozaichimeisho メタを LIKE 検索）
 *     'mado'      => array(...), // 間取り
 *     'price_min' => '3',        // 万円
 *     'price_max' => '8',
 *     'tag'       => 'pet-ok',
 *     'orderby'   => 'date'|'price_asc'|'price_desc',
 *     'paged'     => 1,
 *     'posts_per_page' => 10,
 *   )
 *
 * @param array $filters
 * @return array WP_Query 引数
 */
function lc_build_fudo_query_args( $filters = array() ) {
	$filters = wp_parse_args( $filters, array(
		'kbn'            => '',
		'bukken_cat'     => 0,
		'area'           => array(),
		'mado'           => array(),
		'price_min'      => '',
		'price_max'      => '',
		'menseki_min'    => '',
		'menseki_max'    => '',
		'tag'            => '',
		'setsubi'        => array(),
		'orderby'        => 'date',
		'paged'          => 1,
		'posts_per_page' => 12,
		's'              => '',
	) );

	$args = array(
		'post_type'      => 'fudo',
		'posts_per_page' => (int) $filters['posts_per_page'],
		'paged'          => max( 1, (int) $filters['paged'] ),
	);
	if ( ! empty( $filters['s'] ) ) {
		$args['s'] = sanitize_text_field( $filters['s'] );
	}

	$meta_query = array( 'relation' => 'AND' );
	$tax_query  = array( 'relation' => 'AND' );

	// 種別フィルター
	//   fudou プラグインは meta_key 'bukkenshubetsu' に数字コードを保存：
	//     1101〜1599 = 売地・売戸建・売マン・売建物（売買系）
	//     3101〜3299 = 賃貸居住・賃貸事業（賃貸系）
	//   判定式は admin_fudou.php に従い < 3000 なら売買、>= 3000 なら賃貸。
	//   ほかの保存形式（独自カスタマイズで kbn='rent' などを直接入れている場合）にも
	//   保険として OR でフォールバック。
	if ( ! empty( $filters['kbn'] ) ) {
		$kbn       = sanitize_text_field( $filters['kbn'] );
		$kbn_label = ( $kbn === 'sale' ) ? '売買' : ( ( $kbn === 'rent' ) ? '賃貸' : '' );

		if ( $kbn === 'rent' ) {
			$shubetsu_range = array( 3000, 3999 );
		} elseif ( $kbn === 'sale' ) {
			$shubetsu_range = array( 1000, 1999 );
		} else {
			$shubetsu_range = null;
		}

		$kbn_or = array( 'relation' => 'OR' );
		if ( $shubetsu_range ) {
			$kbn_or[] = array(
				'key'     => 'bukkenshubetsu',
				'value'   => $shubetsu_range,
				'compare' => 'BETWEEN',
				'type'    => 'NUMERIC',
			);
		}
		// 旧仕様／独自フィールド互換
		$kbn_or[] = array( 'key' => 'kbn',      'value' => $kbn,       'compare' => '=' );
		$kbn_or[] = array( 'key' => 'shubetsu', 'value' => $kbn,       'compare' => '=' );
		if ( $kbn_label ) {
			$kbn_or[] = array( 'key' => 'kbn', 'value' => $kbn_label, 'compare' => '=' );
		}
		$meta_query[] = $kbn_or;

		if ( taxonomy_exists( 'fudo_kbn' ) ) {
			// 競合しないように OR ではなく追加情報として併設
			$tax_query[] = array(
				'taxonomy' => 'fudo_kbn',
				'field'    => 'slug',
				'terms'    => array( $kbn ),
				'operator' => 'IN',
			);
		}
	}

	// エリア — fudou は専用タクソノミー無し。住所メタ(shozaichimeisho 等)で LIKE 検索
	if ( ! empty( $filters['area'] ) ) {
		$areas = array_filter( array_map( 'sanitize_text_field', (array) $filters['area'] ) );
		if ( $areas ) {
			$addr_or = array( 'relation' => 'OR' );
			foreach ( $areas as $a ) {
				foreach ( array( 'shozaichimeisho', 'shozaichi', 'address', 'jusho' ) as $k ) {
					$addr_or[] = array( 'key' => $k, 'value' => $a, 'compare' => 'LIKE' );
				}
			}
			$meta_query[] = $addr_or;
		}
	}

	// 間取り — fudou は madorisu（部屋数）と madorisyurui（種別コード）で分けて保存
	// 標準ラベル→コード対応: 10=R, 20=K, 25=SK, 30=DK, 35=SDK, 40=LK, 45=SLK, 50=LDK, 55=SLDK
	if ( ! empty( $filters['mado'] ) ) {
		$mado_list = array_filter( array_map( 'sanitize_text_field', (array) $filters['mado'] ) );
		if ( $mado_list ) {
			$mado_label_map = array(
				'1R'    => array( 'su' => '1', 'sy' => '10' ),
				'1K'    => array( 'su' => '1', 'sy' => '20' ),
				'1DK'   => array( 'su' => '1', 'sy' => '30' ),
				'1LDK'  => array( 'su' => '1', 'sy' => '50' ),
				'2K'    => array( 'su' => '2', 'sy' => '20' ),
				'2DK'   => array( 'su' => '2', 'sy' => '30' ),
				'2LDK'  => array( 'su' => '2', 'sy' => '50' ),
				'3DK'   => array( 'su' => '3', 'sy' => '30' ),
				'3LDK'  => array( 'su' => '3', 'sy' => '50' ),
				'4LDK'  => array( 'su' => '4', 'sy' => '50' ),
				'4LDK+' => array( 'su' => '4', 'sy' => '50', 'su_gte' => true ),
			);
			$mado_or = array( 'relation' => 'OR' );
			foreach ( $mado_list as $m ) {
				$parsed = isset( $mado_label_map[ strtoupper( $m ) ] ) ? $mado_label_map[ strtoupper( $m ) ] : null;
				if ( ! $parsed ) { continue; }
				$mado_or[] = array(
					'relation' => 'AND',
					array(
						'key'     => 'madorisu',
						'value'   => $parsed['su'],
						'compare' => ! empty( $parsed['su_gte'] ) ? '>=' : '=',
						'type'    => ! empty( $parsed['su_gte'] ) ? 'NUMERIC' : 'CHAR',
					),
					array(
						'key'     => 'madorisyurui',
						'value'   => $parsed['sy'],
						'compare' => '=',
					),
				);
			}
			if ( count( $mado_or ) > 1 ) {
				$meta_query[] = $mado_or;
			}
		}
	}

	// 価格範囲 (万円単位 → 円単位)
	$min = $filters['price_min'] !== '' ? lc_parse_price( $filters['price_min'] ) : null;
	$max = $filters['price_max'] !== '' ? lc_parse_price( $filters['price_max'] ) : null;
	// 入力値が "3" のように万円のみだった場合は万円→円へ補正
	if ( $min !== null && $min > 0 && $min < 10000 ) { $min *= 10000; }
	if ( $max !== null && $max > 0 && $max < 10000 ) { $max *= 10000; }
	if ( $min !== null || $max !== null ) {
		$range = array( 'relation' => 'OR' );
		foreach ( array( 'kakaku', 'price', 'bukken_kakaku' ) as $k ) {
			if ( $min !== null && $max !== null ) {
				$range[] = array(
					'key'     => $k,
					'value'   => array( $min, $max ),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				);
			} elseif ( $min !== null ) {
				$range[] = array(
					'key'     => $k,
					'value'   => $min,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				);
			} elseif ( $max !== null ) {
				$range[] = array(
					'key'     => $k,
					'value'   => $max,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				);
			}
		}
		$meta_query[] = $range;
	}

	// 面積範囲 (m² 単位、menseki 数値メタ)
	$mmin = $filters['menseki_min'] !== '' ? (float) preg_replace( '/[^0-9.]/', '', (string) $filters['menseki_min'] ) : null;
	$mmax = $filters['menseki_max'] !== '' ? (float) preg_replace( '/[^0-9.]/', '', (string) $filters['menseki_max'] ) : null;
	if ( $mmin !== null || $mmax !== null ) {
		$mens_or = array( 'relation' => 'OR' );
		foreach ( array( 'menseki', 'area', 'senyu_menseki', 'kenchiku_menseki' ) as $k ) {
			if ( $mmin !== null && $mmax !== null ) {
				$mens_or[] = array(
					'key'     => $k,
					'value'   => array( $mmin, $mmax ),
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL',
				);
			} elseif ( $mmin !== null ) {
				$mens_or[] = array( 'key' => $k, 'value' => $mmin, 'compare' => '>=', 'type' => 'DECIMAL' );
			} elseif ( $mmax !== null ) {
				$mens_or[] = array( 'key' => $k, 'value' => $mmax, 'compare' => '<=', 'type' => 'DECIMAL' );
			}
		}
		if ( count( $mens_or ) > 1 ) {
			$meta_query[] = $mens_or;
		}
	}

	// 設備・条件 — fudou の 'setsubi' メタ（スラッシュ区切りの数字コード）を LIKE で検索
	if ( ! empty( $filters['setsubi'] ) ) {
		$codes = array_filter( array_map( 'sanitize_text_field', (array) $filters['setsubi'] ) );
		$setsubi_or = array( 'relation' => 'OR' );
		foreach ( $codes as $c ) {
			if ( ! ctype_digit( $c ) ) { continue; }
			$setsubi_or[] = array(
				'key'     => 'setsubi',
				'value'   => '/' . $c . '/',
				'compare' => 'LIKE',
			);
		}
		if ( count( $setsubi_or ) > 1 ) {
			$meta_query[] = $setsubi_or;
		}
	}

	// タグ — fudou は 'bukken_tag' タクソノミーを使用
	if ( ! empty( $filters['tag'] ) ) {
		$tags = is_array( $filters['tag'] )
			? array_filter( array_map( 'sanitize_title', $filters['tag'] ) )
			: array_filter( array_map( 'sanitize_title', explode( ',', $filters['tag'] ) ) );
		if ( $tags ) {
			$tag_tax = taxonomy_exists( 'bukken_tag' ) ? 'bukken_tag' : ( taxonomy_exists( 'fudo_tag' ) ? 'fudo_tag' : '' );
			if ( $tag_tax ) {
				$tax_query[] = array(
					'taxonomy' => $tag_tax,
					'field'    => 'slug',
					'terms'    => $tags,
					'operator' => 'IN',
				);
			}
		}
	}

	// 物件カテゴリ絞り込み（bukken タクソノミー）
	if ( ! empty( $filters['bukken_cat'] ) && (int) $filters['bukken_cat'] > 0 ) {
		$tax_query[] = array(
			'taxonomy' => 'bukken',
			'field'    => 'term_id',
			'terms'    => (int) $filters['bukken_cat'],
		);
	}

	if ( count( $meta_query ) > 1 ) { $args['meta_query'] = $meta_query; }
	if ( count( $tax_query )  > 1 ) { $args['tax_query']  = $tax_query; }

	// 並び替え
	switch ( $filters['orderby'] ) {
		case 'price_asc':
			$args['meta_key'] = 'kakaku';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'price_desc':
			$args['meta_key'] = 'kakaku';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'area_desc':
			$args['meta_key'] = 'menseki';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'area_asc':
			$args['meta_key'] = 'menseki';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'rand':
			$args['orderby'] = 'rand';
			break;
		case 'date':
		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
	}

	return $args;
}

/**
 * GET パラメータから lc_build_fudo_query_args() へ渡すフィルタを抽出。
 *
 * @return array
 */
function lc_get_fudo_filters_from_request() {
	$paged = (int) ( get_query_var( 'paged' ) ?: 1 );
	return array(
		'kbn'       => isset( $_GET['kbn'] )       ? sanitize_text_field( wp_unslash( $_GET['kbn'] ) ) : '',
		'area'      => isset( $_GET['area'] )      ? (array) wp_unslash( $_GET['area'] ) : array(),
		'mado'      => isset( $_GET['mado'] )      ? (array) wp_unslash( $_GET['mado'] ) : array(),
		'price_min' => isset( $_GET['price_min'] ) ? sanitize_text_field( wp_unslash( $_GET['price_min'] ) ) : '',
		'price_max' => isset( $_GET['price_max'] ) ? sanitize_text_field( wp_unslash( $_GET['price_max'] ) ) : '',
		'menseki_min' => isset( $_GET['menseki_min'] ) ? sanitize_text_field( wp_unslash( $_GET['menseki_min'] ) ) : '',
		'menseki_max' => isset( $_GET['menseki_max'] ) ? sanitize_text_field( wp_unslash( $_GET['menseki_max'] ) ) : '',
		'tag'       => isset( $_GET['tag'] )       ? wp_unslash( $_GET['tag'] ) : '',
		'setsubi'   => isset( $_GET['setsubi'] )   ? (array) wp_unslash( $_GET['setsubi'] ) : array(),
		'orderby'   => isset( $_GET['orderby'] )   ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'date',
		's'         => isset( $_GET['s'] )         ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		'paged'     => $paged,
	);
}

/**
 * ブロック用物件カード描画（表示項目選択対応）。
 * lc_render_fudo_card() の拡張版。
 *
 * @param int    $post_id        投稿ID
 * @param string $tag_class      'tag-rent' | 'tag-sale'
 * @param string $tag_label      '賃貸' | '売買'
 * @param array  $display_items  array('title','price','layout','area') 表示有無
 * @param string $button_text    詳細ボタン文言
 */
/**
 * fudou プラグインの fudoimg1 / fudoimg2 メタを URL に解決する。
 *   - すでに http(s):// から始まる文字列はそのまま返す
 *   - ファイル名のみが入っている場合は posts.guid LIKE で attachment ID を引き当て、
 *     wp_get_attachment_image_url() で URL を取得する
 *   - 最終フォールバックは wp_get_upload_dir()['baseurl'] + ファイル名
 *
 * @param string $value     メタ値
 * @param string $size      画像サイズ
 * @return string 解決済み URL（空文字なら解決失敗）
 */
function lc_resolve_fudo_image_url( $value, $size = 'medium_large' ) {
	$value = trim( (string) $value );
	if ( $value === '' ) { return ''; }

	// プラグインが提供するフィルタを尊重（外部ドメイン化など）
	if ( has_filter( 'pre_fudoimg_data_add_url' ) ) {
		$value = (string) apply_filters( 'pre_fudoimg_data_add_url', $value, get_the_ID() );
		$value = trim( $value );
		if ( $value === '' ) { return ''; }
	}

	// 既に URL or サイト相対パス
	if ( preg_match( '#^https?://#i', $value ) || strpos( $value, '/' ) === 0 ) {
		return $value;
	}

	// ファイル名のみ → guid LIKE で attachment を検索
	global $wpdb;
	$attachment_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts}
		 WHERE post_type = 'attachment' AND guid LIKE %s
		 ORDER BY ID DESC LIMIT 1",
		'%/' . $wpdb->esc_like( $value )
	) );
	if ( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) { return $url; }
	}

	// 最終フォールバック: uploads ベース URL + ファイル名
	$uploads = wp_get_upload_dir();
	if ( ! empty( $uploads['baseurl'] ) ) {
		return trailingslashit( $uploads['baseurl'] ) . $value;
	}
	return '';
}

/**
 * fudo 物件の外観写真（fudoimg1, fudoimg2）を URL の配列で返す。
 * 両方とも未登録の場合はフィーチャー画像 → ダミーの順にフォールバック。
 *
 * @param int $post_id
 * @param int $max     最大取得枚数（デフォルト 2）
 * @return string[] 画像 URL の配列（必ず 1 件以上）
 */
function lc_get_fudo_image_urls( $post_id, $max = 2 ) {
	$urls = array();
	for ( $i = 1; $i <= $max; $i++ ) {
		$val = (string) get_post_meta( $post_id, 'fudoimg' . $i, true );
		if ( $val === '' ) { continue; }
		$url = lc_resolve_fudo_image_url( $val, 'medium_large' );
		if ( $url !== '' ) { $urls[] = $url; }
	}

	if ( empty( $urls ) ) {
		$thumb = get_the_post_thumbnail_url( $post_id, 'medium_large' );
		if ( $thumb ) { $urls[] = $thumb; }
	}

	if ( empty( $urls ) ) {
		$urls[] = 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=600&q=80';
	}

	return $urls;
}

/**
 * fudo 物件のギャラリー（fudoimg1〜fudoimgN）を URL 配列で返す。
 * single-fudo.php の大ギャラリー表示用。最大30枚まで列挙し、登録のあるものだけを返す。
 *
 * @param int $post_id
 * @param int $max     最大列挙数（プラグイン仕様の上限。デフォルト 30）
 * @return string[] 解決済み URL の配列（0件もあり得る）
 */
function lc_get_fudo_gallery_urls( $post_id, $max = 30 ) {
	$urls = array();
	for ( $i = 1; $i <= $max; $i++ ) {
		$val = (string) get_post_meta( $post_id, 'fudoimg' . $i, true );
		if ( $val === '' ) { continue; }
		$url = lc_resolve_fudo_image_url( $val, 'large' );
		if ( $url !== '' ) { $urls[] = $url; }
	}
	if ( empty( $urls ) ) {
		$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( $thumb ) { $urls[] = $thumb; }
	}
	return $urls;
}

/**
 * fudo 物件の設備（setsubi メタ）を解決済みの名前配列で返す。
 *
 *   メタ: "0102/0103/0205" のようなスラッシュ区切り数値コード
 *   解決: fudou プラグイン data/work-fudo.php の $work_setsubi マスタで name に
 *
 * @param int $post_id
 * @return array<int, array{code:string, name:string}> 名前順は保存順を維持
 */
function lc_get_fudo_setsubi_names( $post_id ) {
	$raw = (string) get_post_meta( $post_id, 'setsubi', true );
	if ( $raw === '' ) { return array(); }

	$codes = preg_split( '#/+#', $raw, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! $codes ) { return array(); }

	global $work_setsubi;
	if ( empty( $work_setsubi ) && function_exists( 'work_setsubi_init_fudou' ) ) {
		work_setsubi_init_fudou();
	}
	if ( ! is_array( $work_setsubi ) ) { $work_setsubi = array(); }

	$out = array();
	foreach ( $codes as $code ) {
		$code = trim( (string) $code );
		if ( $code === '' || ! ctype_digit( $code ) ) { continue; }
		if ( ! isset( $work_setsubi[ $code ]['name'] ) ) { continue; }
		$out[] = array(
			'code' => $code,
			'name' => (string) $work_setsubi[ $code ]['name'],
		);
	}
	return $out;
}

/**
 * 物件が「売買」か「賃貸」かを判定する。
 *
 * fudou プラグインの主キーは `bukkenshubetsu`（4桁数値、<3000=売買 / ≥3000=賃貸）。
 * 旧仕様の `kbn` / `shubetsu` 文字列もフォールバック判定。
 *
 * @param int $post_id
 * @return bool true=売買 / false=賃貸（デフォルト賃貸）
 */
function lc_is_fudo_sale( $post_id ) {
	$code = (int) get_post_meta( $post_id, 'bukkenshubetsu', true );
	if ( $code > 0 ) {
		return $code < 3000;
	}
	// 旧仕様フォールバック
	$kbn = (string) lc_get_fudo_meta( $post_id, array( 'kbn', 'shubetsu' ), '' );
	if ( $kbn === '' ) { return false; }
	if ( $kbn === 'sale' || $kbn === '売買' ) { return true; }
	if ( strpos( $kbn, '売' ) !== false ) { return true; }
	return false;
}

/**
 * 価格メタの数値を万円表記に変換する。
 *
 * fudou プラグイン 5.9.0+ の fudou_money_format_ja フィルターを優先使用。
 * 200万超 → 売買扱い（億・万・円）、200万以下 → 賃貸扱い（万円）で自動分岐。
 * プラグイン未使用環境では自前の万円変換にフォールバック。
 *
 * @param  mixed $raw  メタから取得した生の価格値（円単位の整数）
 * @return string      整形済み文字列（例: "4.5万円", "1,234万円", "1億2,345万円"）
 */
function lc_format_price( $raw ) {
	if ( ! is_numeric( $raw ) ) {
		return (string) $raw; // すでに文字列フォーマット済みの値はそのまま返す
	}
	// fudou プラグイン 5.9.0+ の億万円フォーマッタを優先使用
	if ( function_exists( 'fudou_money_format_ja' ) ) {
		return trim( apply_filters( 'fudou_money_format_ja', $raw ) );
	}
	// フォールバック（プラグイン未使用環境向け）
	$man = floatval( $raw ) / 10000;
	$str = rtrim( rtrim( number_format( $man, 4, '.', ',' ), '0' ), '.' );
	return $str . '万円';
}

function lc_render_property_block_card( $post_id, $tag_class = 'tag-rent', $tag_label = '賃貸', $display_items = array(), $button_text = '物件詳細を見る', $newup_days = 14 ) {
	$display_items = wp_parse_args( (array) $display_items, array(
		'title' => true, 'price' => true, 'layout' => true, 'area' => true,
	) );

	$image_urls    = lc_get_fudo_image_urls( $post_id, 2 );

	$price         = lc_get_fudo_meta( $post_id, array( 'kakaku', 'price', 'bukken_kakaku' ) );
	$madori        = lc_get_fudo_madori_label( $post_id );
	$menseki       = lc_get_fudo_meta( $post_id, array( 'menseki', 'area', 'senyu_menseki' ) );
	$address       = lc_get_fudo_meta( $post_id, array( 'shozaichi', 'address', 'jusho' ) );
	$newup_days    = max( 0, (int) $newup_days );
	$post_date_u   = get_post_time( 'U', false, $post_id );
	$post_mod_u    = get_post_modified_time( 'U', false, $post_id );
	$threshold     = $newup_days * DAY_IN_SECONDS;
	$is_new        = $newup_days > 0 && ( time() - $post_date_u ) < $threshold && ( $post_mod_u - $post_date_u ) < 60;
	$is_up         = $newup_days > 0 && ! $is_new && ( time() - $post_mod_u ) < $threshold;
	$seiyakubi     = get_post_meta( $post_id, 'seiyakubi', true );
	$kakakukoukai  = get_post_meta( $post_id, 'kakakukoukai', true );
	$kakakujoutai  = get_post_meta( $post_id, 'kakakujoutai', true );
	$permalink     = get_permalink( $post_id );

	// 価格表示文字列を確定（成約済み > 非公開 > 通常）
	$price_label = '';
	if ( $seiyakubi !== '' ) {
		$price_label = 'ご成約済み';
	} elseif ( $kakakukoukai === '0' ) {
		$joutai_map  = array( '1' => '相談', '2' => '確定', '3' => '入札' );
		$price_label = isset( $joutai_map[ $kakakujoutai ] ) ? $joutai_map[ $kakakujoutai ] : '価格相談';
	} elseif ( $price !== '' && $price !== false ) {
		$price_label = lc_format_price( $price );
	}

	$article_class = 'hentry' . ( $seiyakubi !== '' ? ' is-seiyaku' : '' );
	?>
	<article class="<?php echo esc_attr( $article_class ); ?>">
		<?php if ( ! empty( $display_items['title'] ) ) : ?>
		<div class="list_simple_boxtitle">
			<h2 class="entry-title">
				<a href="<?php echo esc_url( $permalink ); ?>"><span class="tag-bukken <?php echo esc_attr( $tag_class ); ?>"><?php echo esc_html( $tag_label ); ?></span><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
			</h2>
		</div>
		<?php endif; ?>
		<div class="list_simple_box">
			<div class="list_picsam_img">
				<?php if ( $is_new ) : ?><span class="new_mark">NEW</span><?php elseif ( $is_up ) : ?><span class="up_mark">UP</span><?php endif; ?>
				<div class="list_picsam_img-grid lc-pics-<?php echo (int) count( $image_urls ); ?>">
					<?php foreach ( $image_urls as $img_url ) : ?>
						<img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy" />
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( ( ! empty( $display_items['price'] ) && $price_label !== '' ) || ( ! empty( $display_items['layout'] ) && ( $madori || $menseki ) ) ) : ?>
				<div class="bukken-meta">
					<?php if ( ! empty( $display_items['price'] ) && $price_label !== '' ) : ?>
						<span class="dpoint1"><?php echo esc_html( $price_label ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $display_items['layout'] ) && ( $madori || $menseki ) ) : ?>
						<span class="dpoint2"><?php echo esc_html( trim( ( $madori ? $madori : '' ) . ' ／ ' . ( $menseki ? $menseki : '' ), ' ／' ) ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $display_items['area'] ) && $address ) : ?>
				<div class="bukken-addr"><?php echo esc_html( $address ); ?></div>
			<?php endif; ?>
			<a href="<?php echo esc_url( $permalink ); ?>"><div class="list_details_button">→ <?php echo esc_html( $button_text ); ?></div></a>
		</div>
	</article>
	<?php
}

/* =============================================================
 * 12. Gutenberg ブロック自動登録
 *    blocks/src 以下の各ブロックフォルダにある block.json を読み込み
 *    render.php があればサーバーサイドレンダリングで出力する
 * ============================================================= */
add_action( 'init', function () {
	$blocks_dir = get_stylesheet_directory() . '/blocks/src';
	if ( ! is_dir( $blocks_dir ) ) { return; }

	$dirs = scandir( $blocks_dir );
	foreach ( $dirs as $d ) {
		if ( $d === '.' || $d === '..' ) { continue; }
		$block_path = $blocks_dir . '/' . $d;
		$meta_file  = $block_path . '/block.json';
		$render_php = $block_path . '/render.php';

		if ( is_dir( $block_path ) && file_exists( $meta_file ) ) {
			if ( file_exists( $render_php ) ) {
				register_block_type( $meta_file, array(
					'render_callback' => function( $attributes, $content ) use ( $render_php ) {
						ob_start();
						include $render_php;
						return (string) ob_get_clean();
					}
				) );
			} else {
				register_block_type( $meta_file );
			}
		}
	}
} );

/* =============================================================
 * 13. ブロック CSS の読み込み — blocks/css 配下のスタイルをフロントへ
 * ============================================================= */
add_action( 'wp_enqueue_scripts', function () {
	$css_dir = get_stylesheet_directory() . '/blocks/css';
	$css_uri = get_stylesheet_directory_uri() . '/blocks/css';
	if ( ! is_dir( $css_dir ) ) { return; }

	$files = glob( $css_dir . '/*.css' );
	if ( ! $files ) { return; }

	foreach ( $files as $file ) {
		$name = basename( $file, '.css' );
		wp_enqueue_style(
			'lc-block-' . $name,
			$css_uri . '/' . basename( $file ),
			array( 'lc-child-theme' ),
			(string) filemtime( $file )
		);
	}
}, 30 );

/* =============================================================
 * 15. カスタマイザー — ヒーロー設定・会社情報
 * ============================================================= */
function lc_sanitize_map_embed( $value ) {
	return wp_kses( $value, array(
		'iframe' => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'style'           => true,
		),
	) );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

	/* ── パネル1: ヒーロー設定 ── */
	$wp_customize->add_panel( 'lc_hero_panel', array(
		'title'    => 'ヒーロー設定',
		'priority' => 30,
	) );

	// セクション1-1: テキスト・ボタン
	$wp_customize->add_section( 'lc_hero_content', array(
		'title' => 'テキスト・ボタン',
		'panel' => 'lc_hero_panel',
	) );

	$hero_fields = array(
		'lc_hero_eyebrow'   => array( 'label' => 'キャッチコピー（上段）',   'default' => 'WAKKANAI REAL ESTATE', 'type' => 'text' ),
		'lc_hero_title'     => array( 'label' => 'タイトル（&lt;br&gt;/&lt;em&gt;使用可）', 'default' => '「部屋を選ぶ」＝<br/>「これからの<em>生活</em>を選ぶ」こと', 'type' => 'textarea' ),
		'lc_hero_sub'       => array( 'label' => 'サブテキスト',              'default' => '日本最北の街、稚内で40年。地域密着の不動産会社が、あなたにちょうどいい暮らしをお手伝いします。', 'type' => 'textarea' ),
		'lc_hero_cta_label' => array( 'label' => 'ボタンラベル',             'default' => '物件を探す', 'type' => 'text' ),
		'lc_hero_cta_url'   => array( 'label' => 'ボタンリンク先 URL',       'default' => '', 'type' => 'url' ),
	);
	$priority = 10;
	foreach ( $hero_fields as $id => $f ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $f['default'],
			'transport'         => 'refresh',
			'sanitize_callback' => ( $f['type'] === 'url' )
				? 'esc_url_raw'
				: ( $id === 'lc_hero_title'
					? function( $v ) { return wp_kses( $v, array( 'br' => array(), 'em' => array(), 'strong' => array() ) ); }
					: 'sanitize_text_field' ),
		) );
		$wp_customize->add_control( $id, array(
			'label'    => $f['label'],
			'section'  => 'lc_hero_content',
			'type'     => $f['type'],
			'priority' => $priority,
		) );
		$priority += 10;
	}

	// セクション1-2: 背景画像
	$wp_customize->add_section( 'lc_hero_image', array(
		'title' => '背景画像',
		'panel' => 'lc_hero_panel',
	) );
	$wp_customize->add_setting( 'lc_hero_image_url', array(
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lc_hero_image_url', array(
		'label'   => 'ヒーロー背景画像',
		'section' => 'lc_hero_image',
	) ) );

	/* ── パネル2: 会社情報 ── */
	$wp_customize->add_panel( 'lc_company_panel', array(
		'title'    => '会社情報',
		'priority' => 31,
	) );

	$wp_customize->add_section( 'lc_company_info', array(
		'title' => 'フッター会社情報',
		'panel' => 'lc_company_panel',
	) );

	// フッターロゴ
	$wp_customize->add_setting( 'lc_company_footer_logo', array(
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lc_company_footer_logo', array(
		'label'    => 'フッターロゴ（推奨: 白抜き PNG）',
		'section'  => 'lc_company_info',
		'priority' => 10,
	) ) );

	// 郵便番号・住所・電話番号・宅建番号
	$company_fields = array(
		'lc_company_postal'   => array( 'label' => '郵便番号（ハイフンなし or あり）', 'default' => '097-0017',    'priority' => 20 ),
		'lc_company_address'  => array( 'label' => '住所',                              'default' => '北海道稚内市栄5丁目7番5号 コーポ サンロード1F', 'priority' => 30 ),
		'lc_company_tel'      => array( 'label' => '電話番号',                          'default' => '0162-32-8877', 'priority' => 40 ),
		'lc_company_license'  => array( 'label' => '宅建業免許番号',                    'default' => '宅地建物取引業免許　北海道知事 宗谷(4) 第53号', 'priority' => 50 ),
	);
	foreach ( $company_fields as $id => $f ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $f['default'],
			'transport'         => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $id, array(
			'label'    => $f['label'],
			'section'  => 'lc_company_info',
			'type'     => 'text',
			'priority' => $f['priority'],
		) );
	}

	// Google マップ埋め込みコード（<iframe> タグをそのまま貼る）
	$wp_customize->add_setting( 'lc_company_map_embed', array(
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'lc_sanitize_map_embed',
	) );
	$wp_customize->add_control( 'lc_company_map_embed', array(
		'label'       => 'Google マップ 埋め込みコード（iframeタグ）',
		'description' => 'Google マップ → 共有 → 地図を埋め込む で取得した iframe コードを貼り付けてください。',
		'section'     => 'lc_company_info',
		'type'        => 'textarea',
		'priority'    => 60,
	) );
} );
