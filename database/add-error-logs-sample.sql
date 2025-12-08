-- エラーログサンプルデータ
-- エックスサーバーのphpMyAdminで実行してください
-- 注意: 実際のusersテーブルとshopsテーブルに存在するIDを使用してください

SET NAMES utf8mb4;

-- 既存のサンプルデータを削除（オプション）
-- DELETE FROM error_logs WHERE id > 0;

-- 店舗IDとユーザーIDを取得（存在する場合）
SET @shop1_id = (SELECT id FROM shops LIMIT 1);
SET @shop2_id = (SELECT id FROM shops ORDER BY id LIMIT 1 OFFSET 1);
SET @user1_id = (SELECT id FROM users LIMIT 1);
SET @user2_id = (SELECT id FROM users ORDER BY id LIMIT 1 OFFSET 1);
SET @user3_id = (SELECT id FROM users ORDER BY id LIMIT 1 OFFSET 2);

-- エラーログサンプルデータの挿入
INSERT INTO `error_logs` (`level`, `environment`, `message`, `file`, `line`, `trace`, `user_id`, `shop_id`, `request_method`, `request_uri`, `ip_address`, `created_at`) VALUES
-- エラーレベル: error
('error', 'development', 'データベース接続エラー: Connection refused', '/api-server/config.php', 45, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/config.php', 'line', 45, 'function', 'getDbConnection', 'class', NULL),
        JSON_OBJECT('file', '/api-server/api/menus.php', 'line', 23, 'function', 'getMenus', 'class', NULL),
        JSON_OBJECT('file', '/api-server/index.php', 'line', 12, 'function', NULL, 'class', NULL)
    )
), @user1_id, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 2 DAY)),

('error', 'development', 'SQL構文エラー: Unknown column \'menu_name\' in \'field list\'', '/api-server/api/menus.php', 67, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/menus.php', 'line', 67, 'function', 'getMenus', 'class', NULL),
        JSON_OBJECT('file', '/api-server/index.php', 'line', 12, 'function', NULL, 'class', NULL)
    )
), @user2_id, @shop1_id, 'GET', '/api/menus', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 27 HOUR)),

('error', 'production', '認証トークンが無効です', '/api-server/config.php', 123, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/config.php', 'line', 123, 'function', 'checkAuth', 'class', NULL),
        JSON_OBJECT('file', '/api-server/api/orders.php', 'line', 34, 'function', 'createOrder', 'class', NULL)
    )
), NULL, @shop1_id, 'POST', '/api/orders', '203.0.113.45', DATE_SUB(NOW(), INTERVAL 29 HOUR)),

('error', 'production', 'ファイルが見つかりません: /var/www/html/uploads/menu_image_123.jpg', '/api-server/api/menus.php', 145, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/menus.php', 'line', 145, 'function', 'updateMenu', 'class', NULL),
        JSON_OBJECT('file', '/api-server/index.php', 'line', 15, 'function', NULL, 'class', NULL)
    )
), @user3_id, @shop2_id, 'PUT', '/api/menus/15', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 32 HOUR)),

('error', 'development', 'メモリ不足エラー: Allowed memory size of 134217728 bytes exhausted', '/api-server/api/orders.php', 234, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/orders.php', 'line', 234, 'function', 'getOrders', 'class', NULL),
        JSON_OBJECT('file', '/api-server/index.php', 'line', 12, 'function', NULL, 'class', NULL)
    )
), @user1_id, @shop1_id, 'GET', '/api/orders?limit=10000', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 12 HOUR)),

-- エラーレベル: warning
('warning', 'development', '非推奨の関数が使用されています: mysql_connect()', '/api-server/config.php', 89, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/config.php', 'line', 89, 'function', 'getDbConnection', 'class', NULL)
    )
), @user2_id, @shop1_id, 'GET', '/api/shops', '192.168.1.103', DATE_SUB(NOW(), INTERVAL 6 HOUR)),

('warning', 'production', 'リクエストパラメータが不正です: limit=abc', '/api-server/api/menus.php', 45, NULL, NULL, @shop1_id, 'GET', '/api/menus?limit=abc', '192.168.1.104', DATE_SUB(NOW(), INTERVAL 4 HOUR)),

('warning', 'development', 'セッションタイムアウトが近づいています', '/api-server/config.php', 156, NULL, @user1_id, @shop1_id, 'POST', '/api/orders', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 2 HOUR)),

('warning', 'production', '大量のデータがリクエストされました: 5000件', '/api-server/api/orders.php', 78, NULL, @user2_id, @shop2_id, 'GET', '/api/orders?limit=5000', '192.168.1.105', DATE_SUB(NOW(), INTERVAL 1 HOUR)),

('warning', 'production', 'CSRFトークンの検証に失敗しました', '/api-server/config.php', 178, NULL, NULL, @shop1_id, 'POST', '/api/menus', '203.0.113.46', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),

-- エラーレベル: info
('info', 'production', 'ユーザーログイン成功', '/api-server/api/auth.php', 45, NULL, @user1_id, @shop1_id, 'POST', '/api/auth/login', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 DAY)),

('info', 'development', 'メニューが更新されました: ID 15', '/api-server/api/menus.php', 123, NULL, @user3_id, @shop2_id, 'PUT', '/api/menus/15', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 18 HOUR)),

('info', 'production', '注文が作成されました: 注文番号 ORD-2024-001234', '/api-server/api/orders.php', 156, NULL, @user1_id, @shop1_id, 'POST', '/api/orders', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 15 HOUR)),

('info', 'production', 'バックアップ処理が開始されました', '/api-server/tools/backup.php', 12, NULL, NULL, NULL, 'POST', '/tools/backup', '192.168.1.1', DATE_SUB(NOW(), INTERVAL 10 HOUR)),

('info', 'development', 'キャッシュがクリアされました', '/api-server/config.php', 234, NULL, @user2_id, @shop1_id, 'POST', '/api/cache/clear', '192.168.1.103', DATE_SUB(NOW(), INTERVAL 8 HOUR)),

-- エラーレベル: debug
('debug', 'development', 'SQLクエリ実行: SELECT * FROM menus WHERE shop_id = 1', '/api-server/api/menus.php', 67, NULL, NULL, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 5 HOUR)),

('debug', 'development', 'リクエストパラメータ: {"table_id": "5", "items": [...]}', '/api-server/api/orders.php', 89, NULL, @user1_id, @shop1_id, 'POST', '/api/orders', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 3 HOUR)),

('debug', 'development', '認証トークン検証成功: user_id=1, role=manager', '/api-server/config.php', 123, NULL, @user1_id, @shop1_id, 'GET', '/api/users', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 HOUR)),

('debug', 'production', 'セッション情報: session_id=abc123def456', '/api-server/config.php', 156, NULL, @user2_id, @shop2_id, 'GET', '/api/shops/2', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),

('debug', 'development', 'レスポンス生成完了: 200 OK, データ件数: 25', '/api-server/api/menus.php', 145, NULL, NULL, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 20 MINUTE)),

-- 複数の店舗・ユーザーにまたがるエラー例
('error', 'production', '店舗情報の取得に失敗しました: Shop not found', '/api-server/api/shops.php', 89, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/shops.php', 'line', 89, 'function', 'getShop', 'class', NULL)
    )
), NULL, NULL, 'GET', '/api/shops/999', '192.168.1.106', DATE_SUB(NOW(), INTERVAL 3 DAY)),

('warning', 'production', 'ユーザーが存在しない店舗にアクセスしようとしました', '/api-server/config.php', 234, NULL, @user1_id, NULL, 'GET', '/api/shops/999/menus', '192.168.1.107', DATE_SUB(NOW(), INTERVAL 54 HOUR)),

('error', 'production', '権限が不足しています: この操作にはowner権限が必要です', '/api-server/config.php', 178, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/config.php', 'line', 178, 'function', 'checkPermission', 'class', NULL),
        JSON_OBJECT('file', '/api-server/api/users.php', 'line', 123, 'function', 'deleteUser', 'class', NULL)
    )
), @user3_id, @shop1_id, 'DELETE', '/api/users/10', '192.168.1.108', DATE_SUB(NOW(), INTERVAL 51 HOUR)),

('info', 'production', 'ユーザーがログアウトしました', '/api-server/api/auth.php', 67, NULL, @user1_id, @shop1_id, 'POST', '/api/auth/logout', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 36 HOUR)),

('error', 'production', '外部API呼び出しエラー: Payment gateway timeout', '/api-server/api/payments.php', 234, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/payments.php', 'line', 234, 'function', 'processPayment', 'class', NULL),
        JSON_OBJECT('file', '/api-server/index.php', 'line', 18, 'function', NULL, 'class', NULL)
    )
), @user2_id, @shop1_id, 'POST', '/api/payments', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 32 HOUR)),

-- 最近のエラー（過去1時間以内）
('error', 'development', 'JSONデコードエラー: Syntax error', '/api-server/api/orders.php', 45, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/orders.php', 'line', 45, 'function', 'createOrder', 'class', NULL)
    )
), @user1_id, @shop1_id, 'POST', '/api/orders', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 50 MINUTE)),

('warning', 'production', 'レート制限に達しました: 100リクエスト/分', '/api-server/config.php', 267, NULL, @user1_id, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 40 MINUTE)),

('info', 'development', '新しいメニューが追加されました: カレーライス', '/api-server/api/menus.php', 189, NULL, @user3_id, @shop2_id, 'POST', '/api/menus', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 25 MINUTE)),

('debug', 'development', 'キャッシュヒット: menus_shop_1', '/api-server/api/menus.php', 34, NULL, NULL, @shop1_id, 'GET', '/api/menus', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),

('error', 'production', 'ファイルアップロードエラー: ファイルサイズが大きすぎます (10MB)', '/api-server/api/menus.php', 234, JSON_OBJECT(
    'frames', JSON_ARRAY(
        JSON_OBJECT('file', '/api-server/api/menus.php', 'line', 234, 'function', 'updateMenu', 'class', NULL)
    )
), @user3_id, @shop2_id, 'PUT', '/api/menus/20', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 5 MINUTE));

-- 確認用クエリ
-- SELECT level, COUNT(*) as count FROM error_logs GROUP BY level;
-- SELECT * FROM error_logs ORDER BY created_at DESC LIMIT 10;

