# SYN-Ownd Child Theme — 株式会社ルーミン・ココ

北海道稚内市の不動産会社「ルーミン・ココ」向け SYN-Ownd 子テーマ。
親テーマ **syn-ownd** ＋ 不動産プラグイン **fudou** ＋ Dify チャットボット に最適化した、緑基調・赤 CTA の和文タイポデザイン。

## 動作要件

| 項目 | バージョン |
| --- | --- |
| WordPress | 6.0 以上 |
| PHP | 7.4 以上（8.x 推奨） |
| 親テーマ | **SYN Ownd**（株式会社ウェブライダー） |
| プラグイン（推奨） | **不動産プラグイン (fudou)** — nendeb.jp |
| プラグイン（任意） | Dify チャットボット (`udify.app` または自社 Dify ホスト) |

## インストール

1. 親テーマ **SYN Ownd** を `wp-content/themes/syn-ownd/` に配置し、有効化。
2. このフォルダを `wp-content/themes/syn-ownd-child/` にアップロード。
3. WordPress 管理画面 **外観 > テーマ** で _SYN Ownd Child – Lumin Coco_ を有効化。
4. **外観 > メニュー** で `グローバルナビ（ヘッダー）` メニューを作成し、`primary` の位置に割り当て。URL に "contact" を含む項目は自動的に赤い CTA ボタンになります。
5. **外観 > ウィジェット** で以下のエリアを設定：

   **トップページ (ホーム本文)**（ウィジェットID: `top_widgets`）に順番に配置：
   1. **LC 物件検索フォーム**
   2. **LC 最新情報**
   3. **LC 賃貸物件**
   4. **LC 売買物件**

   **サイドバー (物件検索)**（ウィジェットID: `sidebar-widget`）に順番に配置：
   1. **LC キーワード検索**
   2. **LC エリアから探す**
   3. **LC タグクラウド**

   **フッターナビゲーション：左 / 中央 / 右**（`ftrnav-widget-{left,center,right}`）にメニューや HTML を配置。

6. **Dify チャットボット**を使う場合は `wp-config.php` に以下を追記：

```php
define( 'LC_DIFY_TOKEN', 'XXXXXXXX' );             // Dify App Token
define( 'LC_DIFY_BASE_URL', 'https://udify.app' ); // 自社ホストならその URL
```

## ファイル構成

```
syn-ownd-child/
├── style.css                       # テーマヘッダー＋CSS変数の上書き
├── theme.json                      # FSE 用カラーパレット
├── functions.php                   # CSS/JS 読み込み・ヘルパー関数・ウィジェット登録
├── screenshot.png                  # 外観 > テーマ 用プレビュー
│
├── header.php                      # ヘッダー（ロゴ＋グロナビ）
├── footer.php                      # フッター（3列＋チャット FAB）
├── front-page.php                  # ホーム（Hero＋top_widgets）
├── archive-fudo.php                # 物件一覧（絞り込み＋フィルタ）
├── single-fudo.php                 # 物件詳細
├── archive.php                     # 通常記事のアーカイブ
├── single.php                      # 通常記事
├── page.php                        # 固定ページ（汎用）
├── page-aboutus.php                # 会社概要ページ
├── page-recommend-hub.php          # おすすめコンテンツ一覧ページ
├── page-recommend-guide.php        # おすすめガイドページ
├── 404.php
├── index.php                       # フォールバック
├── searchform.php
├── sidebar.php                     # sidebar-widget を出力
│
├── inc/
│   ├── class-lc-widgets.php        # WP_Widget ラッパー（7ブロック分）
│   ├── recommend.php               # おすすめコンテンツのヘルパー
│   └── legacy-redirects.php        # 旧URL → 新URL リダイレクト
│
├── blocks/
│   ├── src/
│   │   ├── rental-properties/      # 賃貸物件ブロック
│   │   ├── sale-properties/        # 売買物件ブロック
│   │   ├── property-search/        # 物件検索フォームブロック
│   │   ├── news-list/              # 最新情報ブロック
│   │   ├── sidebar-areas/          # エリアから探すブロック
│   │   ├── sidebar-search/         # キーワード検索ブロック
│   │   ├── sidebar-tags/           # タグクラウドブロック
│   │   └── setsubi-tags/           # 設備タグブロック
│   └── css/                        # ブロック別CSS（自動エンキュー）
│
├── template-parts/
│   └── recommend/                  # おすすめ記事用パーツ
│
├── assets/
│   ├── css/
│   │   ├── theme.css               # レイアウト／コンポーネント CSS
│   │   └── recommend.css           # おすすめコンテンツ CSS
│   ├── js/
│   │   ├── chat-fab.js             # Dify チャットボット起動 FAB
│   │   ├── nav.js                  # ナビゲーション
│   │   ├── fudo-filter.js          # 物件絞り込み
│   │   ├── fudo-map.js             # 物件地図連携
│   │   └── recommend-tweaks.js     # おすすめコンテンツ UI
│   └── images/
│       ├── logo/                   # ロゴ・ワードマーク・シルエット
│       └── about/                  # 会社概要ページ用画像
│
└── _preview/                       # HTML プレビュー（WordPress では使われません）
```

## カスタムブロック／ウィジェット

全ブロックは **Gutenberg ブロック** と **WP_Widget** の両形式に対応しています。
[classic-widgets プラグイン](https://wordpress.org/plugins/classic-widgets/) が有効な環境でも、管理画面から「LC 〜」という名称でドラッグ配置できます。

| ウィジェット名 | ブロック名 | 主な設定項目 |
| --- | --- | --- |
| LC 物件検索フォーム | `lc-property-search` | 表示フィールド、エリアリスト |
| LC 賃貸物件 | `lc-rental-properties` | タイトル、表示件数、並び順、ボタン文言 |
| LC 売買物件 | `lc-sale-properties` | タイトル、表示件数、並び順、ボタン文言 |
| LC 最新情報 | `lc-news-list` | タイトル、表示件数、カテゴリ表示 |
| LC エリアから探す | `lc-sidebar-areas` | タイトル、タクソノミー、最大件数 |
| LC キーワード検索 | `lc-sidebar-search` | — |
| LC タグクラウド | `lc-sidebar-tags` | — |

> **仕組み**：`inc/class-lc-widgets.php` の各 WP_Widget が、対応する `blocks/src/{slug}/render.php` を include して表示。ブロック側の修正がウィジェット側にも自動反映されます。

## ウィジェットエリア一覧

| ID | 名称 | 表示位置 |
| --- | --- | --- |
| `top_widgets` | トップページ (ホーム本文) | `front-page.php` 本文 |
| `sidebar-widget` | サイドバー (物件検索) | 物件一覧・詳細の右カラム |
| `ftrnav-widget-left` | フッターナビゲーション：左 | フッター左列 |
| `ftrnav-widget-center` | フッターナビゲーション：中央 | フッター中列 |
| `ftrnav-widget-right` | フッターナビゲーション：右 | フッター右列 |
| `front-before-widget` | — | ホーム Hero 直後 |
| `front-after-widget` | — | ホーム フッター直前 |
| `archive-before-widget` | — | 物件一覧 パンくず前 |
| `archive-after-widget` | — | 物件一覧 ページネーション後 |
| `syousai_widgets` | 物件詳細ページ (fudou) | 物件詳細サイドバー |
| `sidebar-fixed-widget` | 追従サイドバー | スクロール追従エリア |
| `ftr-head-widget` | フッター上部 | フッター上スライダー |

> `top_widgets` / `sidebar-widget` / `ftrnav-widget-*` は子テーマで `.lc-widget` マークアップに再登録済みです。

## カスタマイザーで上書きできる項目

`外観 > カスタマイズ` から以下を上書き可能：

| theme_mod キー | 既定値 | 用途 |
| --- | --- | --- |
| `lc_hero_eyebrow` | `WAKKANAI REAL ESTATE` | ヒーロー上のラベル |
| `lc_hero_title` | `「部屋を選ぶ」＝…` | ヒーロー見出し（`<br>` `<em>` 可） |
| `lc_hero_sub` | （長文） | ヒーロー本文 |
| `lc_hero_cta_label` | `物件を探す` | ヒーロー CTA ラベル |
| `lc_hero_cta_url` | 物件アーカイブ | ヒーロー CTA リンク |
| `lc_hero_image_url` | Unsplash 画像 | ヒーロー背景画像 URL |
| `lc_brand_en_strong` | `ROOM IN KOKO` | ヘッダロゴ脇の英字 |
| `lc_brand_en_sub` | `WAKKANAI · HOKKAIDO` | ヘッダロゴ脇のサブ |
| `lc_company_address` | （住所） | フッター会社情報 |
| `lc_company_license` | 宅建免許番号 | フッター下部 |
| `lc_company_tel` | `0162-32-8877` | 物件詳細サイドの電話 |
| `lc_company_tel_note` | `受付 9:00-18:00…` | 受付時間注記 |
| `lc_chat_label` | `物件のことAIに相談` | チャット吹き出し |
| `lc_chat_fallback_url` | `/contact/` | Dify 未設定時の遷移先 |

## 物件検索パラメータ仕様

`archive-fudo.php` への GET パラメータ：

| パラメータ | 型 | 例 | 動作 |
| --- | --- | --- | --- |
| `kbn` | string | `rent` / `sale` | 種別フィルタ |
| `area[]` | array | `area[]=naka` | エリア複数選択（OR） |
| `mado[]` | array | `mado[]=1LDK` | 間取り複数選択（OR） |
| `price_min` | number | `3` | 価格下限（万円） |
| `price_max` | number | `8` | 価格上限（万円） |
| `tag` | string | `pet-ok,parking` | タグ（カンマ区切り、AND） |
| `orderby` | string | `date` / `price_asc` / `price_desc` | 並び替え |
| `s` | string | `稚内` | キーワード |
| `paged` | number | `2` | ページ番号 |

## 不動産プラグイン (fudou) 連携

- アーカイブ（`archive-fudo.php`）と詳細（`single-fudo.php`）はプラグイン側の DOM（`#list_simplepage` / `#list_simplepage2` / `.hentry` / `.list_simple_box` / `.list_detail` / `#list_add` / `#list_other`）をそのまま使い、子テーマ側で**視覚だけ**刷新しています。
- 物件メタの参照キーは複数候補に順次フォールバック（`lc_get_fudo_meta` 参照）：
  - 価格: `kakaku` → `price` → `bukken_kakaku`
  - 間取り: `madori` → `layout`
  - 面積: `menseki` → `area` → `senyu_menseki`
  - 所在地: `shozaichi` → `address` → `jusho`
  - 交通: `koutsu` → `access`

## カスタムテンプレ DOM の維持

不動産プラグインが出力する物件詳細ページの DOM は以下を維持：

```html
#list_simplepage2
  .list_simple_box
    .list_detail
      .twocol
        table#list_add   (所在地・交通・間取り・面積・築年・構造・方位)
        table#list_other (賃料・管理費・敷礼・駐車場・取引態様・入居可)
#toiawasesaki            (問い合わせサイドバー)
```

物件一覧側も `.hentry > .list_simple_boxtitle + .list_simple_box > .list_picsam_img / .bukken-meta / .bukken-addr / a > .list_details_button` を踏襲。

## ブランドカラー

| 役割 | 色 | 用途 |
| --- | --- | --- |
| メイングリーン | `#0F825D` | ヘッダ／見出し下線／詳細ボタン |
| ダークグリーン | `#08593F` | 本文テキスト／フッター |
| アクセント赤 | `#D63C26` | CTA／お問い合わせ／価格・NEW バッジ |
| アクセント黄 | `#E6B028` | おすすめ・UP バッジ |
| 背景 | `#F4F7F1` | ページ背景 |
| ライトグリーン | `#E3F3EC` | テーブルヘッダ・タグチップ |

## カスタマイズの始め方

1. **配色のみ変更** — `style.css` 冒頭の `:root{ --color-main: ... }` を差し替えるだけ。
2. **見出しフォントを Sans に揃える** — `style.css` の `--font-display` を Noto Sans JP 等に変更。
3. **物件カードを 3 列に** — `assets/css/theme.css` の `#list_simplepage` の `--lc-grid-cols` を `3` に上書き。
4. **チャット FAB を消す** — `footer.php` から `.lc-chat-dock` ブロックを削除、または `LC_DIFY_TOKEN` を空のまま運用。

## ライセンス

GPL v2 or later — 親テーマ SYN Ownd（GPL）に準拠。
