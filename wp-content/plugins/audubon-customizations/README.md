# Studio Audubon Customizations

Studio Audubonサイト（https://studio-audubon.jp/）のWordPressカスタマイズプラグインです。
要件に対応する形で、以下を **管理画面から編集可能** にします。

## 機能一覧

### 1. アクターページ
- カスタム投稿タイプ **「アクター」** を追加
- 編集画面で次の項目を入力可能：
  - **プロフィールPDF**: メディアライブラリからPDFを選択
  - **最新の出演作品**: 自動取得（推奨）または手動指定
- アクター詳細ページに以下のリンクが自動表示されます：
  - **最新の出演作品**（Information記事へのリンク）
  - **プロフィールPDFダウンロード**ボタン

### 2. Information（旧News）
- カスタム投稿タイプ **「Information」** を追加（`/information/`）
- **放映日時**フィールド: 投稿の日付ではなく、任意の日時を入力可能（円企画スタイル）
- **表示ラベル**フィールド: 「初回放送」「公開日」「上演」など自由記述
- **出演アクター**フィールド: アクターを複数選択でき、アクターページとリンク連動
- 一覧ページは放映日時の新しい順に並びます

### 3. スライド（既存`slides` CPTの体裁変更）
- 既存のカスタム投稿タイプ `slides`（テーマ提供）に **新しいメタフィールドを追加** します（CPTは新規登録しません）
  - **ポスター下に表示するテキスト**（例:「山田太郎 出演」）
  - **リンク先URL**（任意）
- ショートコード `[audubon_slides]` で配置
  - 3枚並びで自動スライド（ホバーで一時停止、prev/nextボタンつき）
  - アイキャッチをA4比率（210:297）で表示、下にテキスト
  - レスポンシブ対応（タブレット2列、スマホ1列）
  - `prefers-reduced-motion` 設定時は自動スライド停止
- **既存テーマのスライド表示には影響しません。** 新しい体裁を使いたい箇所にのみショートコードを貼ってください。

### 4. Works（作品一覧）
- カスタム投稿タイプ **「Works」** を追加
- バナーと同じくA4ポスターベース、ただし **静的な一覧表示**（自動移動なし）
- ショートコード `[audubon_works columns="3"]`
- カラム数（2/3/4）を切り替え可能

## ショートコード

| ショートコード | 説明 |
|---|---|
| `[audubon_slides]` | 既存`slides` CPTを使ったA4ポスター3分割の自動スライダー |
| `[audubon_works columns="3"]` | A4ポスターベースの静的Works一覧 |
| `[audubon_actor_links]` | アクターページの「最新出演 / PDF」リンクブロック |
| `[audubon_information_date]` | Information記事の放映日時（テンプレートタグ的な使い方） |

## 設置方法

1. このディレクトリ全体を `/wp-content/plugins/audubon-customizations/` にアップロード
2. 管理画面の **プラグイン > インストール済みプラグイン** から有効化
3. **設定 > パーマリンク設定** を開いて「変更を保存」（リライトルール再生成のため）
4. 必要なページに上記ショートコードを貼り付け

## カスタマイズ用テンプレートタグ（テーマファイルから使う）

```php
// アクターページ単体テンプレートで
echo do_shortcode( '[audubon_actor_links]' );

// または個別呼び出し
$pdf_url = audubon_get_actor_profile_pdf_url();
$info    = audubon_get_actor_latest_information();

// Information一覧で
echo audubon_get_information_display_date();   // 放映日時（無ければ投稿日）
echo audubon_get_information_label();          // 表示ラベル
```

## ファイル構成

```
audubon-customizations/
├── audubon-customizations.php        # メインプラグインファイル
├── includes/
│   ├── post-types.php                # CPT登録
│   ├── meta-boxes.php                # カスタムフィールド
│   ├── template-tags.php             # ヘルパー関数
│   ├── shortcodes.php                # ショートコード4種
│   └── admin.php                     # 管理画面の一覧列拡張
├── assets/
│   ├── css/{banner,works,actor}.css
│   └── js/banner.js                  # 自動スライダー
└── templates/
    ├── single-actor.php              # アクター詳細フォールバック
    └── archive-information.php       # Information一覧フォールバック
```

## ライセンス
GPLv2 or later（WordPress本体に準拠）
