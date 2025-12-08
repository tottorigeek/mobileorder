# CORSエラー修正ガイド

## 問題

NuxtアプリからエックスサーバーのAPIサーバーにアクセスする際にCORSエラーが発生しています。

## 修正内容

### 1. `.htaccess`の修正

エックスサーバーの`radish/.htaccess`ファイルを以下のように修正してください：

```apache
# CORS設定（Nuxtアプリからのアクセスを許可）
<IfModule mod_headers.c>
    # すべてのリクエストにCORSヘッダーを追加
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, Accept"
    Header always set Access-Control-Max-Age "86400"
</IfModule>

# OPTIONSリクエストの処理（プリフライトリクエスト）
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]
</IfModule>
```

**変更点:**
- `Header set` → `Header always set` に変更（すべてのレスポンスに確実にヘッダーを追加）
- `Access-Control-Allow-Headers`に`Accept`を追加

### 2. `config.php`の修正

`radish/config.php`の`setJsonHeader()`関数を以下のように修正してください：

```php
function setJsonHeader() {
    // CORSヘッダーを確実に設定
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Max-Age: 86400');
        header('Content-Type: application/json; charset=utf-8');
    }
}
```

### 3. `menus.php`の修正

`radish/api/menus.php`の先頭に`setJsonHeader()`を追加してください：

```php
require_once __DIR__ . '/../config.php';

setJsonHeader();  // ← この行を追加

$method = $_SERVER['REQUEST_METHOD'];
```

## 動作確認

### 1. CORSテストファイルで確認

`cors-test.php`ファイルを`radish/api/`ディレクトリにアップロードして、ブラウザで以下にアクセス：

```
http://mameq.xsrv.jp/radish/api/cors-test.php
```

JSONが返ることを確認してください。

### 2. ブラウザの開発者ツールで確認

1. Nuxtアプリを起動（`npm run dev`）
2. ブラウザで`http://localhost:3000/customer`にアクセス
3. **F12**で開発者ツールを開く
4. **Network**タブを開く
5. ページをリロード
6. `menus`へのリクエストを確認：
   - ステータスが**200**になっているか
   - レスポンスヘッダーに`Access-Control-Allow-Origin: *`が含まれているか

### 3. メニューが表示されるか確認

- メニュー一覧ページで6件のメニューが表示されることを確認

## トラブルシューティング

### まだCORSエラーが発生する場合

1. **ブラウザのキャッシュをクリア**
   - Ctrl+Shift+Delete（Windows）
   - Cmd+Shift+Delete（Mac）

2. **`.htaccess`が正しくアップロードされているか確認**
   - ファイル名が`.htaccess`（先頭のドットを含む）になっているか
   - ファイルのパーミッションが正しいか

3. **エックスサーバーの設定を確認**
   - サーバーパネルで`mod_headers`が有効か確認
   - エックスサーバーでは通常有効ですが、確認してください

4. **直接APIにアクセスして確認**
   ```
   http://mameq.xsrv.jp/radish/api/menus
   ```
   ブラウザで直接アクセスしてJSONが返ることを確認

5. **エラーログを確認**
   - エックスサーバーのエラーログを確認
   - PHPのエラーログを確認

## 参考

- [MDN Web Docs - CORS](https://developer.mozilla.org/ja/docs/Web/HTTP/CORS)
- [エックスサーバー マニュアル](https://www.xserver.ne.jp/manual/)

