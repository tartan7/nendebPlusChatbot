<?php
/**
 * Lumin Coco — 副読本 (Recommend) 共通機能
 * ============================================================================
 *  1. recommend.css の自動 enqueue（テンプレ使用時のみ）
 *  2. 「副読本」メタボックスの登録（page テンプレ用フィールド）
 *  3. エディタで使えるショートコード
 *      - [rec_section num="①" title="…" en="…"] … [/rec_section]
 *      - [rec_callout type="info|warn|danger|ok" title="…"] … [/rec_callout]
 *      - [rec_qa q="…"] …本文… [/rec_qa]
 *      - [rec_refs] … <li>…</li> 群 … [/rec_refs]
 *      - [rec_ref href="…" date="YYYY-MM-DD" source="…" pdf="1"]…ラベル…[/rec_ref]
 *      - [rec_wak]
 *           [rec_wak_row name="…" summary="…" link="…" date="YYYY-MM-DD"]
 *           ...
 *        [/rec_wak]
 *
 *  使い方:
 *    functions.php の末尾に次の 1 行を追加してください。
 *      require_once get_stylesheet_directory() . '/inc/recommend.php';
 *
 * @package SYN_Ownd_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ============================================================================
 * 1. recommend.css の自動 enqueue（page-recommend-guide.php 使用時のみ）
 * ========================================================================== */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page() ) { return; }
	$tpl = get_page_template_slug();
	$is_rec = ( $tpl === 'page-recommend-guide.php' ) || strpos( (string) $tpl, 'recommend' ) !== false;

	// テンプレ未割当でも /recommend/ 配下ページは対象に
	$slug = is_singular() ? get_post_field( 'post_name', get_the_ID() ) : '';
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	if ( ! $is_rec && ( $slug === 'recommend' || strpos( $uri, '/recommend/' ) === 0 ) ) {
		$is_rec = true;
	}

	if ( ! $is_rec ) { return; }

	wp_enqueue_style(
		'lc-recommend',
		get_stylesheet_directory_uri() . '/assets/css/recommend.css',
		array( 'lc-child-theme' ),
		defined( 'LC_THEME_VERSION' ) ? LC_THEME_VERSION : null
	);
}, 30 );

/* ============================================================================
 * 2. 「副読本」メタボックス
 * ========================================================================== */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'lc_recommend_meta',
		'副読本（ガイド共通設定）',
		'lc_recommend_meta_box',
		'page',
		'normal',
		'high'
	);
} );

function lc_recommend_meta_box( $post ) {
	wp_nonce_field( 'lc_recommend_meta', 'lc_recommend_meta_nonce' );

	$fields = array(
		'_rec_eyebrow'         => array( 'label' => 'ラベル（例 GUIDE · MOVING）', 'type' => 'text' ),
		'_rec_title_en'        => array( 'label' => '英文サブタイトル（例 MOVING IN &amp; SETTLING）', 'type' => 'text' ),
		'_rec_lead'            => array( 'label' => 'リード文（HTML 可：&lt;b&gt; &lt;a&gt; &lt;br&gt;）', 'type' => 'textarea' ),
		'_rec_updated'         => array( 'label' => '最終更新日（YYYY-MM-DD）', 'type' => 'text' ),
		'_rec_read_time'       => array( 'label' => '読了の目安（例 約6分）', 'type' => 'text' ),
		'_rec_related'         => array( 'label' => '関連リンクの HTML（例 &lt;a href="…"&gt;引越し&lt;/a&gt; ／ &lt;a href="…"&gt;トラブル&lt;/a&gt;）', 'type' => 'textarea' ),
		'_rec_prev_url'        => array( 'label' => '前ページ URL', 'type' => 'text' ),
		'_rec_prev_label'      => array( 'label' => '前ページ ラベル', 'type' => 'text' ),
		'_rec_next_url'        => array( 'label' => '次ページ URL', 'type' => 'text' ),
		'_rec_next_label'      => array( 'label' => '次ページ ラベル', 'type' => 'text' ),
		'_rec_back_text'       => array( 'label' => '末尾の戻り文言（HTML 可）', 'type' => 'textarea' ),
		'_rec_back_cta1_label' => array( 'label' => '末尾CTA1 ラベル', 'type' => 'text' ),
		'_rec_back_cta1_url'   => array( 'label' => '末尾CTA1 URL',  'type' => 'text' ),
		'_rec_back_cta2_label' => array( 'label' => '末尾CTA2 ラベル', 'type' => 'text' ),
		'_rec_back_cta2_url'   => array( 'label' => '末尾CTA2 URL',  'type' => 'text' ),
	);

	echo '<div style="display:grid; grid-template-columns:220px 1fr; gap:8px 16px; align-items:start;">';
	foreach ( $fields as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<label for="' . esc_attr( $key ) . '" style="padding-top:6px; color:#08593F; font-weight:600;">' . wp_kses_post( $f['label'] ) . '</label>';
		if ( $f['type'] === 'textarea' ) {
			echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="3" style="width:100%;">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" style="width:100%;" />';
		}
	}
	echo '</div>';
	echo '<p style="margin-top:12px; font-size:12px; color:#6B7287;">※ 本文（エディタ）はショートコード [rec_section] [rec_callout] [rec_qa] [rec_wak] [rec_refs] [rec_ref] が使えます。</p>';
}

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['lc_recommend_meta_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( wp_unslash( $_POST['lc_recommend_meta_nonce'] ), 'lc_recommend_meta' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$text_keys = array(
		'_rec_eyebrow', '_rec_title_en', '_rec_updated', '_rec_read_time',
		'_rec_prev_url', '_rec_prev_label', '_rec_next_url', '_rec_next_label',
		'_rec_back_cta1_label', '_rec_back_cta1_url', '_rec_back_cta2_label', '_rec_back_cta2_url',
	);
	foreach ( $text_keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
		}
	}

	$html_keys = array( '_rec_lead', '_rec_related', '_rec_back_text' );
	$allowed = array(
		'b' => array(), 'strong' => array(), 'em' => array(), 'i' => array(),
		'br' => array(), 'small' => array(),
		'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
	);
	foreach ( $html_keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, wp_kses( wp_unslash( $_POST[ $k ] ), $allowed ) );
		}
	}
}, 10, 1 );

/* ============================================================================
 * 3. ショートコード
 * ========================================================================== */

/* ---- 3-1. セクション ---- */
add_shortcode( 'rec_section', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'num'         => '',
		'title'       => '',
		'en'          => '',
		'id'          => '',          // h2 の id（後方互換）
		'section_id'  => '',          // section の id（アンカーリンク用）
		'extra_class' => '',          // section に追加する CSS クラス
	), $atts, 'rec_section' );

	// section_id 指定時は h2 id を "{section_id}-ttl" に自動生成
	if ( $a['section_id'] ) {
		$sec_id  = $a['section_id'];
		$ttl_id  = $sec_id . '-ttl';
	} else {
		$sec_id  = '';
		$ttl_id  = $a['id'];
	}

	$sec_id_attr = $sec_id ? ' id="' . esc_attr( $sec_id ) . '"' : '';
	$ttl_id_attr = $ttl_id ? ' id="' . esc_attr( $ttl_id ) . '"' : '';
	$extra_cls   = $a['extra_class'] ? ' ' . esc_attr( $a['extra_class'] ) : '';
	$labelled    = $ttl_id ? ' aria-labelledby="' . esc_attr( $ttl_id ) . '"' : '';

	$out  = '<section class="rec-sec' . $extra_cls . '"' . $sec_id_attr . $labelled . '>';
	// num と title が両方空のときはヘッダーを省略（用語集の用語本体セクション等）
	if ( $a['num'] !== '' || $a['title'] !== '' ) {
		$out .= '<header class="rec-sec__head">';
		if ( $a['num'] !== '' ) {
			$out .= '<span class="rec-sec__num" aria-hidden="true">' . esc_html( $a['num'] ) . '</span>';
		}
		$out .= '<h2 class="rec-sec__ttl"' . $ttl_id_attr . '>';
		$out .= esc_html( $a['title'] );
		if ( $a['en'] ) {
			$out .= '<small>' . esc_html( $a['en'] ) . '</small>';
		}
		$out .= '</h2></header>';
	}
	// $content は the_content() の wpautop 処理済み（Classic）or ブロックエディタ由来
	// 二重 wpautop で生じる空 <p> と、ショートコード直前の <br> を除去してから処理
	$body = preg_replace( '/<p>(\s|&nbsp;)*<\/p>/i', '', $content );
	$body = preg_replace( '/(<br\s*\/?>)+(\s*\[)/i', '$2', $body );
	$out .= do_shortcode( $body );
	$out .= '</section>';
	return $out;
} );

/* ---- 3-1b. カテゴリ編集メモ（Resources ページ用） ---- */
add_shortcode( 'rec_cat_note', function ( $atts, $content = '' ) {
	$body = preg_replace( '/<p>(\s|&nbsp;)*<\/p>/i', '', $content );
	return '<div class="rec-cat__note">' . do_shortcode( $body ) . '</div>';
} );

/* ---- 3-1c. 50音インデックスナビ（Glossary ページ用） ---- */
add_shortcode( 'rec_az', function ( $atts ) {
	$a = shortcode_atts( array(
		'empty' => '',  // 無効にする読み（カンマ区切り）例: "な,わ"
	), $atts, 'rec_az' );

	$empty_list = array_filter( array_map( 'trim', explode( ',', $a['empty'] ) ) );

	$kana_map = array(
		'あ' => 'kana-a',  'か' => 'kana-ka', 'さ' => 'kana-sa',
		'た' => 'kana-ta', 'な' => 'kana-na', 'は' => 'kana-ha',
		'ま' => 'kana-ma', 'や' => 'kana-ya', 'ら' => 'kana-ra', 'わ' => 'kana-wa',
	);

	$out = '<nav class="rec-az" aria-label="50音インデックス"><span class="rec-az__label">索引</span>';
	foreach ( $kana_map as $kana => $id ) {
		if ( in_array( $kana, $empty_list, true ) ) {
			$out .= '<a href="#' . esc_attr( $id ) . '" class="is-empty" aria-disabled="true">' . esc_html( $kana ) . '</a>';
		} else {
			$out .= '<a href="#' . esc_attr( $id ) . '">' . esc_html( $kana ) . '</a>';
		}
	}
	$out .= '</nav>';
	return $out;
} );

/* ---- 3-2. Callout ---- */
add_shortcode( 'rec_callout', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'type'  => 'info',  // info | warn | danger | ok
		'title' => '',
	), $atts, 'rec_callout' );

	$type  = in_array( $a['type'], array( 'info', 'warn', 'danger', 'ok' ), true ) ? $a['type'] : 'info';
	$icons = array( 'info' => 'i', 'warn' => '!', 'danger' => '!', 'ok' => '✓' );
	$icon  = $icons[ $type ];

	$out  = '<div class="rec-callout rec-callout--' . esc_attr( $type ) . '" role="note">';
	$out .= '<span class="rec-callout__icon" aria-hidden="true">' . esc_html( $icon ) . '</span>';
	$out .= '<div class="rec-callout__body">';
	if ( $a['title'] ) {
		$out .= '<b class="rec-callout__ttl">' . esc_html( $a['title'] ) . '</b>';
	}
	$body = preg_replace( '/<p>(\s|&nbsp;)*<\/p>/i', '', $content );
	$out .= do_shortcode( $body );
	$out .= '</div></div>';
	return $out;
} );

/* ---- 3-3. Q&A（<details>） ---- */
add_shortcode( 'rec_qa', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'q'    => '質問',
		'open' => '',
	), $atts, 'rec_qa' );

	$open = ( $a['open'] === '1' || $a['open'] === 'true' ) ? ' open' : '';
	$out  = '<details class="rec-qa"' . $open . '>';
	$out .= '<summary>' . esc_html( $a['q'] ) . '</summary>';
	$out .= '<div class="rec-qa__body">' . do_shortcode( wpautop( $content ) ) . '</div>';
	$out .= '</details>';
	return $out;
} );

/* ---- 3-4. 参照リンク集（外側） ---- */
add_shortcode( 'rec_refs', function ( $atts, $content = '' ) {
	return '<ul class="rec-refs" role="list">' . do_shortcode( $content ) . '</ul>';
} );

/* ---- 3-5. 参照リンク（1行） ---- */
add_shortcode( 'rec_ref', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'href'   => '#',
		'date'   => '',
		'source' => '',
		'pdf'    => '',
	), $atts, 'rec_ref' );

	$is_pdf  = in_array( strtolower( (string) $a['pdf'] ), array( '1', 'true', 'yes' ), true );
	$label   = trim( strip_tags( $content ) );
	$label   = $label !== '' ? $label : $a['href'];

	$out  = '<li>';
	$out .= '<a href="' . esc_url( $a['href'] ) . '" target="_blank" rel="noopener">';
	$out .= esc_html( $label );
	$out .= ' <span class="ext-mark" aria-label="外部リンク">↗</span>';
	if ( $is_pdf ) {
		$out .= ' <span class="rec-refs__tag is-pdf">PDF</span>';
	}
	$out .= '</a>';
	if ( $a['source'] || $a['date'] ) {
		$out .= '<span class="rec-refs__meta">';
		if ( $a['source'] ) { $out .= esc_html( $a['source'] ); }
		if ( $a['source'] && $a['date'] ) { $out .= '　／　'; }
		if ( $a['date'] ) {
			$out .= '最終確認 <time datetime="' . esc_attr( $a['date'] ) . '">' . esc_html( $a['date'] ) . '</time>';
		}
		$out .= '</span>';
	}
	$out .= '</li>';
	return $out;
} );

/* ---- 3-6. 稚内ブロック表（外側） ---- */
add_shortcode( 'rec_wak', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'caption' => '行政・ライフライン・ごみ等の早見表。最新の案内は各公式サイトをご確認ください。',
	), $atts, 'rec_wak' );

	$id = 'rec-wak-' . wp_generate_uuid4();
	$out  = '<table class="rec-wak-tbl" aria-describedby="' . esc_attr( $id ) . '">';
	$out .= '<caption id="' . esc_attr( $id ) . '">' . esc_html( $a['caption'] ) . '</caption>';
	$out .= '<thead><tr>';
	$out .= '<th scope="col" class="col-name">名称</th>';
	$out .= '<th scope="col" class="col-summary">概要・主な手続き</th>';
	$out .= '<th scope="col" class="col-link">公式リンク</th>';
	$out .= '<th scope="col" class="col-check">確認日</th>';
	$out .= '</tr></thead><tbody>';
	// content は [rec_wak_row] の連続を想定。wpautop が挿入した <br> を除去
	$out .= do_shortcode( preg_replace( '/<br\s*\/?>/', '', $content ) );
	$out .= '</tbody></table>';
	return $out;
} );

/* ---- 3-7. 稚内ブロック表（行） ---- */
add_shortcode( 'rec_wak_row', function ( $atts ) {
	$a = shortcode_atts( array(
		'name'    => '',
		'summary' => '',
		'link'    => '',
		'label'   => '',  // リンクの表示テキスト（省略時はホスト名表示）
		'date'    => '',
	), $atts, 'rec_wak_row' );

	$link_html = '—';
	if ( $a['link'] ) {
		$label = $a['label'] ? $a['label'] : wp_parse_url( $a['link'], PHP_URL_HOST );
		$link_html = '<a href="' . esc_url( $a['link'] ) . '" target="_blank" rel="noopener">'
			. esc_html( $label )
			. '<span class="ext-icon" aria-label="外部リンク">↗</span></a>';
	}

	$date_html = '—';
	if ( $a['date'] ) {
		$date_html = '<time datetime="' . esc_attr( $a['date'] ) . '">' . esc_html( $a['date'] ) . '</time>';
	}

	$out  = '<tr>';
	$out .= '<td class="col-name"    data-label="名称">'     . esc_html( $a['name'] )    . '</td>';
	$out .= '<td class="col-summary" data-label="概要">'     . esc_html( $a['summary'] ) . '</td>';
	$out .= '<td class="col-link"    data-label="公式リンク">' . $link_html              . '</td>';
	$out .= '<td class="col-check"   data-label="確認日">'   . $date_html               . '</td>';
	$out .= '</tr>';
	return $out;
} );

/* ---- 3-8. タイムライン（外側） ---- */
add_shortcode( 'rec_timeline', function ( $atts, $content = '' ) {
	return '<ol class="rec-timeline" role="list">' . do_shortcode( preg_replace( '/<br\s*\/?>/', '', $content ) ) . '</ol>';
} );

/* ---- 3-9. タイムライン（1行） ---- */
add_shortcode( 'rec_timeline_item', function ( $atts ) {
	$a = shortcode_atts( array(
		'when' => '',
		'what' => '',
		'note' => '',
	), $atts, 'rec_timeline_item' );

	$out  = '<li>';
	$out .= '<span class="when">' . esc_html( $a['when'] ) . '</span>';
	$out .= '<span class="what">' . wp_kses( $a['what'], array( 'b' => array(), 'strong' => array() ) ) . '</span>';
	if ( $a['note'] ) {
		$out .= '<span class="note">' . esc_html( $a['note'] ) . '</span>';
	}
	$out .= '</li>';
	return $out;
} );

/* ---- 3-10. フローリスト（外側） ---- */
add_shortcode( 'rec_flow', function ( $atts, $content = '' ) {
	return '<ol class="rec-flow" role="list">' . do_shortcode( preg_replace( '/<br\s*\/?>/', '', $content ) ) . '</ol>';
} );

/* ---- 3-11. フローリスト（1行） ---- */
add_shortcode( 'rec_flow_item', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'title' => '',
		'desc'  => '',
	), $atts, 'rec_flow_item' );

	$allowed = array( 'b' => array(), 'strong' => array(), 'a' => array( 'href' => array() ) );
	$desc_html = $a['desc'] ? esc_html( $a['desc'] )
	                        : wp_kses( trim( $content ), $allowed );
	$out  = '<li><div>';
	$out .= '<strong>' . esc_html( $a['title'] ) . '</strong>';
	$out .= '<span>' . $desc_html . '</span>';
	$out .= '</div></li>';
	return $out;
} );

/* ---- 3-12. チェックリスト（外側） ---- */
add_shortcode( 'rec_check', function ( $atts, $content = '' ) {
	return '<ul class="rec-check" role="list">' . do_shortcode( preg_replace( '/<br\s*\/?>/', '', $content ) ) . '</ul>';
} );

/* ---- 3-13. チェックリスト（1行） ---- */
add_shortcode( 'rec_check_item', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'bold' => '',
	), $atts, 'rec_check_item' );

	$allowed = array( 'b' => array(), 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) );
	$inner = $a['bold'] ? '<b>' . esc_html( $a['bold'] ) . '</b>　' : '';
	$inner .= wp_kses( trim( $content ), $allowed );
	return '<li><span>' . $inner . '</span></li>';
} );

/* ---- 3-14. トラブルリスト（外側） ---- */
add_shortcode( 'rec_trouble_list', function ( $atts, $content = '' ) {
	return '<ul class="rec-trouble-list" role="list">' . do_shortcode( preg_replace( '/<br\s*\/?>/', '', $content ) ) . '</ul>';
} );

/* ---- 3-15. トラブルリスト（1件） ---- */
add_shortcode( 'rec_trouble_item', function ( $atts, $content = '' ) {
	$a = shortcode_atts( array(
		'title'  => '',
		'points' => '',
		'where'  => '',
	), $atts, 'rec_trouble_item' );

	$out  = '<li>';
	$out .= '<h4>' . esc_html( $a['title'] ) . '</h4>';
	if ( $content ) {
		$out .= '<p>' . esc_html( trim( strip_tags( $content ) ) ) . '</p>';
	}
	if ( $a['points'] ) {
		$out .= '<p class="points"><b>論点</b>' . esc_html( $a['points'] ) . '</p>';
	}
	if ( $a['where'] ) {
		$out .= '<p class="where"><b>主な相談先</b>：' . esc_html( $a['where'] ) . '</p>';
	}
	$out .= '</li>';
	return $out;
} );

/* ---- 3-16. カテゴリ注釈ボックス（Resources 用） ---- */
add_shortcode( 'rec_cat_note', function ( $atts, $content = '' ) {
	$out  = '<div class="rec-cat__note">';
	$out .= do_shortcode( wpautop( $content ) );
	$out .= '</div>';
	return $out;
} );
