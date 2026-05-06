# Studio Audubon WordPress カスタマイズ

studio-audubon.jp の既存テーマ `studio_audubon_03` に対する機能拡張を組み込んだバージョンです。

## ダウンロード

- **[studio_audubon_03.zip](./studio_audubon_03.zip)** ← 修正版テーマ一式（約3MB）
- もしくは [studio_audubon_03/](./studio_audubon_03/) フォルダの中身を直接

## 適用方法

このテーマは **新しいフォルダ名 (`studio_audubon_03`)** として作られているので、
既存の `studio_audubon_02` を上書き・削除しません。**並立してインストール**し、
管理画面から切り替えるだけで適用・ロールバックができます。

1. ZIPをダウンロード&解凍
2. 解凍した `studio_audubon_03` フォルダを **そのまま** サーバの `wp-content/themes/` にアップロード
   （結果として `wp-content/themes/studio_audubon_02/` と `wp-content/themes/studio_audubon_03/` が両方存在する状態になります）
3. 管理画面 > **外観 > テーマ** を開くと「studio_audubon 03」が表示されるので、**ライブプレビュー**で見た目を確認 → 問題なければ **有効化**
4. 設定 > パーマリンク設定 で「変更を保存」

**ロールバック**: 管理画面 > 外観 > テーマ で `studio_audubon_02` を有効化すれば即座に元に戻ります。
既存テーマは触っていないため、いつでも切り戻せます。

サイト体裁はそのまま（既存の `style.css` 1848行を踏襲）に、以下の機能が追加・変更されます。

## 変更点

### 1. アクター詳細ページ
`single-actor.php` のプロフィール下に追加表示：
- **最新の出演** リンク（Information記事へ自動でリンク）
- **プロフィールPDFをダウンロード** ボタン

編集画面（アクター）に「アクター追加情報」メタボックスが追加され、PDFと最新出演を設定できます。

### 2. ニュース → Information
- ページタイトル `News` → `Information` に変更（トップページ + ニュースページ共通）
- 表示日付を **投稿の作成日** から **任意のフリーテキスト放映日時** に変更
  - 例: `2025年8月13日(水)21:00〜` `毎週土曜` `公開中` など
- 編集画面（ニュース）に「放映日時・出演アクター」メタボックスが追加されます
- 並び替え用日付フィールドで一覧の並びを制御可能（空欄なら投稿日順）

### 3. トップページのスライダー（バナー）
- 既存の単一画像フェードスライダー → **A4ポスター3分割の自動スライダー** に置き換え
- 各スライドの下に「○○出演」などのキャプションを表示可能
- 編集画面（スライド）に「スライド設定」メタボックスが追加されます

### 4. Works一覧
- サムネイルを **A4ポスター比率（210:297）** で表示
- アイキャッチ画像が中央クロップで収まります

## 追加されたファイル

```
studio_audubon_03/
├── audubon-customizations.php    ← 全機能のメインファイル（新規）
└── assets/
    ├── css/audubon-features.css  ← 機能拡張用CSS（新規）
    └── js/audubon-features.js    ← スライダーJS（新規）
```

## 修正されたファイル

| ファイル | 変更点 |
|---|---|
| `functions.php` | `audubon-customizations.php` を1行で読み込み |
| `single-actor.php` | 「最新の出演」「PDFダウンロード」ブロックを追加 |
| `page-news.php` | タイトル `News` → `Information`、フリーテキスト日付対応 |
| `index.php` | トップのスライダーを3分割A4化、News→Information、フリーテキスト日付 |
| `page-works.php` | サムネイルをA4比率で表示するようマークアップ整理 |

## メタキー（参考）

全て `_audubon_*` プレフィックスで名前空間化、既存ACFフィールドとは衝突しません。

| メタキー | 対象CPT | 内容 |
|---|---|---|
| `_audubon_profile_pdf_id` | actor | プロフィールPDF（添付ID） |
| `_audubon_latest_information_id` | actor | 最新出演（手動指定） |
| `_audubon_auto_latest_information` | actor | 自動取得ON/OFF |
| `_audubon_broadcast_text` | news_list | 放映日時（フリーテキスト） |
| `_audubon_broadcast_sort` | news_list | 並び替え用日付 |
| `_audubon_related_actors` | news_list | 出演アクター（複数） |
| `_audubon_slide_caption` | slides | ポスター下キャプション |
| `_audubon_slide_link` | slides | リンク先URL |

## ロールバック

- **テーマ単位で戻す**: 管理画面 > 外観 > テーマ で `studio_audubon_02` を有効化
- **新テーマを残しつつ機能拡張だけ無効化**: `studio_audubon_03/functions.php` の最終行
  （`require_once ... audubon-customizations.php` の行）を削除

入力したデータ（PDF、放映日時、キャプション等）はDBに残るため、再有効化すればそのまま復元されます。
