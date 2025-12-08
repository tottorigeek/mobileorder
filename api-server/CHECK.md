# API動作確認チェックリスト

## 確認手順

### 1. データベース接続テスト

以下のURLにアクセスして、データベース接続を確認してください：

```
http://mameq.xsrv.jp/radish/test-db.php
```

**確認項目:**
- ✓ データベース接続が成功しているか
- ✓ テーブル（menus, orders, order_items）が存在するか
- ✓ メニューデータが存在するか

### 2. メニューAPIのデバッグ

以下のURLにアクセスして、メニューAPIの動作を確認してください：

```
http://mameq.xsrv.jp/radish/api/debug-menus.php
```

**確認項目:**
- ✓ テーブルが存在するか
- ✓ データが取得できるか
- ✓ エラーメッセージがないか

### 3. メニューAPIの動作確認

以下のURLにアクセスして、JSONが返ることを確認してください：

```
http://mameq.xsrv.jp/radish/api/menus
```

**期待される結果:**
```json
[
  {
    "id": "1",
    "number": "001",
    "name": "ハンバーガー",
    "description": "ジューシーなハンバーガー",
    "price": 800,
    "category": "food",
    "imageUrl": null,
    "isAvailable": true,
    "isRecommended": true
  },
  ...
]
```

### 4. 注文APIの動作確認

以下のURLにアクセスして、JSONが返ることを確認してください：

```
http://mameq.xsrv.jp/radish/api/orders
```

**期待される結果:**
```json
[]
```
（まだ注文がない場合は空の配列）

## 問題が発生した場合

### エラー: "Table does not exist"

**解決方法:**
1. phpMyAdminでデータベースを選択
2. `database/schema-multi-shop.sql`ファイルをインポート
3. テーブルが作成されたか確認

### エラー: "Failed to fetch menus"

**確認事項:**
1. `debug-menus.php`でエラーの詳細を確認
2. テーブルにデータが存在するか確認
3. `is_available`カラムの値が正しいか確認（1または0）

### エラー: データベース接続エラー

**確認事項:**
1. `config.php`の設定が正しいか
2. データベースが作成されているか
3. ユーザーに適切な権限が付与されているか

## 次のステップ

すべての確認が完了したら：

1. Nuxtアプリを起動して動作確認
2. メニュー一覧が表示されるか確認
3. 注文機能が動作するか確認

