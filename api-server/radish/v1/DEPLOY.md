# エックスサーバーへのデプロイ手順

## 1. ファイルの準備

`api-server/radish`ディレクトリ内の以下のファイルを準備してください：

- `radish/config.php` - データベース設定（**radish直下**）
- `radish/index.php` - エントリーポイント（オプション）
- `radish/v1/index.php` - APIルーティング
- `radish/v1/auth.php` - 認証API
- `radish/v1/menus.php` - メニューAPI
- `radish/v1/orders.php` - 注文API
- `radish/v1/shops.php` - 店舗API
- `radish/v1/users.php` - ユーザーAPI
- その他のAPIファイル（`radish/v1/`内）
- `radish/tools/` - ユーティリティツール
- `database/schema-multi-shop.sql`（SQLファイルは`database/`フォルダに集約されています）

## 2. FTP接続

1. サーバーのFTP情報を取得
2. FTPクライアント（FileZillaなど）で接続
3. `/radish/` ディレクトリに移動

## 3. ファイルのアップロード

以下のディレクトリ構造でアップロード：

```
radish/
  config.php          # ← radish直下に配置
  index.php
  v1/
    index.php         # APIルーティング
    auth.php
    menus.php
    orders.php
    shops.php
    users.php
    ...
  tools/
    test-db.php
    cors-test.php
    ...
```

**重要**: `config.php`は`radish/`直下に配置してください。`v1/`フォルダ内のAPIファイルは`require_once __DIR__ . '/../config.php';`で親ディレクトリの`config.php`を読み込みます。

## 4. データベースの作成

1. サーバーパネルにログイン
2. **MySQL設定** → **MySQLデータベース作成**
3. 以下を設定：
   - データベース名（例: `mobileorder_db`）
   - ユーザー名（例: `mobileorder_user`）
   - パスワード（強力なパスワードを設定）
4. 作成完了後、情報をメモ

## 5. データベーススキーマのインポート

1. サーバーパネルから **phpMyAdmin** を開く
2. 作成したデータベースを選択
3. **インポート**タブをクリック
4. `database/schema-multi-shop.sql`ファイルを選択
5. **実行**をクリック

## 6. 設定ファイルの編集

FTPまたはファイルマネージャーで`radish/config.php`を編集：

```php
define('DB_NAME', 'mobileorder_db');      // 作成したデータベース名
define('DB_USER', 'mobileorder_user');    // 作成したユーザー名
define('DB_PASS', 'your_password');        // 作成したパスワード
```

**注意**: `config.php`は`radish/`直下に配置してください。`v1/`フォルダ内ではありません。

## 7. 動作確認

ブラウザで以下にアクセス：

- `https://api.towndx.com/radish/v1/` - トップページ（設定確認）
- `https://api.towndx.com/radish/v1/api/menus` - メニューAPI（JSONが返ることを確認）

## 8. Nuxtアプリの設定

`nuxt.config.ts`を更新：

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

## トラブルシューティング

### 403 Forbiddenエラー

- `.htaccess`ファイルのパーミッションを確認
- ディレクトリのパーミッションを755に設定

### 500 Internal Server Error

- `config.php`のデータベース接続情報を確認
- エラーログを確認（サーバーパネル → ログ）

### データベース接続エラー

- データベース名、ユーザー名、パスワードが正しいか確認
- データベースが作成されているか確認
- ユーザーにデータベースへのアクセス権限があるか確認

