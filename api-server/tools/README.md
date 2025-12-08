# 開発ツール

このディレクトリには、開発・デバッグ用のスクリプトが含まれています。

## ファイル一覧

- `test-auth.php` - 認証テスト用スクリプト
- `fix-admin-password.php` - 管理者パスワード修正スクリプト
- `generate-password-hash.php` - パスワードハッシュ生成スクリプト
- `cors-test.php` - CORS設定テスト用ファイル
- `debug-menus.php` - メニューAPIデバッグ用ファイル
- `orders-fixed.php` - 注文APIの古いバージョン（参考用）
- `test-db.php` - データベース接続テスト用ファイル

## 注意事項

**本番環境では、これらのファイルへのアクセスを制限することを強く推奨します。**

`.htaccess`ファイルでアクセス制限を設定するか、認証を追加してください。

