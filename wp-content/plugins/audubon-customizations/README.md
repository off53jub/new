# Studio Audubon Customizations

Studio Audubonサイト（https://studio-audubon.jp/）のWordPressカスタマイズプラグインです。
**新しいCPTは作りません**。既存のCPTに対してメタフィールドと表示用テンプレートを追加し、必要な機能だけを後付けします。

| 領域 | 既存CPT | 追加するもの |
|---|---|---|
| アクターページ | `actor` | プロフィールPDF / 最新の出演リンク（自動 or 手動） |
| ニュース       | `news_list` | 放映日時（**フリーテキスト**） / 並び替え用日付 / 出演アクター紐付け |
| スライド       | `slides` | ポスター下に表示するテキスト / リンク先（A4 3分割スライダーのショートコード） |
| Works          | サイト側で運用しているCPT（要指定）| A4ポスター比率で表示するショートコード |

## 機能詳細

### 1. アクターページ（CPT: `actor`）
編集画面に「アクター追加情報」セクションが追加されます。
- **プロフィールPDF**: メディアライブラリからPDFを選択
- **最新の出演（ニュース記事）**: 自動取得 or 手動指定

詳細ページに以下が表示されます（テンプレートのフォールバック適用時、または `[audubon_actor_links]` を貼り付けた場所）。
- 「最新の出演」→ ニュース記事へのリンク
- 「プロフィールPDFをダウンロード」ボタン

### 2. ニュース（CPT: `news_list`）
- **放映日時**: フリーテキスト（例: `2025年8月13日(水)21:00〜` / `毎週土曜` / `公開中` など自由）
- **並び替え用日付**: 一覧の並び順制御に使う任意の `YYYY-MM-DD`
- **出演アクター**: アクターを複数紐付け（アクターページの「最新の出演」自動取得に使用）
- 一覧テンプレートでは投稿日の代わりに**フリーテキスト**を表示します。

### 3. スライド（CPT: `slides`）
既存の `slides` CPTに以下を追加：
- ポスター下に表示するテキスト（例: `山田太郎 出演`）
- リンク先URL（任意）

ショートコード `[audubon_slides]` を貼ると、A4ポスター3分割の自動スライダーで表示できます。
**既存テーマのスライド表示には影響しません。**

### 4. Works
Worksの運用CPTがサイト固有のため、ショートコード呼び出し時に明示します。

```
[audubon_works post_type="works"]
```

通常投稿のカテゴリで運用している場合：
```
[audubon_works post_type="post" category="works"]
```

カラム数: `columns="2"` / `columns="3"`（既定） / `columns="4"`

A4比率（210:297）でアイキャッチ画像を中央クロップ表示します。

## ショートコード一覧

| ショートコード | 説明 |
|---|---|
| `[audubon_slides]` | 既存`slides` CPTを使ったA4ポスター3分割の自動スライダー |
| `[audubon_works post_type="..."]` | A4ポスター比率の静的Works一覧 |
| `[audubon_actor_links]` | アクターページの「最新の出演 / PDF」リンクブロック |
| `[audubon_news_date]` | ニュース記事の放映日時（フリーテキスト） |

## テーマファイルとしての保管

将来的に新テーマを作成する想定があるため、本プラグインの `templates/` 配下に**ドロップイン可能な PHP テンプレートファイル**を別ファイルとして保管しています。

```
templates/
├── single-actor.php          # 新テーマにそのままコピー可
├── single-news_list.php
└── archive-news_list.php
```

**新テーマ移行手順**:
1. 上記のファイルを新テーマのルート直下にコピー
2. `wp_enqueue_style()` で本プラグインのCSSを読み込むか、内容を新テーマのスタイルシートにマージ
3. テーマ側に同名ファイルがあれば WordPress のテンプレート階層により**テーマ側が優先**されるので、自動的に切り替わります

既存テーマに同名のテンプレートが既にある場合、本プラグインのフォールバックは適用されません（既存表示はそのまま）。

## 設置方法

1. このディレクトリ全体を `/wp-content/plugins/audubon-customizations/` にアップロード
2. 管理画面 > プラグイン から有効化
3. 設定 > パーマリンク設定 を開いて「変更を保存」
4. 必要なページに上記ショートコードを貼り付け

## ファイル構成

```
audubon-customizations/
├── audubon-customizations.php        # メイン
├── README.md
├── includes/
│   ├── post-types.php                # 既存CPTへのpre_get_postsフックのみ
│   ├── meta-boxes.php                # actor / news_list / slides にメタボックス追加
│   ├── template-tags.php             # ヘルパー関数
│   ├── shortcodes.php                # 4種のショートコード
│   └── admin.php                     # 既存CPT一覧の列拡張
├── assets/
│   ├── css/{banner,works,actor}.css
│   └── js/banner.js                  # スライダーの自動回転
└── templates/                        # 新テーマ移行用のドロップインテンプレート
    ├── single-actor.php
    ├── single-news_list.php
    └── archive-news_list.php
```

## ライセンス
GPLv2 or later
