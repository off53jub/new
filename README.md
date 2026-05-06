# Studio Audubon WordPress カスタマイズ

studio-audubon.jp の既存テーマ `studio_audubon_02` に対する機能拡張を組み込んだバージョンです。

## ダウンロード

- **[studio_audubon_02.zip](./studio_audubon_02.zip)** ← 修正版テーマ一式（約3MB）
- もしくは [studio_audubon_02/](./studio_audubon_02/) フォルダの中身を直接

## 適用方法

1. **念のため現行テーマをバックアップ**（FTPで `wp-content/themes/studio_audubon_02/` をローカルに保存）
2. 上記ZIPをダウンロード&解凍
3. 解凍したフォルダの中身を、サーバの `wp-content/themes/studio_audubon_02/` に**上書きアップロード**
4. 管理画面 > 設定 > パーマリンク設定 で「変更を保存」

これでサイト体裁はそのままに、以下の機能が追加・変更されます。

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
studio_audubon_02/
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

`functions.php` の最終行（`require_once` の行）を削除すれば、機能拡張だけ無効化できます。
完全に元に戻すにはバックアップしておいた元のテーマファイルで上書きしてください。

入力したデータ（PDF、放映日時、キャプション等）はDBに残るため、再度 `require_once` を有効化すれば復元されます。
