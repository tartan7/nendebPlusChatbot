<?php
/**
 * Lumin Coco — 旧URLのリダイレクト（副読本「家に関する情報」セクション）
 * ============================================================================
 *
 *  /recom11/         → /recommend/guide/trouble/   （住宅・賃貸トラブル）
 *  /recom12/         → /recommend/                  （ハブ化）
 *  /recommend/link/  → /recommend/resources/        （関連リンク集にリネーム）
 *
 *  ＋ /recom12/ の子URL（あれば）は章別の新URLへ。スラッグが分かったものだけ
 *    LC_LEGACY_RECOM12_MAP に追加していけば追従できます。
 *
 *  使い方:
 *    functions.php の末尾に次の1行を追加してください。
 *      require_once get_stylesheet_directory() . '/inc/legacy-redirects.php';
 *
 *  方針:
 *    - 301 Permanent Redirect（恒久移動）
 *    - パスのトレイリングスラッシュを正規化してから判定
 *    - クエリ文字列とフラグメントは可能な範囲で引き継ぐ
 *    - 検索エンジン・ブラウザのキャッシュを尊重するため WP の template_redirect で処理
 *    - リダイレクト対象は配列で管理し、追加・除外を容易に
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ----------------------------------------------------------------------------
 * 1. 単純な 1:1 マッピング
 * --------------------------------------------------------------------------- */
const LC_LEGACY_PATH_MAP = array(
	'/recom11'        => '/recommend/guide/trouble/',
	'/recom11/'       => '/recommend/guide/trouble/',
	'/recommend/link' => '/recommend/resources/',
	'/recommend/link/' => '/recommend/resources/',
);

/* ----------------------------------------------------------------------------
 * 2. /recom12/ の章分割マップ
 *    旧サイトで使われていた末尾スラッグ → 新URL
 *    既存ページがあればここに追記してください。マッチしない場合は
 *    /recommend/（ハブ）へフォールバックします。
 * --------------------------------------------------------------------------- */
const LC_LEGACY_RECOM12_MAP = array(
	// 例（実在URLが判明したらコメントアウトを外す）
	// 'rent'    => '/recommend/guide/contract-rent/',
	// 'buy'     => '/recommend/guide/contract-buy/',
	// 'money'   => '/recommend/guide/money-tax-loan/',
	// 'tax'     => '/recommend/guide/money-tax-loan/',
	// 'loan'    => '/recommend/guide/money-tax-loan/',
	// 'moving'  => '/recommend/guide/moving/',
);

/* ----------------------------------------------------------------------------
 * 3. テンプレート読み込み前にリダイレクト
 * --------------------------------------------------------------------------- */
add_action( 'template_redirect', 'lc_legacy_recommend_redirects', 1 );

function lc_legacy_recommend_redirects() {

	// 管理画面・REST・cron などは対象外
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( $request === '' ) { return; }

	// パスとクエリに分割
	$parts = explode( '?', $request, 2 );
	$path  = $parts[0];
	$query = isset( $parts[1] ) ? $parts[1] : '';

	// 末尾スラッシュを揃えるための正規化キー（前後の "/" を除いて使う）
	$norm = '/' . trim( rawurldecode( $path ), '/' );

	$target = null;

	/* --- 3-1. 1:1 マッピング --- */
	if ( isset( LC_LEGACY_PATH_MAP[ $norm ] ) ) {
		$target = LC_LEGACY_PATH_MAP[ $norm ];
	} elseif ( isset( LC_LEGACY_PATH_MAP[ $norm . '/' ] ) ) {
		$target = LC_LEGACY_PATH_MAP[ $norm . '/' ];
	}

	/* --- 3-2. /recom12/ 配下 --- */
	if ( $target === null && preg_match( '#^/recom12(?:/(.+?))?/?$#', $norm, $m ) ) {
		$slug = isset( $m[1] ) ? strtolower( $m[1] ) : '';
		// 末尾以降が空、または未マッピング → /recommend/ ハブへ
		$target = '/recommend/';
		if ( $slug !== '' ) {
			// "rent/foo" のような深いパスでも、第1セグメントだけで判定
			$first = explode( '/', $slug )[0];
			if ( isset( LC_LEGACY_RECOM12_MAP[ $first ] ) ) {
				$target = LC_LEGACY_RECOM12_MAP[ $first ];
			}
		}
	}

	if ( $target === null ) { return; }

	// クエリ文字列を引き継ぐ（target に ? が既にある場合は & で連結）
	if ( $query !== '' ) {
		$target .= ( strpos( $target, '?' ) === false ? '?' : '&' ) . $query;
	}

	// ループ防止：すでに同じパスにいる場合は何もしない
	$current_norm = '/' . trim( rawurldecode( $parts[0] ), '/' ) . '/';
	$target_norm  = '/' . trim( strtok( ltrim( $target, '/' ), '?#' ), '/' ) . '/';
	if ( $current_norm === $target_norm ) { return; }

	wp_safe_redirect( home_url( $target ), 301, 'Lumin Coco Legacy Redirect' );
	exit;
}

/* ----------------------------------------------------------------------------
 * 4. （任意）旧URL記載のブックマーク用に、移行先で一度だけ案内バナーを出す
 *    URL末尾に ?from=recom11 などが付いてやってきた読者へ。
 *
 *    使い方：移行先テンプレ側で
 *      <?php lc_legacy_origin_banner(); ?>
 *    を呼び出すと、対応する案内 HTML を返します。
 * --------------------------------------------------------------------------- */
function lc_legacy_origin_banner() {
	if ( empty( $_GET['from'] ) ) { return; }
	$from = sanitize_key( $_GET['from'] );

	$messages = array(
		'recom11' => '旧 <code>/recom11/</code>（住宅トラブル対処法）はこちらのページに統合されました。',
		'recom12' => '旧 <code>/recom12/</code>（不動産アドバイス）の内容は副読本トップから章別にアクセスできます。',
		'link'    => '旧 <code>/recommend/link/</code> は <a href="/recommend/resources/">関連リンク集</a> にリネームしました。',
	);

	if ( empty( $messages[ $from ] ) ) { return; }
	?>
	<div class="rec-migrated" role="status" aria-live="polite">
		<span aria-hidden="true">↪</span>
		<span><b><?php echo wp_kses_post( $messages[ $from ] ); ?></b> 旧URLは自動で転送されます。ブックマークの修正をお願いします。</span>
	</div>
	<?php
}

/* ----------------------------------------------------------------------------
 * 5. （任意・推奨）Search Console / アクセス解析用に、リダイレクト発生を
 *    ログへ残したい場合のフック例。デフォルトは無効。
 * --------------------------------------------------------------------------- */
// add_action( 'template_redirect', function () {
//     if ( ! empty( $_SERVER['REQUEST_URI'] ) && preg_match( '#^/(recom11|recom12|recommend/link)#', $_SERVER['REQUEST_URI'] ) ) {
//         error_log( '[lc-legacy-redirect] ' . $_SERVER['REQUEST_URI'] );
//     }
// }, 0 );
