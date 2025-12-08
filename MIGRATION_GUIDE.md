# 複数店舗管理システムへの移行ガイド

## 概要

既存の単一店舗システムから複数店舗管理システムへの移行手順です。

## 移行手順

### Phase 1: データベースの移行

1. **既存データのバックアップ**
   ```sql
   -- 既存のテーブルをバックアップ
   CREATE TABLE menus_backup AS SELECT * FROM menus;
   CREATE TABLE orders_backup AS SELECT * FROM orders;
   CREATE TABLE order_items_backup AS SELECT * FROM order_items;
   ```

2. **新しいスキーマの適用**
   - `api-server/database-multi-shop.sql`を実行
   - 新しいテーブル（shops, users）が作成される

3. **既存データの移行**
   ```sql
   -- デフォルト店舗を作成
   INSERT INTO shops (code, name, description, max_tables) 
   VALUES ('default', 'デフォルト店舗', '既存データ用', 20);
   
   SET @default_shop_id = LAST_INSERT_ID();
   
   -- メニューデータの移行
   UPDATE menus SET shop_id = @default_shop_id;
   
   -- 注文データの移行
   UPDATE orders SET shop_id = @default_shop_id;
   ```

### Phase 2: APIファイルの更新

1. **config.phpの更新**
   - `api-server/config-multi-shop.php`の内容で`config.php`を置き換え
   - データベース接続情報を設定

2. **APIファイルの更新**
   - `api-server/api/menus.php` - 店舗IDフィルタリング追加済み
   - `api-server/api/orders.php` - 店舗IDフィルタリング追加済み
   - `api-server/api/index.php` - shops, authエンドポイント追加済み

3. **新規APIファイルの追加**
   - `api-server/api/shops.php` - 店舗API
   - `api-server/api/auth.php` - 認証API

### Phase 3: Nuxtアプリの更新

1. **型定義の更新**
   - `types/multi-shop.ts`を`types/index.ts`に統合

2. **ストアの追加**
   - `stores/shop.ts` - 店舗管理ストア
   - `stores/auth.ts` - 認証ストア

3. **ページの追加・更新**
   - `pages/shop-select.vue` - 店舗選択ページ
   - `pages/admin/dashboard.vue` - 管理ダッシュボード
   - `pages/customer/index.vue` - 店舗コード対応
   - `pages/staff/login.vue` - 認証API対応

### Phase 4: 動作確認

1. **店舗選択機能**
   - `/shop-select`で店舗一覧が表示される
   - 店舗を選択して顧客画面に遷移できる

2. **メニュー表示**
   - 選択した店舗のメニューが表示される

3. **注文機能**
   - 店舗ごとの注文が作成される

4. **認証機能**
   - スタッフログインが動作する
   - 店舗ごとの注文管理ができる

## 注意事項

1. **既存データの保護**
   - 移行前に必ずバックアップを取得
   - テスト環境で移行を確認

2. **URL構造の変更**
   - 顧客側: `/customer?shop=shop001`
   - スタッフ側: `/staff/login?shop=shop001`

3. **セッション管理**
   - 店舗情報はローカルストレージに保存
   - 認証情報もローカルストレージに保存

## ロールバック手順

問題が発生した場合のロールバック：

1. バックアップテーブルからデータを復元
2. 旧バージョンのAPIファイルに戻す
3. Nuxtアプリも旧バージョンに戻す

