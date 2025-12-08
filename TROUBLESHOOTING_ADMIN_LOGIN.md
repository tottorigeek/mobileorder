# adminユーザーのログイン問題のトラブルシューティング

## 問題

`admin` / `password123` でログインできない。APIから `{"error":"Invalid credentials"}` が返ってくる。

## 原因

認証API (`api-server/api/auth.php`) は以下の条件をすべて満たす必要があります：

1. ユーザーが存在する (`username = 'admin'`)
2. ユーザーが有効 (`is_active = 1`)
3. **ユーザーに関連する店舗が存在する** (`shop_id` が有効)
4. **店舗が有効** (`shops.is_active = 1`)
5. パスワードが正しい (`password_verify` が成功)

`INNER JOIN shops` を使用しているため、店舗が存在しない、または無効化されている場合はログインできません。

## 診断方法

### 1. テストスクリプトを実行

以下のURLにアクセスして、`admin` ユーザーの状態を確認してください：

```
http://mameq.xsrv.jp/radish/api/test-auth.php
```

このスクリプトは以下を確認します：
- ユーザーの存在
- パスワードハッシュの検証
- 店舗の関連付け
- ログイン可能かどうか

### 2. データベースで直接確認

phpMyAdminで以下のSQLを実行してください：

```sql
-- adminユーザーの状態を確認
SELECT 
    u.id,
    u.username,
    u.name,
    u.role,
    u.is_active as user_active,
    u.shop_id,
    s.code as shop_code,
    s.name as shop_name,
    s.is_active as shop_active
FROM users u
LEFT JOIN shops s ON u.shop_id = s.id
WHERE u.username = 'admin';
```

## 解決方法

### 方法1: パスワード修正スクリプトを実行（最も簡単）

以下のURLにアクセスするだけで、パスワードが自動的に修正されます：

```
http://mameq.xsrv.jp/radish/api/fix-admin-password.php
```

このスクリプトは：
- `password123` の正しいハッシュを生成
- `admin` ユーザーのパスワードを更新
- 検証結果を表示

### 方法2: パスワードハッシュを生成してSQLで修正

1. 以下のURLにアクセスしてハッシュを生成：
   ```
   http://mameq.xsrv.jp/radish/api/generate-password-hash.php
   ```

2. 表示されたSQLをphpMyAdminで実行

### 方法3: 既存のSQLファイルを使用

`database/fix-admin-password-correct.sql` を実行してください。これにより：
- パスワードハッシュが正しく設定されます
- 店舗との関連付けが確認されます

### 方法2: adminユーザーを再作成

`sample-data.sql` を再実行してください。ただし、既存データが削除されるため注意してください。

### 方法3: 手動で修正

phpMyAdminで以下のSQLを実行：

```sql
-- 1. shop001のIDを取得
SET @shop1_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);

-- 2. adminユーザーが存在するか確認
SELECT * FROM users WHERE username = 'admin';

-- 3. adminユーザーが存在しない場合は作成
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `email`, `role`, `is_active`) 
VALUES (
    @shop1_id, 
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'システム管理者', 
    'admin@example.com', 
    'owner', 
    1
)
ON DUPLICATE KEY UPDATE
    `password_hash` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `shop_id` = @shop1_id,
    `is_active` = 1;

-- 4. shop_usersテーブルが存在する場合は、全店舗に関連付け
-- （テーブルが存在する場合のみ実行）
INSERT IGNORE INTO `shop_users` (`shop_id`, `user_id`, `role`, `is_primary`)
SELECT s.id, u.id, 'owner', CASE WHEN s.code = 'shop001' THEN 1 ELSE 0 END
FROM shops s
CROSS JOIN (SELECT id FROM users WHERE username = 'admin' LIMIT 1) u
WHERE s.code IN ('shop001', 'shop002', 'shop003', 'shop004', 'shop005', 'shop006', 'shop007', 'shop008');
```

## 確認

修正後、再度ログインを試してください：

```bash
curl -X POST http://mameq.xsrv.jp/radish/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

成功すると以下のようなレスポンスが返ります：

```json
{
  "success": true,
  "user": {
    "id": "1",
    "username": "admin",
    "name": "システム管理者",
    "email": "admin@example.com",
    "role": "owner",
    "shop": {
      "id": "1",
      "code": "shop001",
      "name": "レストラン イタリアン"
    }
  }
}
```

