# PHP API サーバー

モバイルオーダーシステムのAPIサーバーです。

## フォルダ構造

```
api-server/
└── radish/
    ├── config.php         # データベース設定（radish直下）
    ├── index.php          # エントリーポイント（オプション）
    ├── v1/
    │   ├── index.php      # APIルーティング
    │   ├── auth.php       # 認証API
    │   ├── menus.php      # メニューAPI
    │   ├── orders.php     # 注文API
    │   ├── shops.php      # 店舗API
    │   ├── users.php      # ユーザーAPI
    │   └── ...            # その他のAPI
    └── tools/             # ユーティリティツール
        ├── test-db.php
        ├── cors-test.php
        └── ...
```

## 設置場所

サーバーの以下のディレクトリに配置してください：

```
/radish/
```

APIのベースURLは以下のようになります：
```
https://api.towndx.com/radish/v1
```

**重要**: `config.php`は`radish/`直下に配置し、`v1/`フォルダ内のAPIファイルは`require_once __DIR__ . '/../config.php';`で読み込みます。

## セットアップ手順

### 1. ファイルのアップロード

FTPまたはファイルマネージャーを使用して、`api-server/radish`ディレクトリ内のファイルをサーバーの`radish`ディレクトリにアップロードしてください。

**重要**: `config.php`は`radish/`直下に配置し、`v1/`フォルダと`tools/`フォルダも`radish/`直下に配置してください。

### 2. データベースの作成

1. サーバーパネルにログイン
2. **MySQL設定** → **MySQLデータベース作成**
3. データベース名、ユーザー名、パスワードを設定
4. 作成した情報をメモしておく

### 3. データベーススキーマのインポート

1. サーバーパネルから **phpMyAdmin** にアクセス
2. 作成したデータベースを選択
3. **インポート**タブをクリック
4. `database/schema-multi-shop.sql`ファイルを選択してインポート

### 4. 設定ファイルの編集

`radish/config.php`ファイルを編集して、データベース接続情報を設定してください：

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');      // 作成したデータベース名
define('DB_USER', 'your_database_user');     // 作成したユーザー名
define('DB_PASS', 'your_database_password'); // 作成したパスワード
```

**重要**: `config.php`は`radish/`直下に配置してください。`v1/`フォルダ内ではありません。

### 5. パーミッションの設定

必要に応じて、ディレクトリのパーミッションを確認してください（通常は自動的に設定されます）。

## API エンドポイント

### メニューAPI

- **GET** `/radish/v1/api/menus` - メニュー一覧取得
  - クエリパラメータ: `category` (オプション) - カテゴリでフィルター
  - クエリパラメータ: `shop` (オプション) - 店舗コードでフィルター

### 注文API

- **GET** `/radish/v1/api/orders` - 注文一覧取得
  - クエリパラメータ: `status` (オプション) - ステータスでフィルター
  - クエリパラメータ: `shop` (オプション) - 店舗コードでフィルター
  - クエリパラメータ: `tableNumber` (オプション) - テーブル番号でフィルター
- **GET** `/radish/v1/api/orders/{id}` - 注文取得（単一）
- **POST** `/radish/v1/api/orders` - 注文作成
  - リクエストボディ:
    ```json
    {
      "shopCode": "shop001",
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
- **PUT** `/radish/v1/api/orders/{id}` - 注文ステータス更新
  - リクエストボディ:
    ```json
    {
      "status": "accepted"
    }
    ```

### 認証API

- **POST** `/radish/v1/api/auth/login` - ログイン
- **GET** `/radish/v1/api/auth/me` - 認証状態確認

## Nuxtアプリからの接続設定

`nuxt.config.ts`の`runtimeConfig`を以下のように設定してください：

```typescript
runtimeConfig: {
  public: {
    apiBase: 'https://api.towndx.com/radish/v1'
  }
}
```

または、環境変数で設定：

```bash
NUXT_PUBLIC_API_BASE=https://api.towndx.com/radish/v1
```

## セキュリティ設定

本番環境では、以下の設定を推奨します：

1. `config.php`の`display_errors`を`0`に設定（既に設定済み）
2. `.htaccess`のCORS設定を必要に応じて制限
3. データベース接続情報の保護
4. SSL証明書の設定（HTTPS化）

## 動作確認

### 1. データベース接続テスト

`radish/tools/test-db.php`ファイルをブラウザで開いて、データベース接続を確認してください：

```
https://api.towndx.com/radish/tools/test-db.php
```

### 2. APIエンドポイントのテスト

- メニューAPI: `https://api.towndx.com/radish/v1/api/menus`
- 注文API: `https://api.towndx.com/radish/v1/api/orders`

ブラウザでアクセスして、JSONが返ることを確認してください。

## トラブルシューティング

詳細は [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) と [ERROR_HANDLING.md](./ERROR_HANDLING.md) を参照してください。

### 500エラーが発生する場合

1. データベース接続情報が正しいか確認（`radish/config.php`）
2. データベースが作成されているか確認
3. テーブルが作成されているか確認（phpMyAdminで確認）
4. エラーログを確認（サーバーパネル → ログ）

### CORSエラーが発生する場合

1. `radish/config.php`のCORS設定を確認（`setJsonHeader()`関数）
2. 許可オリジンリストにフロントエンドのドメインが含まれているか確認

### データベース接続エラーが発生する場合

1. `radish/config.php`の接続情報を確認
2. データベースユーザーに適切な権限が付与されているか確認
3. データベースホスト名を確認（通常は`localhost`）

## 参考リンク

- [PHP PDO マニュアル](https://www.php.net/manual/ja/book.pdo.php)

