# APIサーバー動作状況

## ✅ 動作確認完了

### メニューAPI
- **エンドポイント**: `http://mameq.xsrv.jp/radish/api/menus`
- **ステータス**: ✅ 正常動作
- **メニュー数**: 6件

### 注文API
- **エンドポイント**: `http://mameq.xsrv.jp/radish/api/orders`
- **ステータス**: ✅ 正常動作
- **機能**:
  - GET `/api/orders` - 注文一覧取得 ✅
  - GET `/api/orders/{id}` - 注文取得（単一） ✅
  - POST `/api/orders` - 注文作成 ✅
  - PUT `/api/orders/{id}` - 注文ステータス更新 ✅

## データベース

- **接続**: ✅ 正常
- **テーブル**: 
  - `menus` ✅
  - `orders` ✅
  - `order_items` ✅
- **サンプルデータ**: 6件のメニューデータが登録済み

## 次のステップ

1. ✅ APIサーバーの動作確認完了
2. ⏭ Nuxtアプリとの連携確認
3. ⏭ 実際の注文フローのテスト

## テスト方法

### メニュー取得のテスト
```bash
curl http://mameq.xsrv.jp/radish/api/menus
```

### 注文作成のテスト
```bash
curl -X POST http://mameq.xsrv.jp/radish/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "tableNumber": "1",
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
```

### 注文一覧取得のテスト
```bash
curl http://mameq.xsrv.jp/radish/api/orders
```

