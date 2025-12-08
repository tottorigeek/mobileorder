# 動作確認テストガイド

## 概要

このガイドでは、飲食店オーダーシステムの主要機能の動作確認テスト手順を説明します。

## 前提条件

1. APIサーバーが起動していること
   - URL: `https://api.towndx.com/radish/v1`
2. データベースにテスト用データが存在すること
   - テストユーザー: `seki` / `password123`
   - テスト店舗: `shop001`
3. Nuxt.jsアプリケーションが起動していること（クライアント側テストの場合）

## テスト方法

### 方法1: curlコマンドを使用（推奨）

#### 1. ログインしてトークンを取得

```bash
curl -X POST https://api.towndx.com/radish/v1/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"seki","password":"password123"}' \
  | jq '.token'
```

**期待結果**: JWTトークンが返される

#### 2. 認証状態確認

```bash
TOKEN="<上記で取得したトークン>"
curl -X GET https://api.towndx.com/radish/v1/api/auth/me \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.'
```

**期待結果**: ユーザー情報が返される

#### 3. 注文作成（認証済みユーザー）

```bash
curl -X POST https://api.towndx.com/radish/v1/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "tableNumber": "5",
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
  }' | jq '.'
```

**期待結果**: 作成された注文情報が返される（`shop_id`が含まれていることを確認）

#### 4. 注文作成（一般顧客 - QRコード経由）

```bash
curl -X POST https://api.towndx.com/radish/v1/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "shopCode": "shop001",
    "tableNumber": "6",
    "items": [
      {
        "menuId": "1",
        "menuNumber": "001",
        "menuName": "ハンバーガー",
        "quantity": 1,
        "price": 800
      }
    ],
    "totalAmount": 800
  }' | jq '.'
```

**期待結果**: 作成された注文情報が返される（認証なしでも成功）

#### 5. 注文一覧取得（認証済みユーザー）

```bash
curl -X GET https://api.towndx.com/radish/v1/api/orders \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.'
```

**期待結果**: 注文一覧が返される

#### 6. 注文一覧取得（一般顧客）

```bash
curl -X GET "https://api.towndx.com/radish/v1/api/orders?shop=shop001&tableNumber=6" \
  | jq '.'
```

**期待結果**: 該当テーブルの注文一覧が返される

#### 7. 注文ステータス更新

```bash
ORDER_ID="<作成した注文のID>"
curl -X PUT https://api.towndx.com/radish/v1/api/orders/$ORDER_ID \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status": "accepted"}' \
  | jq '.'
```

**期待結果**: 更新された注文情報が返される

#### 8. エラーハンドリング確認

```bash
# 無効なトークンでのアクセス
curl -X GET https://api.towndx.com/radish/v1/api/auth/me \
  -H "Authorization: Bearer invalid_token" \
  -w "\nHTTP_STATUS:%{http_code}"

# 期待結果: 401 Unauthorized

# 認証なしでの注文ステータス更新
curl -X PUT https://api.towndx.com/radish/v1/api/orders/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "accepted"}' \
  -w "\nHTTP_STATUS:%{http_code}"

# 期待結果: 401 Unauthorized
```

### 方法2: テストスクリプトを使用

```bash
cd api-server
chmod +x test-api.sh
./test-api.sh
```

**注意**: `jq`コマンドが必要です。インストールされていない場合は、`sudo apt-get install jq`（Ubuntu/Debian）または`brew install jq`（macOS）でインストールしてください。

### 方法3: ブラウザで確認

#### 1. ログインページ
- URL: `http://localhost:3000/staff/login` または `http://localhost:3000/company/login`
- テストユーザー: `seki` / `password123`

#### 2. メニュー一覧
- URL: `http://localhost:3000/shop/shop001`
- メニューが表示されることを確認

#### 3. 注文作成
- メニューを選択して注文
- 注文が正常に作成されることを確認

#### 4. 注文管理
- URL: `http://localhost:3000/staff/orders`
- 注文一覧が表示されることを確認
- 注文ステータスを更新できることを確認

## 確認項目チェックリスト

### 認証機能
- [ ] ログインが正常に動作する
- [ ] JWTトークンが正しく返される
- [ ] トークンがlocalStorageに保存される
- [ ] 認証状態確認（/api/auth/me）が正常に動作する
- [ ] ログアウトが正常に動作する
- [ ] 無効なトークンでアクセスすると401エラーが返される

### 注文機能
- [ ] 認証済みユーザーが注文を作成できる
- [ ] 一般顧客（QRコード経由）が注文を作成できる
- [ ] 注文に`shop_id`が正しく設定される
- [ ] 認証済みユーザーが注文一覧を取得できる
- [ ] 一般顧客がクエリパラメータで注文一覧を取得できる
- [ ] 店舗スタッフが注文ステータスを更新できる
- [ ] 一般顧客が注文ステータスを更新できない（401エラー）

### メニュー機能
- [ ] メニュー一覧が取得できる（公開）
- [ ] 店舗コードでフィルタリングできる

### ユーザー管理機能
- [ ] 管理者がユーザー一覧を取得できる
- [ ] 管理者がユーザーを作成できる
- [ ] 管理者がユーザーを更新できる
- [ ] オーナーがユーザーを削除できる
- [ ] パスワード変更が正常に動作する

### セキュリティ
- [ ] 認証なしで保護されたエンドポイントにアクセスできない
- [ ] 異なる店舗のデータにアクセスできない
- [ ] JWTトークンの有効期限が正しく機能する

## トラブルシューティング

### 問題1: ログインに失敗する

**確認事項:**
1. ユーザー名とパスワードが正しいか
2. データベースにユーザーが存在するか
3. ユーザーが有効化されているか（`is_active = 1`）
4. 店舗が有効化されているか（`is_active = 1`）

**解決方法:**
```bash
# パスワードをリセット
curl "https://api.towndx.com/radish/v1/tools/fix-admin-password.php?username=seki"
```

### 問題2: 401 Unauthorizedエラー

**確認事項:**
1. JWTトークンが正しく送信されているか
2. Authorizationヘッダーの形式が正しいか（`Bearer <token>`）
3. トークンの有効期限が切れていないか

**解決方法:**
- 再度ログインして新しいトークンを取得
- ブラウザの開発者ツールでネットワークタブを確認

### 問題3: 注文作成に失敗する

**確認事項:**
1. `shop_id`が正しく設定されているか
2. リクエストボディの形式が正しいか
3. 店舗コードが存在するか（一般顧客の場合）

**解決方法:**
- リクエストボディを確認
- データベースで店舗が存在するか確認

### 問題4: CORSエラー

**確認事項:**
1. `config.php`のCORS設定が正しいか
2. `.htaccess`の設定が正しいか

**解決方法:**
- `api-server/radish/v1/config.php`の`setJsonHeader()`関数を確認
- `api-server/radish/v1/.htaccess`の設定を確認

## テスト結果の記録

テスト実施後、以下の情報を記録してください：

- **実施日時**: _______________
- **実施者**: _______________
- **テスト環境**: _______________
- **テスト結果**: `api-server/TEST_PLAN.md`のチェックリストに記入

## 次のステップ

すべてのテストが成功したら：

1. 本番環境へのデプロイ準備
2. パフォーマンステスト
3. セキュリティ監査
4. ユーザー受け入れテスト（UAT）

