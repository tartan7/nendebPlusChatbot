<?php
/**
 * Classic Widget wrappers for syn-ownd-child Gutenberg blocks.
 *
 * classic-widgets プラグインが有効な場合、Gutenberg ベースのブロックを
 * ウィジェットエリアに直接挿入できない。本ファイルでは各ブロックを
 * 従来型の WP_Widget としてラップし、従来UI／ブロックUIの両方で
 * 利用可能にする。
 *
 * 各ウィジェットの widget() メソッドは、対応するブロックの render.php を
 * 同じ $attributes 規約で include することで、表示ロジックを完全共有する。
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 共通基底クラス。
 * サブクラスは $block_slug と defaults() / form() を実装すればよい。
 */
abstract class LC_Block_Widget extends WP_Widget {

	/** @var string blocks/src/{slug}/ に対応するスラッグ */
	protected $block_slug = '';

	/** 属性のデフォルト値（ブロックの block.json と同じ規約） */
	protected function defaults() {
		return array();
	}

	/** render.php のパス */
	protected function render_path() {
		return get_stylesheet_directory() . '/blocks/src/' . $this->block_slug . '/render.php';
	}

	public function widget( $args, $instance ) {
		$attributes = wp_parse_args( (array) $instance, $this->defaults() );
		$render = $this->render_path();
		if ( ! file_exists( $render ) ) { return; }

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		include $render;
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * デフォルト型から型推定して保存値をサニタイズ。
	 * 配列／オブジェクト型はサブクラスで update() をオーバーライドする想定。
	 */
	public function update( $new, $old ) {
		$out = array();
		foreach ( $this->defaults() as $key => $def ) {
			if ( is_bool( $def ) ) {
				$out[ $key ] = ! empty( $new[ $key ] );
			} elseif ( is_int( $def ) ) {
				$out[ $key ] = isset( $new[ $key ] ) ? max( 0, (int) $new[ $key ] ) : $def;
			} elseif ( is_array( $def ) ) {
				$out[ $key ] = isset( $new[ $key ] ) ? (array) $new[ $key ] : $def;
			} else {
				$out[ $key ] = isset( $new[ $key ] ) ? sanitize_text_field( $new[ $key ] ) : $def;
			}
		}
		return $out;
	}
}

/* ============================================================
 * 賃貸物件
 * ============================================================ */
class LC_Widget_Rental_Properties extends LC_Block_Widget {
	protected $block_slug = 'rental-properties';

	public function __construct() {
		parent::__construct(
			'lc_rental_properties',
			'LC 賃貸物件',
			array( 'description' => '賃貸物件をグリッド表示。' )
		);
	}

	protected function defaults() {
		return array(
			'title'        => '賃貸物件',
			'item'         => 4,
			'sort'         => 'date',
			'bukken_cat'   => 0,
			'newup_days'   => 14,
			'buttonText'   => '物件詳細を見る',
			'displayItems' => array( 'title' => true, 'price' => true, 'layout' => true, 'area' => true ),
		);
	}

	public function update( $new, $old ) {
		$out = parent::update( $new, $old );
		$valid_di = array( 'title', 'price', 'layout', 'area' );
		$di = array();
		foreach ( $valid_di as $k ) {
			$di[ $k ] = ! empty( $new['displayItems'][ $k ] );
		}
		$out['displayItems'] = $di;
		return $out;
	}

	public function form( $instance ) {
		$i  = wp_parse_args( (array) $instance, $this->defaults() );
		$di = wp_parse_args( (array) $i['displayItems'], array( 'title' => true, 'price' => true, 'layout' => true, 'area' => true ) );
		$terms = get_terms( array( 'taxonomy' => 'bukken', 'hide_empty' => false ) );
		?>
		<p><label>セクションタイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>表示件数<br>
			<input class="tiny-text" type="number" min="1" max="20" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'item' ) ); ?>"
				value="<?php echo esc_attr( $i['item'] ); ?>" /></label></p>
		<p><label>並び順<br>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'sort' ) ); ?>">
				<option value="date"       <?php selected( $i['sort'], 'date' ); ?>>新着順</option>
				<option value="price_asc"  <?php selected( $i['sort'], 'price_asc' ); ?>>価格が安い順</option>
				<option value="price_desc" <?php selected( $i['sort'], 'price_desc' ); ?>>価格が高い順</option>
				<option value="area_desc"  <?php selected( $i['sort'], 'area_desc' ); ?>>面積が広い順</option>
				<option value="area_asc"   <?php selected( $i['sort'], 'area_asc' ); ?>>面積が狭い順</option>
				<option value="rand"       <?php selected( $i['sort'], 'rand' ); ?>>ランダム</option>
			</select></label></p>
		<p><label>物件カテゴリ<br>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'bukken_cat' ) ); ?>">
				<option value="0" <?php selected( $i['bukken_cat'], 0 ); ?>>すべて</option>
				<?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : foreach ( $terms as $term ) : ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $i['bukken_cat'], $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; endif; ?>
			</select></label></p>
		<p><label>NEW/UP バッジ表示日数（0で非表示）<br>
			<input class="tiny-text" type="number" min="0" max="365" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'newup_days' ) ); ?>"
				value="<?php echo esc_attr( $i['newup_days'] ); ?>" /></label></p>
		<p>表示項目：</p>
		<?php
		$di_opts = array( 'title' => '物件名', 'price' => '価格', 'layout' => '間取り・面積', 'area' => '所在地' );
		foreach ( $di_opts as $key => $label ) :
			$name    = $this->get_field_name( 'displayItems' ) . '[' . $key . ']';
			$checked = ! empty( $di[ $key ] ) ? ' checked="checked"' : '';
			?>
			<p style="margin-left:1em;">
				<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo $checked; // phpcs:ignore ?> /> <?php echo esc_html( $label ); ?></label>
			</p>
		<?php endforeach; ?>
		<p><label>詳細ボタン文言<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'buttonText' ) ); ?>"
				value="<?php echo esc_attr( $i['buttonText'] ); ?>" /></label></p>
		<?php
	}
}

/* ============================================================
 * 売買物件
 * ============================================================ */
class LC_Widget_Sale_Properties extends LC_Block_Widget {
	protected $block_slug = 'sale-properties';

	public function __construct() {
		parent::__construct(
			'lc_sale_properties',
			'LC 売買物件',
			array( 'description' => '売買物件をグリッド表示。' )
		);
	}

	protected function defaults() {
		return array(
			'title'        => '売買物件',
			'item'         => 4,
			'sort'         => 'date',
			'bukken_cat'   => 0,
			'newup_days'   => 14,
			'buttonText'   => '物件詳細を見る',
			'displayItems' => array( 'title' => true, 'price' => true, 'layout' => true, 'area' => true ),
		);
	}

	public function update( $new, $old ) {
		$out = parent::update( $new, $old );
		$valid_di = array( 'title', 'price', 'layout', 'area' );
		$di = array();
		foreach ( $valid_di as $k ) {
			$di[ $k ] = ! empty( $new['displayItems'][ $k ] );
		}
		$out['displayItems'] = $di;
		return $out;
	}

	public function form( $instance ) {
		$i  = wp_parse_args( (array) $instance, $this->defaults() );
		$di = wp_parse_args( (array) $i['displayItems'], array( 'title' => true, 'price' => true, 'layout' => true, 'area' => true ) );
		$terms = get_terms( array( 'taxonomy' => 'bukken', 'hide_empty' => false ) );
		?>
		<p><label>セクションタイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>表示件数<br>
			<input class="tiny-text" type="number" min="1" max="20" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'item' ) ); ?>"
				value="<?php echo esc_attr( $i['item'] ); ?>" /></label></p>
		<p><label>並び順<br>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'sort' ) ); ?>">
				<option value="date"       <?php selected( $i['sort'], 'date' ); ?>>新着順</option>
				<option value="price_asc"  <?php selected( $i['sort'], 'price_asc' ); ?>>価格が安い順</option>
				<option value="price_desc" <?php selected( $i['sort'], 'price_desc' ); ?>>価格が高い順</option>
				<option value="area_desc"  <?php selected( $i['sort'], 'area_desc' ); ?>>面積が広い順</option>
				<option value="area_asc"   <?php selected( $i['sort'], 'area_asc' ); ?>>面積が狭い順</option>
				<option value="rand"       <?php selected( $i['sort'], 'rand' ); ?>>ランダム</option>
			</select></label></p>
		<p><label>物件カテゴリ<br>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'bukken_cat' ) ); ?>">
				<option value="0" <?php selected( $i['bukken_cat'], 0 ); ?>>すべて</option>
				<?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : foreach ( $terms as $term ) : ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $i['bukken_cat'], $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; endif; ?>
			</select></label></p>
		<p><label>NEW/UP バッジ表示日数（0で非表示）<br>
			<input class="tiny-text" type="number" min="0" max="365" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'newup_days' ) ); ?>"
				value="<?php echo esc_attr( $i['newup_days'] ); ?>" /></label></p>
		<p>表示項目：</p>
		<?php
		$di_opts = array( 'title' => '物件名', 'price' => '価格', 'layout' => '間取り・面積', 'area' => '所在地' );
		foreach ( $di_opts as $key => $label ) :
			$name    = $this->get_field_name( 'displayItems' ) . '[' . $key . ']';
			$checked = ! empty( $di[ $key ] ) ? ' checked="checked"' : '';
			?>
			<p style="margin-left:1em;">
				<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"<?php echo $checked; // phpcs:ignore ?> /> <?php echo esc_html( $label ); ?></label>
			</p>
		<?php endforeach; ?>
		<p><label>詳細ボタン文言<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'buttonText' ) ); ?>"
				value="<?php echo esc_attr( $i['buttonText'] ); ?>" /></label></p>
		<?php
	}
}

/* ============================================================
 * 物件検索フォーム
 * ============================================================ */
class LC_Widget_Property_Search extends LC_Block_Widget {
	protected $block_slug = 'property-search';

	public function __construct() {
		parent::__construct(
			'lc_property_search',
			'LC 物件検索フォーム',
			array( 'description' => '物件検索のクイックフォーム。' )
		);
	}

	protected function defaults() {
		return array(
			'title'         => '物件を探す',
			'archiveUrl'    => '',
			'displayFields' => array( 'kbn', 'area', 'mado', 'price' ),
		);
	}

	public function form( $instance ) {
		$i = wp_parse_args( (array) $instance, $this->defaults() );
		$fields = (array) $i['displayFields'];
		?>
		<p><label>タイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>送信先URL（空欄なら物件アーカイブ）<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'archiveUrl' ) ); ?>"
				value="<?php echo esc_attr( $i['archiveUrl'] ); ?>" /></label></p>
		<p>表示フィールド：</p>
		<?php
		$opts = array( 'kbn' => '種別', 'area' => 'エリア', 'mado' => '間取り', 'price' => '価格' );
		foreach ( $opts as $key => $label ) :
			$name    = $this->get_field_name( 'displayFields' ) . '[]';
			$checked = in_array( $key, $fields, true ) ? ' checked="checked"' : '';
			?>
			<p style="margin-left:1em;">
				<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $key ); ?>"<?php echo $checked; // phpcs:ignore ?> /> <?php echo esc_html( $label ); ?></label>
			</p>
		<?php endforeach;
	}

	public function update( $new, $old ) {
		return array(
			'title'         => isset( $new['title'] )      ? sanitize_text_field( $new['title'] )      : '物件を探す',
			'archiveUrl'    => isset( $new['archiveUrl'] ) ? esc_url_raw( $new['archiveUrl'] )         : '',
			'displayFields' => isset( $new['displayFields'] )
				? array_values( array_filter( array_map( 'sanitize_key', (array) $new['displayFields'] ) ) )
				: array(),
		);
	}
}

/* ============================================================
 * 最新情報
 * ============================================================ */
class LC_Widget_News_List extends LC_Block_Widget {
	protected $block_slug = 'news-list';

	public function __construct() {
		parent::__construct(
			'lc_news_list',
			'LC 最新情報',
			array( 'description' => '通常投稿(post)の最新N件を表示。' )
		);
	}

	protected function defaults() {
		return array(
			'title'           => '最新情報',
			'eyebrow'         => 'NEWS / INFORMATION',
			'postsPerPage'    => 5,
			'displayCategory' => true,
			'displayDate'     => true,
		);
	}

	public function form( $instance ) {
		$i = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p><label>タイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>小見出し<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'eyebrow' ) ); ?>"
				value="<?php echo esc_attr( $i['eyebrow'] ); ?>" /></label></p>
		<p><label>表示件数<br>
			<input class="tiny-text" type="number" min="1" max="20" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'postsPerPage' ) ); ?>"
				value="<?php echo esc_attr( $i['postsPerPage'] ); ?>" /></label></p>
		<p><label><input type="checkbox"
				name="<?php echo esc_attr( $this->get_field_name( 'displayCategory' ) ); ?>"
				<?php checked( $i['displayCategory'] ); ?> /> カテゴリを表示</label></p>
		<p><label><input type="checkbox"
				name="<?php echo esc_attr( $this->get_field_name( 'displayDate' ) ); ?>"
				<?php checked( $i['displayDate'] ); ?> /> 日付を表示</label></p>
		<?php
	}
}

/* ============================================================
 * サイドバー：キーワード検索
 * ============================================================ */
class LC_Widget_Sidebar_Search extends LC_Block_Widget {
	protected $block_slug = 'sidebar-search';

	public function __construct() {
		parent::__construct(
			'lc_sidebar_search',
			'LC キーワード検索',
			array( 'description' => '物件のキーワード検索フォーム。' )
		);
	}

	protected function defaults() {
		return array(
			'title'       => 'キーワードで物件を検索',
			'placeholder' => '物件キーワード',
		);
	}

	public function form( $instance ) {
		$i = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p><label>タイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>プレースホルダー<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>"
				value="<?php echo esc_attr( $i['placeholder'] ); ?>" /></label></p>
		<?php
	}
}

/* ============================================================
 * サイドバー：タグクラウド
 * ============================================================ */
class LC_Widget_Sidebar_Tags extends LC_Block_Widget {
	protected $block_slug = 'sidebar-tags';

	public function __construct() {
		parent::__construct(
			'lc_sidebar_tags',
			'LC タグクラウド',
			array( 'description' => '物件投稿タグ (bukken_tag) のタグクラウド。' )
		);
	}

	protected function defaults() {
		return array(
			'title'     => '設備・条件タグ',
			'taxonomy'  => 'bukken_tag',
			'itemLimit' => 20,
		);
	}

	public function form( $instance ) {
		$i = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p><label>タイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>タクソノミー<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>"
				value="<?php echo esc_attr( $i['taxonomy'] ); ?>" /></label></p>
		<p><label>表示件数<br>
			<input class="tiny-text" type="number" min="1" max="100" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'itemLimit' ) ); ?>"
				value="<?php echo esc_attr( $i['itemLimit'] ); ?>" /></label></p>
		<?php
	}
}

/* ============================================================
 * サイドバー：設備・条件タグクラウド (setsubi メタ集計)
 * ============================================================ */
class LC_Widget_Setsubi_Tags extends LC_Block_Widget {
	protected $block_slug = 'setsubi-tags';

	public function __construct() {
		parent::__construct(
			'lc_setsubi_tags',
			'LC 設備タグクラウド',
			array( 'description' => 'fudou プラグインの「設備・条件」(setsubi メタ) を集計したタグクラウド。' )
		);
	}

	protected function defaults() {
		return array(
			'title'     => '設備・条件タグ',
			'itemLimit' => 30,
			'showCount' => false,
		);
	}

	public function form( $instance ) {
		$i = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p><label>タイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>最大表示件数<br>
			<input class="tiny-text" type="number" min="1" max="200" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'itemLimit' ) ); ?>"
				value="<?php echo esc_attr( $i['itemLimit'] ); ?>" /></label></p>
		<p><label><input type="checkbox"
				name="<?php echo esc_attr( $this->get_field_name( 'showCount' ) ); ?>"
				<?php checked( $i['showCount'] ); ?> /> 件数を表示</label></p>
		<?php
	}
}

/* ============================================================
 * サイドバー：エリアから探す
 * ============================================================ */
class LC_Widget_Sidebar_Areas extends LC_Block_Widget {
	protected $block_slug = 'sidebar-areas';

	public function __construct() {
		parent::__construct(
			'lc_sidebar_areas',
			'LC エリアから探す',
			array( 'description' => 'fudo 物件の所在地メタ (shozaichimeisho) を市区町村ごとに集計してエリア一覧表示。' )
		);
	}

	protected function defaults() {
		return array(
			'title'        => 'エリアから探す',
			'prefecture'   => '北海道',
			'limitPerCity' => 30,
			'showCount'    => false,
		);
	}

	public function form( $instance ) {
		$i = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p><label>タイトル<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $i['title'] ); ?>" /></label></p>
		<p><label>都道府県（［地域］見出しに表示。空欄なら市区町村名のみ）<br>
			<input class="widefat" type="text"
				name="<?php echo esc_attr( $this->get_field_name( 'prefecture' ) ); ?>"
				value="<?php echo esc_attr( $i['prefecture'] ); ?>" /></label></p>
		<p><label>1 市区町村あたり最大件数<br>
			<input class="tiny-text" type="number" min="1" max="200" step="1"
				name="<?php echo esc_attr( $this->get_field_name( 'limitPerCity' ) ); ?>"
				value="<?php echo esc_attr( $i['limitPerCity'] ); ?>" /></label></p>
		<p><label><input type="checkbox"
				name="<?php echo esc_attr( $this->get_field_name( 'showCount' ) ); ?>"
				<?php checked( $i['showCount'] ); ?> /> 件数を表示</label></p>
		<?php
	}
}

/* ============================================================
 * 登録
 * ============================================================ */
add_action( 'widgets_init', function () {
	register_widget( 'LC_Widget_Property_Search' );
	register_widget( 'LC_Widget_Rental_Properties' );
	register_widget( 'LC_Widget_Sale_Properties' );
	register_widget( 'LC_Widget_News_List' );
	register_widget( 'LC_Widget_Sidebar_Search' );
	register_widget( 'LC_Widget_Sidebar_Tags' );
	register_widget( 'LC_Widget_Setsubi_Tags' );
	register_widget( 'LC_Widget_Sidebar_Areas' );
} );
