# 管理画面アカウント情報

## デフォルトアカウント

データベーススキーマ（`database/schema-multi-shop.sql`）を実行すると、以下のサンプルアカウントが作成されます。

### 店舗スタッフ用アカウント

#### 店舗1（レストランA）のオーナー
- **ユーザー名**: `owner1`
- **パスワード**: `password123`
- **役割**: オーナー（owner）
- **表示名**: オーナー1
- **所属店舗**: shop001（レストランA）

#### 店舗2（カフェB）のオーナー
- **ユーザー名**: `owner2`
- **パスワード**: `password123`
- **役割**: オーナー（owner）
- **表示名**: オーナー2
- **所属店舗**: shop002（カフェB）

### マイグレーション時のデフォルトアカウント

既存システムから移行する場合（`database/migration-add-shop-id.sql`を実行した場合）：

- **ユーザー名**: `admin`
- **パスワード**: `password123`
- **役割**: オーナー（owner）
- **表示名**: 管理者
- **所属店舗**: default（デフォルト店舗）

### 複数店舗オーナー用アカウント

`database/schema-multi-shop-owner.sql`を実行すると、以下のアカウントが作成されます：

- **ユーザー名**: `multiowner`
- **パスワード**: `password123`
- **役割**: オーナー（owner）
- **表示名**: 複数店舗オーナー
- **所属店舗**: shop001（レストランA）、shop002（カフェB）
- **管理画面**: `/multi-shop/dashboard`（複数店舗統合管理画面）

## ログイン方法

### 店舗スタッフ用管理画面

1. トップページから「店舗スタッフ用」をクリック
2. または `/staff/login` に直接アクセス
3. 上記のアカウント情報でログイン
4. ログイン後、店舗選択画面が表示されます
5. 所属店舗を選択してダッシュボードへ進みます

### 弊社向け管理画面

現在、弊社向け管理画面は店舗スタッフ用のアカウントでログインできますが、将来的には専用のスーパーアドミンアカウントが必要になります。

## 権限について

### owner（オーナー）
- 店舗の全権限
- スタッフの追加・編集・削除
- スタッフの役割変更
- メニュー管理
- 注文管理
- 会計管理

### manager（管理者）
- スタッフの追加・編集
- スタッフの有効/無効切り替え
- スタッフのパスワード変更
- メニュー管理
- 注文管理

### staff（スタッフ）
- 自分の情報の閲覧・編集
- 自分のパスワード変更
- 注文管理（ステータス更新）

## パスワードの変更

ログイン後、以下のページからパスワードを変更できます：

- `/admin/users/password` - 自分のパスワード変更
- `/admin/users/{id}/password` - 他のスタッフのパスワード変更（管理者のみ）

## セキュリティ注意事項

⚠️ **重要**: 本番環境では必ず以下の対応を行ってください：

1. **デフォルトパスワードの変更**
   - 初回ログイン後、すぐにパスワードを変更してください
   - デフォルトパスワード（`password123`）は推測されやすいため危険です

2. **強力なパスワードの設定**
   - 8文字以上
   - 大文字・小文字・数字・記号を含む
   - 辞書に載っている単語を避ける

3. **アカウントの管理**
   - 不要なアカウントは削除または無効化
   - 定期的にアカウント一覧を確認

4. **ログイン履歴の確認**
   - `/admin/users` で最終ログイン日時を確認
   - 不審なアクセスがないかチェック

## 新しいアカウントの作成

### オーナー・管理者による追加

1. `/admin/users` にアクセス
2. 「スタッフを追加」ボタンをクリック
3. 必要情報を入力：
   - ユーザー名（必須）
   - パスワード（必須、6文字以上）
   - 表示名（必須）
   - メールアドレス（任意）
   - 役割（オーナーのみ設定可能）

### データベースから直接作成

phpMyAdminで以下のSQLを実行：

```sql
-- 店舗IDを取得（例: shop001のIDを取得）
SET @shop_id = (SELECT id FROM shops WHERE code = 'shop001' LIMIT 1);

-- 新しいユーザーを作成（パスワード: newpassword123）
INSERT INTO `users` (`shop_id`, `username`, `password_hash`, `name`, `role`) 
VALUES (
  @shop_id, 
  'newuser', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  '新しいユーザー', 
  'staff'
);
```

**注意**: パスワードハッシュは `password_hash()` 関数で生成する必要があります。上記のハッシュは `password123` 用です。

## パスワードハッシュの生成方法

PHPでパスワードハッシュを生成する場合：

```php
<?php
$password = 'your_password_here';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>
```

または、オンラインツールを使用して生成することもできます。

## トラブルシューティング

### ログインできない場合

1. **ユーザー名・パスワードの確認**
   - 大文字・小文字を区別します
   - スペースが含まれていないか確認

2. **アカウントが有効か確認**
   ```sql
   SELECT username, is_active FROM users WHERE username = 'owner1';
   ```
   `is_active` が `1` である必要があります

3. **店舗が有効か確認**
   ```sql
   SELECT u.username, s.is_active as shop_active 
   FROM users u 
   INNER JOIN shops s ON u.shop_id = s.id 
   WHERE u.username = 'owner1';
   ```
   店舗の `is_active` も `1` である必要があります

4. **パスワードのリセット**
   ```sql
   -- パスワードを password123 にリセット
   UPDATE users 
   SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
   WHERE username = 'owner1';
   ```

## 関連ドキュメント

- [スタッフ管理機能](./STAFF_MANAGEMENT.md)
- [データベーススキーマ](./database/README.md)
- [APIサーバー README](./api-server/README.md)

