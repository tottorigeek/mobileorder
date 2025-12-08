# エックスサーバー用 PHP API サーバー

モバイルオーダーシステムのAPIサーバーです。

## 設置場所

エックスサーバーの以下のディレクトリに配置してください：

```
/home/your_account/public_html/radish/
```

または、FTPで接続した場合：

```
public_html/radish/
```

## ファイル構成

```
radish/
├── .htaccess          # URLルーティングとCORS設定
├── config.php         # データベース設定
├── index.php          # エントリーポイント（オプション）
├── api/
│   ├── index.php      # APIルーティング
│   ├── menus.php      # メニューAPI
│   └── orders.php     # 注文API
└── (SQLファイルは database/ フォルダに移動しました)
```

## セットアップ手順

### 1. ファイルのアップロード

FTPまたはファイルマネージャーを使用して、`api-server`ディレクトリ内のファイルをエックスサーバーの`radish`ディレクトリにアップロードしてください。

### 2. データベースの作成

1. エックスサーバーのサーバーパネルにログイン
2. **MySQL設定** → **MySQLデータベース作成**
3. データベース名、ユーザー名、パスワードを設定
4. 作成した情報をメモしておく

### 3. データベーススキーマのインポート

1. サーバーパネルから **phpMyAdmin** にアクセス
2. 作成したデータベースを選択
3. **インポート**タブをクリック
4. `database/schema-multi-shop.sql`ファイルを選択してインポート

### 4. 設定ファイルの編集

`config.php`ファイルを編集して、データベース接続情報を設定してください：

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');      // 作成したデータベース名
define('DB_USER', 'your_database_user');     // 作成したユーザー名
define('DB_PASS', 'your_database_password'); // 作成したパスワード
```

### 5. パーミッションの設定

`.htaccess`ファイルが読み込まれるように、ディレクトリのパーミッションを確認してください（通常は自動的に設定されます）。

## API エンドポイント

### メニューAPI

- **GET** `/radish/api/menus` - メニュー一覧取得
  - クエリパラメータ: `category` (オプション) - カテゴリでフィルター

### 注文API

- **GET** `/radish/api/orders` - 注文一覧取得
  - クエリパラメータ: `status` (オプション) - ステータスでフィルター
- **GET** `/radish/api/orders/{id}` - 注文取得（単一）
- **POST** `/radish/api/orders` - 注文作成
  - リクエストボディ:
    ```json
    {
      "tableNumber": "1",
      "items": [
        {
          "menuId": "1",
          "menuNumber": "001",
          "menuName": "ハンバーガー",
          "quantity": 2,
          "price": 800
        }
      ],
      "totalAmount": 1600
    }
    ```
- **PUT** `/radish/api/orders/{id}` - 注文ステータス更新
  - リクエストボディ:
    ```json
    {
      "status": "accepted"
    }
    ```

## Nuxtアプリからの接続設定

`nuxt.config.ts`の`runtimeConfig`を以下のように設定してください：

```typescript
runtimeConfig: {
  public: {
    apiBase: 'http://mameq.xsrv.jp/radish/api'
  }
}
```

または、環境変数で設定：

```bash
NUXT_PUBLIC_API_BASE=http://mameq.xsrv.jp/radish/api
```

## セキュリティ設定

本番環境では、以下の設定を推奨します：

1. `config.php`の`display_errors`を`0`に設定（既に設定済み）
2. `.htaccess`のCORS設定を必要に応じて制限
3. データベース接続情報の保護
4. SSL証明書の設定（HTTPS化）

## 動作確認

### 1. データベース接続テスト

`test-db.php`ファイルをブラウザで開いて、データベース接続を確認してください：

```
http://mameq.xsrv.jp/radish/test-db.php
```

### 2. APIエンドポイントのテスト

- メニューAPI: `http://mameq.xsrv.jp/radish/api/menus`
- 注文API: `http://mameq.xsrv.jp/radish/api/orders`

ブラウザでアクセスして、JSONが返ることを確認してください。

## トラブルシューティング

詳細は [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) と [ERROR_HANDLING.md](./ERROR_HANDLING.md) を参照してください。

### 500エラーが発生する場合

1. データベース接続情報が正しいか確認
2. データベースが作成されているか確認
3. テーブルが作成されているか確認（phpMyAdminで確認）
4. エラーログを確認（エックスサーバーのログファイル）

### CORSエラーが発生する場合

1. `.htaccess`ファイルが正しくアップロードされているか確認
2. `mod_headers`モジュールが有効か確認（エックスサーバーでは通常有効）

### データベース接続エラーが発生する場合

1. `config.php`の接続情報を確認
2. データベースユーザーに適切な権限が付与されているか確認
3. エックスサーバーのデータベースホスト名を確認（通常は`localhost`）

## 参考リンク

- [エックスサーバー マニュアル](https://www.xserver.ne.jp/manual/)
- [PHP PDO マニュアル](https://www.php.net/manual/ja/book.pdo.php)

