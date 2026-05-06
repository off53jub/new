# Studio Audubon WordPress Customizations

studio-audubon.jp 用のテーマ拡張ファイル一式です。**現在使用中のWordPressテーマフォルダにそのままアップロード**して使います。

## ダウンロード

- **[audubon-customizations.zip](./audubon-customizations.zip)** ← クリックでダウンロード（GitHub上で「Download」ボタンが表示されます）
- もしくは [audubon-theme/](./audubon-theme/) フォルダの中身を直接ダウンロード

## 内容

- アクターページ: プロフィールPDFダウンロード + 最新の出演リンク
- ニュース（news_list）: 投稿日ではなく**フリーテキスト**の放映日時を入力可能
- スライド（slides）: A4ポスター3分割の自動スライダー（`[audubon_slides]`）
- Works（通常投稿）: A4ポスター比率の一覧（`[audubon_works]` または専用テンプレート）

## インストール

1. ZIP を解凍
2. 中身（PHPファイル + assets/ フォルダ）を、現在使用中のテーマフォルダ直下にアップロード
3. テーマの `functions.php` の末尾に1行追加：
   ```php
   require_once get_stylesheet_directory() . '/audubon-customizations.php';
   ```
4. 管理画面 > 設定 > パーマリンク設定で「変更を保存」

詳しい使い方は [audubon-theme/README.md](./audubon-theme/README.md) を参照してください。
