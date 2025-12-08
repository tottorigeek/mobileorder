# 動作確認テスト計画

## テスト対象機能

### 1. 認証機能
- [ ] ログイン（JWTトークン取得）
- [ ] ログアウト
- [ ] 認証状態確認（/api/auth/me）
- [ ] パスワード変更

### 2. 注文機能
- [ ] 注文作成（認証済みユーザー）
- [ ] 注文作成（一般顧客 - QRコード経由）
- [ ] 注文一覧取得（認証済みユーザー）
- [ ] 注文一覧取得（一般顧客 - クエリパラメータ）
- [ ] 注文取得（単一）
- [ ] 注文ステータス更新（店舗スタッフのみ）

### 3. メニュー機能
- [ ] メニュー一覧取得（公開）
- [ ] メニュー一覧取得（店舗コード指定）

### 4. ユーザー管理機能
- [ ] ユーザー一覧取得（管理者のみ）
- [ ] ユーザー作成（管理者のみ）
- [ ] ユーザー更新（管理者のみ）
- [ ] ユーザー削除（オーナーのみ）

### 5. 店舗管理機能
- [ ] 店舗一覧取得（公開）
- [ ] 所属店舗一覧取得（認証済みユーザー）

## テスト手順

### 準備
1. テスト用ユーザーが存在することを確認
   - ユーザー名: `seki`
   - パスワード: `password123`
   - ロール: `owner`

2. テスト用店舗が存在することを確認
   - 店舗コード: `shop001`

### テスト1: ログイン機能
```bash
# ログイン
curl -X POST http://mameq.xsrv.jp/radish/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "seki",
    "password": "password123"
  }'

# 期待結果: 200 OK, tokenとuser情報が返される
```

### テスト2: 認証状態確認
```bash
# 認証状態確認（トークンを使用）
curl -X GET http://mameq.xsrv.jp/radish/api/auth/me \
  -H "Authorization: Bearer <TOKEN>"

# 期待結果: 200 OK, ユーザー情報が返される
```

### テスト3: 注文作成（認証済みユーザー）
```bash
# 注文作成（認証済み）
curl -X POST http://mameq.xsrv.jp/radish/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
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
  }'

# 期待結果: 200 OK, 作成された注文情報が返される
```

### テスト4: 注文作成（一般顧客 - QRコード経由）
```bash
# 注文作成（一般顧客）
curl -X POST http://mameq.xsrv.jp/radish/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "shopCode": "shop001",
    "tableNumber": "5",
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
  }'

# 期待結果: 200 OK, 作成された注文情報が返される
```

### テスト5: 注文一覧取得（認証済みユーザー）
```bash
# 注文一覧取得（認証済み）
curl -X GET http://mameq.xsrv.jp/radish/api/orders \
  -H "Authorization: Bearer <TOKEN>"

# 期待結果: 200 OK, 注文一覧が返される
```

### テスト6: 注文一覧取得（一般顧客）
```bash
# 注文一覧取得（一般顧客）
curl -X GET "http://mameq.xsrv.jp/radish/api/orders?shop=shop001&tableNumber=5"

# 期待結果: 200 OK, 該当テーブルの注文一覧が返される
```

### テスト7: 注文ステータス更新
```bash
# 注文ステータス更新（認証済みユーザー）
curl -X PUT http://mameq.xsrv.jp/radish/api/orders/<ORDER_ID> \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "status": "accepted"
  }'

# 期待結果: 200 OK, 更新された注文情報が返される
```

### テスト8: メニュー一覧取得
```bash
# メニュー一覧取得（店舗コード指定）
curl -X GET "http://mameq.xsrv.jp/radish/api/menus?shop=shop001"

# 期待結果: 200 OK, メニュー一覧が返される
```

### テスト9: パスワード変更
```bash
# パスワード変更
curl -X PUT http://mameq.xsrv.jp/radish/api/users/<USER_ID>/password \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{
    "currentPassword": "password123",
    "newPassword": "newpassword123"
  }'

# 期待結果: 200 OK, 成功メッセージが返される
```

### テスト10: エラーハンドリング
```bash
# 無効なトークンでのアクセス
curl -X GET http://mameq.xsrv.jp/radish/api/auth/me \
  -H "Authorization: Bearer invalid_token"

# 期待結果: 401 Unauthorized

# 認証なしでの注文ステータス更新
curl -X PUT http://mameq.xsrv.jp/radish/api/orders/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "accepted"}'

# 期待結果: 401 Unauthorized

# 無効な店舗コードでの注文作成
curl -X POST http://mameq.xsrv.jp/radish/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "shopCode": "invalid_shop",
    "tableNumber": "5",
    "items": [],
    "totalAmount": 0
  }'

# 期待結果: 404 Not Found
```

## テスト結果記録

### 実施日時
- 日時: _______________
- 実施者: _______________

### テスト結果
| テスト番号 | テスト項目 | 結果 | 備考 |
|-----------|----------|------|------|
| 1 | ログイン機能 | ☐ | |
| 2 | 認証状態確認 | ☐ | |
| 3 | 注文作成（認証済み） | ☐ | |
| 4 | 注文作成（一般顧客） | ☐ | |
| 5 | 注文一覧取得（認証済み） | ☐ | |
| 6 | 注文一覧取得（一般顧客） | ☐ | |
| 7 | 注文ステータス更新 | ☐ | |
| 8 | メニュー一覧取得 | ☐ | |
| 9 | パスワード変更 | ☐ | |
| 10 | エラーハンドリング | ☐ | |

## 注意事項

1. テスト前に、データベースにテスト用データが存在することを確認してください
2. テスト用トークンは、テスト1で取得したトークンを使用してください
3. テスト後は、必要に応じてテストデータを削除してください
4. 本番環境でテストする場合は、既存データに影響を与えないよう注意してください

