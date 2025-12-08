# エックスサーバーへのデプロイ手順

## 1. ファイルの準備

`api-server`ディレクトリ内の以下のファイルを準備してください：

- `.htaccess`
- `config.php`
- `index.php`（オプション）
- `api/index.php`
- `api/menus.php`
- `api/orders.php`
- `database.sql`

## 2. FTP接続

1. エックスサーバーのサーバーパネルからFTP情報を取得
2. FTPクライアント（FileZillaなど）で接続
3. `/home/your_account/public_html/radish/` ディレクトリに移動

## 3. ファイルのアップロード

以下のディレクトリ構造でアップロード：

```
public_html/
  radish/
    .htaccess
    config.php
    index.php
    api/
      index.php
      menus.php
      orders.php
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
4. `database.sql`ファイルを選択
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

- `http://mameq.xsrv.jp/radish/` - トップページ（設定確認）
- `http://mameq.xsrv.jp/radish/api/menus` - メニューAPI（JSONが返ることを確認）

## 8. Nuxtアプリの設定

`nuxt.config.ts`を更新：

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

