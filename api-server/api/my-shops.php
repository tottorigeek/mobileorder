<?php
/**
 * ユーザーが所属する店舗一覧取得API
 * GET /api/my-shops - ログインユーザーが所属する全店舗を取得
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    sendErrorResponse(405, 'Method not allowed');
}

try {
    // 認証チェック
    $auth = checkAuth();
    $userId = $auth['user_id'];
    
    $pdo = getDbConnection();
    
    // shop_usersテーブルが存在するか確認
    $tableExists = false;
    try {
        $checkStmt = $pdo->query("SHOW TABLES LIKE 'shop_users'");
        $tableExists = $checkStmt->rowCount() > 0;
    } catch (PDOException $e) {
        // テーブルが存在しない場合は既存の方法で取得
    }
    
    if ($tableExists) {
        // 複数店舗対応: shop_usersテーブルから取得
        $sql = "SELECT s.*, su.role as shop_role, su.is_primary
                FROM shops s
                INNER JOIN shop_users su ON s.id = su.shop_id
                WHERE su.user_id = :user_id 
                AND s.is_active = 1
                ORDER BY su.is_primary DESC, s.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $shops = $stmt->fetchAll();
    } else {
        // 既存の方法: usersテーブルのshop_idから取得
        $sql = "SELECT s.*, u.role as shop_role
                FROM shops s
                INNER JOIN users u ON s.id = u.shop_id
                WHERE u.id = :user_id 
                AND s.is_active = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $shops = $stmt->fetchAll();
        
        // is_primaryを設定（既存データは主店舗として扱う）
        foreach ($shops as &$shop) {
            $shop['is_primary'] = 1;
        }
    }
    
    $result = array_map(function($shop) {
        return [
            'id' => (string)$shop['id'],
            'code' => $shop['code'],
            'name' => $shop['name'],
            'description' => $shop['description'],
            'address' => $shop['address'],
            'phone' => $shop['phone'],
            'email' => $shop['email'],
            'maxTables' => (int)$shop['max_tables'],
            'isActive' => (bool)$shop['is_active'],
            'shopRole' => $shop['shop_role'] ?? $shop['role'] ?? 'staff',
            'isPrimary' => isset($shop['is_primary']) ? (bool)$shop['is_primary'] : true,
            'createdAt' => $shop['created_at'],
            'updatedAt' => $shop['updated_at']
        ];
    }, $shops);
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    handleDatabaseError($e, 'fetching user shops');
}

