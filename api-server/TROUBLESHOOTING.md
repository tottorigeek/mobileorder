# トラブルシューティングガイド

## 現在のエラー状況

APIサーバーにアクセスした際に以下のエラーが発生しています：

```
{"error":"Database connection failed"}
```

これはデータベース接続に失敗していることを示しています。

## 確認手順

### 1. config.phpの設定確認

エックスサーバーのFTPまたはファイルマネージャーで `config.php` を開き、以下の設定が正しいか確認してください：

```php
define('DB_HOST', 'localhost'); // 通常は 'localhost' のまま
define('DB_NAME', 'your_database_name'); // ← 実際のデータベース名に変更
define('DB_USER', 'your_database_user'); // ← 実際のユーザー名に変更
define('DB_PASS', 'your_database_password'); // ← 実際のパスワードに変更
```

### 2. データベースの作成確認

エックスサーバーのサーバーパネルで以下を確認：

1. **MySQL設定** → **MySQLデータベース一覧** を開く
2. データベースが作成されているか確認
3. データベース名、ユーザー名、パスワードをメモ

### 3. データベーススキーマのインポート確認

1. サーバーパネルから **phpMyAdmin** を開く
2. 作成したデータベースを選択
3. **構造**タブで以下のテーブルが存在するか確認：
   - `menus`
   - `orders`
   - `order_items`

テーブルが存在しない場合は、`database.sql`をインポートしてください。

### 4. エックスサーバーのデータベースホスト名確認

エックスサーバーによっては、`localhost`ではなく、専用のホスト名が設定されている場合があります。

サーバーパネルの **MySQL設定** → **MySQLデータベース一覧** で、**ホスト名**を確認してください。

例：
- `localhost`
- `mysql123.xserver.jp`（サーバーによって異なる）

ホスト名が`localhost`以外の場合は、`config.php`の`DB_HOST`を変更してください。

### 5. 接続テスト用のPHPファイル

以下の内容で `test-db.php` を作成し、`radish`ディレクトリにアップロードして、ブラウザでアクセスしてください：

```php
<?php
// データベース接続テスト
define('DB_HOST', 'localhost'); // 実際のホスト名に変更
define('DB_NAME', 'your_database_name'); // 実際のデータベース名に変更
define('DB_USER', 'your_database_user'); // 実際のユーザー名に変更
define('DB_PASS', 'your_database_password'); // 実際のパスワードに変更

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "✓ データベース接続成功！<br>";
    echo "データベース名: " . DB_NAME . "<br>";
    
    // テーブル一覧を表示
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>テーブル一覧:<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
    // メニューテーブルのデータ数を確認
    if (in_array('menus', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM menus");
        $count = $stmt->fetchColumn();
        echo "<br>メニュー数: " . $count . "件<br>";
    }
    
} catch (PDOException $e) {
    echo "✗ データベース接続エラー: " . $e->getMessage() . "<br>";
    echo "<br>確認事項:<br>";
    echo "1. DB_HOST: " . DB_HOST . "<br>";
    echo "2. DB_NAME: " . DB_NAME . "<br>";
    echo "3. DB_USER: " . DB_USER . "<br>";
    echo "4. DB_PASS: " . (DB_PASS ? "設定済み" : "未設定") . "<br>";
}
?>
```

`http://mameq.xsrv.jp/radish/test-db.php` にアクセスして、接続状況を確認してください。

## よくある問題と解決方法

### 問題1: データベース名・ユーザー名・パスワードが間違っている

**解決方法:**
- サーバーパネルの **MySQL設定** → **MySQLデータベース一覧** で正確な情報を確認
- `config.php`を正しい情報で更新

### 問題2: データベースが作成されていない

**解決方法:**
1. サーバーパネル → **MySQL設定** → **MySQLデータベース作成**
2. データベース名、ユーザー名、パスワードを設定
3. `database.sql`をインポート

### 問題3: テーブルが存在しない

**解決方法:**
1. phpMyAdminでデータベースを選択
2. **インポート**タブを開く
3. `database.sql`ファイルを選択してインポート

### 問題4: ホスト名が間違っている

**解決方法:**
- サーバーパネルでホスト名を確認
- `config.php`の`DB_HOST`を正しいホスト名に変更

## 動作確認手順

設定を修正した後、以下の手順で動作確認してください：

1. **データベース接続テスト**
   ```
   http://mameq.xsrv.jp/radish/test-db.php
   ```
   接続成功とテーブル一覧が表示されることを確認

2. **メニューAPIテスト**
   ```
   http://mameq.xsrv.jp/radish/api/menus
   ```
   JSON形式でメニュー一覧が返ることを確認

3. **注文APIテスト**
   ```
   http://mameq.xsrv.jp/radish/api/orders
   ```
   JSON形式で注文一覧（空の配列でもOK）が返ることを確認

## サポート

問題が解決しない場合は、以下を確認してください：

1. エックスサーバーのエラーログ（サーバーパネル → ログ）
2. PHPのエラーログ
3. `test-db.php`の出力内容

