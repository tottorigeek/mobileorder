# エックスサーバーへのデプロイ手順

## 1. ファイルの準備

`api-server/radish/v1`ディレクトリ内の以下のファイルを準備してください：

- `.htaccess`
- `config.php`
- `index.php`（オプション）
- `api/index.php`
- `api/auth.php`
- `api/menus.php`
- `api/orders.php`
- `api/shops.php`
- その他のAPIファイル
- `database/schema-multi-shop.sql`（SQLファイルは`database/`フォルダに集約されています）

## 2. FTP接続

1. サーバーのFTP情報を取得
2. FTPクライアント（FileZillaなど）で接続
3. `/radish/v1/` ディレクトリに移動

## 3. ファイルのアップロード

以下のディレクトリ構造でアップロード：

```
radish/
  v1/
    .htaccess
    config.php
    index.php
    api/
      index.php
      auth.php
      menus.php
      orders.php
      shops.php
      ...
    tools/
      ...
```

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

FTPまたはファイルマネージャーで`config.php`を編集：

```php
define('DB_NAME', 'mobileorder_db');      // 作成したデータベース名
define('DB_USER', 'mobileorder_user');    // 作成したユーザー名
define('DB_PASS', 'your_password');        // 作成したパスワード
```

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

