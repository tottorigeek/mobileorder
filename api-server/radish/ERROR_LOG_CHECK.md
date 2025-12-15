# エックスサーバーでのエラーログ確認方法

## エラーログの確認方法（3つの方法）

### 方法1: サーバーパネルから確認（最も簡単）

1. **エックスサーバーのサーバーパネルにログイン**
2. **「ログ」メニューをクリック**
3. **「エラーログ」を選択**
4. 最新のエラーログが表示されます

**確認できる内容:**
- PHPのエラー
- データベース接続エラー
- 一般的なサーバーエラー

**注意点:**
- エラーログは一定期間で自動削除されます
- 最新のエラーのみが表示される場合があります

---

### 方法2: PHPエラーログファイルを直接確認（詳細なログ）

エックスサーバーでは、PHPのエラーログが特定のファイルに保存されます。

#### ファイルの場所

通常、以下のパスに保存されています：

```
/home/ユーザー名/log/エラーログファイル名
```

**例:**
```
/home/towndx/log/error_log
/home/towndx/log/php_error.log
```

#### 確認方法

1. **FTPまたはファイルマネージャーでログディレクトリにアクセス**
2. **エラーログファイルを開く**
3. **最新のエラーを確認**

#### ファイルマネージャーでの確認手順

1. サーバーパネル → **ファイルマネージャー**を開く
2. **`/home/ユーザー名/log/`** ディレクトリに移動
3. **エラーログファイル**（`error_log` や `php_error.log` など）を開く
4. 最新のエラーを確認

#### パスワード再設定・ログイン関連のエラーを検索

エラーログファイルを開いたら、以下のキーワードで検索してください：

- `Password reset`
- `Login`
- `password_verify`
- `password_hash`
- `auth.php`

**例:**
```
[2024-01-15 10:30:45] Password reset - Updating password for user ID: 123
[2024-01-15 10:30:45] Password reset - Update affected rows: 1
[2024-01-15 10:30:45] Password reset - Password verification test: SUCCESS
[2024-01-15 10:31:20] Login - Password verification for user: testuser
[2024-01-15 10:31:20] Login - Password match: no
```

---

### 方法3: アプリケーションのエラーログ（データベースに保存）

このアプリケーションでは、エラーログをデータベースの `error_logs` テーブルに保存しています。

#### 確認方法

1. **管理画面から確認**（推奨）
   - ログイン後、エラーログ一覧ページにアクセス
   - URL: `https://your-domain.com/unei/error-logs`
   - オーナーまたはマネージャー権限が必要です

2. **phpMyAdminから直接確認**
   - サーバーパネル → **phpMyAdmin**を開く
   - データベースを選択
   - `error_logs` テーブルを開く
   - 最新のエラーを確認

#### SQLクエリで確認

phpMyAdminのSQLタブで以下のクエリを実行：

```sql
-- 最新のエラーログを10件取得
SELECT * FROM error_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- パスワード関連のエラーのみ取得
SELECT * FROM error_logs 
WHERE message LIKE '%Password%' OR message LIKE '%password%'
ORDER BY created_at DESC 
LIMIT 20;

-- 認証関連のエラーのみ取得
SELECT * FROM error_logs 
WHERE message LIKE '%Login%' OR message LIKE '%auth%'
ORDER BY created_at DESC 
LIMIT 20;
```

---

## デバッグモードの有効化

エラーログをより詳細に確認するには、デバッグモードを有効にしてください。

### `.env`ファイルの設定

`api-server/radish/.env` ファイルを編集：

```env
# 環境設定
ENVIRONMENT=development

# または直接設定
DEBUG_MODE=true
```

**注意:** 本番環境では `ENVIRONMENT=production` に設定してください。

### デバッグモードが有効な場合のログ内容

デバッグモードが有効な場合、以下の詳細情報がログに記録されます：

- パスワード再設定時の詳細情報
  - ユーザーID
  - パスワードの長さ
  - ハッシュのプレビュー
  - 検証結果

- ログイン時の詳細情報
  - 入力パスワードの長さ
  - 保存されているハッシュの長さ
  - ハッシュのプレビュー
  - 検証結果

---

## パスワード再設定・ログイン問題の確認手順

### ステップ1: エラーログを確認

1. サーバーパネル → **ログ** → **エラーログ**を確認
2. または、FTP/ファイルマネージャーで `/home/ユーザー名/log/error_log` を確認

### ステップ2: パスワード再設定時のログを確認

エラーログで以下のメッセージを検索：

```
Password reset - Updating password for user ID: XXX
Password reset - Update affected rows: 1
Password reset - Password verification test: SUCCESS/FAILED
```

**SUCCESS の場合:** パスワードは正しく更新されています
**FAILED の場合:** パスワードの更新に問題があります

### ステップ3: ログイン時のログを確認

エラーログで以下のメッセージを検索：

```
Login - Password verification for user: XXX
Login - Password match: yes/no
```

**yes の場合:** パスワードは正しく検証されています
**no の場合:** パスワードが一致していません

### ステップ4: データベースを直接確認

phpMyAdminで以下のクエリを実行：

```sql
-- ユーザーのパスワードハッシュを確認
SELECT id, username, 
       LENGTH(password_hash) as hash_length,
       LEFT(password_hash, 20) as hash_preview,
       updated_at
FROM users 
WHERE username = 'your_username';
```

**確認ポイント:**
- `hash_length` が 60 文字であること（bcryptの場合）
- `updated_at` がパスワード再設定後に更新されていること

---

## よくあるエラーメッセージと対処法

### 1. "Password reset - Update affected rows: 0"

**原因:** パスワードの更新が失敗している
**対処法:**
- ユーザーIDが正しいか確認
- データベース接続を確認
- データベースの権限を確認

### 2. "Password reset - Password verification test: FAILED"

**原因:** パスワードのハッシュ化または保存に問題がある
**対処法:**
- データベースの `password_hash` カラムの長さを確認（VARCHAR(255)以上）
- 文字エンコーディングを確認（utf8mb4）

### 3. "Login - Password match: no"

**原因:** パスワードが一致しない
**対処法:**
- パスワード再設定が正しく完了しているか確認
- 入力されたパスワードが正しいか確認
- データベースのパスワードハッシュを確認

---

## エラーログの保存期間

- **サーバーパネルのエラーログ:** 通常、数日から1週間程度
- **PHPエラーログファイル:** サーバーの設定により異なる（通常は数週間から数ヶ月）
- **データベースのエラーログ:** 削除しない限り永続的に保存

---

## トラブルシューティング

### エラーログが見つからない場合

1. **ファイルマネージャーで確認**
   - `/home/ユーザー名/log/` ディレクトリを確認
   - ファイル名が異なる場合があります（`error_log`, `php_error.log`, `error.log` など）

2. **サーバーパネルで確認**
   - サーバーパネル → **ログ** → **エラーログ**を確認
   - 最新のエラーのみが表示される場合があります

3. **デバッグモードを有効化**
   - `.env` ファイルで `ENVIRONMENT=development` を設定
   - より詳細なログが記録されます

### エラーログが大きすぎる場合

1. **特定のキーワードで検索**
   - エラーログファイルを開いて、`Password reset` や `Login` で検索

2. **最新のエラーのみ確認**
   - エラーログファイルの最後の部分を確認（最新のエラーが記録されています）

3. **データベースのエラーログを確認**
   - 管理画面のエラーログ一覧ページを使用
   - フィルター機能で特定のエラーのみ表示

---

## 参考リンク

- [エックスサーバーのログ確認方法](https://www.xserver.ne.jp/manual/man_server_log.php)
- [PHPエラーログの設定](https://www.xserver.ne.jp/manual/man_server_php_error.php)

