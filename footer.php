<?php
/**
 * Footer — Lumin Coco
 *
 * @package SYN_Ownd_Child
 */
$company_postal  = get_theme_mod( 'lc_company_postal',  '097-0017' );
$company_address = get_theme_mod( 'lc_company_address', '北海道稚内市栄5丁目7番5号 コーポ サンロード1F' );
$company_tel     = get_theme_mod( 'lc_company_tel',     '0162-32-8877' );
$company_lic     = get_theme_mod( 'lc_company_license', '宅地建物取引業免許　北海道知事 宗谷(4) 第53号' );
$copyright      = get_theme_mod( 'synx_hdrftr_copyright', sprintf( '© %s %s', date( 'Y' ), get_bloginfo( 'name' ) ) );
$contact_url    = get_theme_mod( 'lc_chat_fallback_url', home_url( '/contact/' ) );
?>

<footer class="lc-ftr" role="contentinfo">

	<?php
	$silhouette = get_stylesheet_directory() . '/assets/images/logo/room-in-koko-silhouette-white.png';
	if ( file_exists( $silhouette ) ) :
	?>
		<div class="lc-ftr__art" aria-hidden="true">
			<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/room-in-koko-silhouette-white.png' ); ?>" alt="" />
		</div>
	<?php endif; ?>

	<div class="lc-ftr__inner">

		<div class="lc-ftr__brand">
			<?php
			$custom_logo = get_theme_mod( 'lc_company_footer_logo', '' );
			$wm_fallback = get_stylesheet_directory() . '/assets/images/logo/lumin-coco-wordmark-white.png';
			if ( $custom_logo ) {
				printf(
					'<img class="lc-ftr__brand-mark" src="%s" alt="%s" />',
					esc_url( $custom_logo ),
					esc_attr( get_bloginfo( 'name', 'display' ) )
				);
			} elseif ( file_exists( $wm_fallback ) ) {
				printf(
					'<img class="lc-ftr__brand-mark" src="%s" alt="%s" />',
					esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/lumin-coco-wordmark-white.png' ),
					esc_attr( get_bloginfo( 'name', 'display' ) )
				);
			} else {
				printf( '<h3>%s</h3>', esc_html( get_bloginfo( 'name', 'display' ) ) );
			}
			?>
			<p>
				<?php if ( $company_postal ) : ?>〒<?php echo esc_html( $company_postal ); ?><br /><?php endif; ?>
				<?php if ( $company_address ) : ?><?php echo esc_html( $company_address ); ?><br /><?php endif; ?>
				<?php if ( $company_tel ) : ?>TEL：<?php echo esc_html( $company_tel ); ?><?php endif; ?>
			</p>
			<?php if ( $company_lic ) : ?>
				<p style="font-size:11px; opacity:.7;"><?php echo esc_html( $company_lic ); ?></p>
			<?php endif; ?>
		</div>

		<?php
		// ３列のフッターウィジェット — 子テーマ独自ID lc-ftr-col-{1,2,3} を使用
		// エリア列にはスラッシュ区切りインライン表示用のモディファイア class を追加
		$cols = array(
			'lc-ftr-col-1' => array( 'title' => 'サービス', 'mod' => '' ),
			'lc-ftr-col-2' => array( 'title' => '会社情報', 'mod' => '' ),
			'lc-ftr-col-3' => array( 'title' => 'エリア',   'mod' => 'lc-ftr__col--areas' ),
		);
		foreach ( $cols as $sidebar_id => $info ) :
			$col_class = trim( 'lc-ftr__col ' . $info['mod'] );
			?>
			<div class="<?php echo esc_attr( $col_class ); ?>">
				<?php
				if ( is_active_sidebar( $sidebar_id ) ) {
					dynamic_sidebar( $sidebar_id );
				} else {
					// フォールバック（中身は管理画面で差し替え可能）
					echo '<h4>' . esc_html( $info['title'] ) . '</h4>';
					echo '<ul>';
					if ( $sidebar_id === 'lc-ftr-col-1' && post_type_exists( 'fudo' ) ) {
						$archive = get_post_type_archive_link( 'fudo' );
						echo '<li><a href="' . esc_url( add_query_arg( 'kbn', 'rent', $archive ) ) . '">賃貸物件検索</a></li>';
						echo '<li><a href="' . esc_url( add_query_arg( 'kbn', 'sale', $archive ) ) . '">売買物件検索</a></li>';
					}
					echo '<li><a href="' . esc_url( $contact_url ) . '">お問い合わせ</a></li>';
					echo '</ul>';
				}
				?>
			</div>
		<?php endforeach; ?>

	</div>

	<div class="lc-ftr__bottom">
		<span class="lc-ftr__license"><?php echo wp_kses_post( $copyright ); ?></span>
		<span>Powered by SYN Ownd × 不動産プラグイン</span>
	</div>
</footer>

<?php /* Dify チャット起動 FAB（functions.php で Dify embed を出力。トークン未設定なら fallback URL に遷移）*/ ?>
<div class="lc-chat-dock" id="lcChatDock" data-fallback-url="<?php echo esc_url( $contact_url ); ?>">
	<button class="lc-chat-fab__label" id="lcChatLabel" type="button">
		<?php echo esc_html( get_theme_mod( 'lc_chat_label', '物件のことAIに相談' ) ); ?>
		<span class="lc-chat-fab__label-close" id="lcChatLabelClose" aria-label="閉じる">×</span>
	</button>
	<button class="lc-chat-fab" id="lcChatFab" type="button" aria-label="チャットで相談">
		<span class="lc-chat-fab__pulse"></span>
		<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
		</svg>
	</button>
</div>

<?php /* オリジナルチャットモーダル */ ?>
<div class="lc-chatmodal" id="lcChatModal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'AI不動産相談', 'syn-ownd-child' ); ?>" hidden>
	<div class="lc-chatmodal__overlay" id="lcChatModalOverlay"></div>
	<div class="lc-chatmodal__panel">
		<div class="lc-chatmodal__head">
			<div class="lc-chatmodal__head-info">
				<svg class="lc-chatmodal__head-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
				<div>
					<strong><?php esc_html_e( 'AI不動産相談', 'syn-ownd-child' ); ?></strong>
					<span><?php esc_html_e( '稚内市の物件について気軽にどうぞ', 'syn-ownd-child' ); ?></span>
				</div>
			</div>
			<button class="lc-chatmodal__close" id="lcChatModalClose" aria-label="<?php esc_attr_e( '閉じる', 'syn-ownd-child' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<div class="lc-chatmodal__messages" id="lcChatMessages">
			<div class="lc-chatmsg lc-chatmsg--ai">
				<div class="lc-chatmsg__bubble"><?php esc_html_e( 'こんにちは！稚内市の賃貸・売買物件についてお気軽にご相談ください。', 'syn-ownd-child' ); ?></div>
			</div>
		</div>
		<div class="lc-chatmodal__foot">
			<textarea class="lc-chatmodal__input" id="lcChatInput" placeholder="<?php esc_attr_e( 'メッセージを入力… (Enterで送信)', 'syn-ownd-child' ); ?>" rows="2"></textarea>
			<button class="lc-chatmodal__send" id="lcChatSend" aria-label="<?php esc_attr_e( '送信', 'syn-ownd-child' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
			</button>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
