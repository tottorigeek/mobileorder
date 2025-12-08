<?php
/**
 * 店舗カテゴリ管理API
 * GET /api/categories?shop_id={shop_id} - 店舗のカテゴリ一覧取得（認証必須）
 * GET /api/categories/{id} - カテゴリ情報取得（認証必須）
 * POST /api/categories - カテゴリ作成（認証必須、オーナー・マネージャーのみ）
 * PUT /api/categories/{id} - カテゴリ更新（認証必須、オーナー・マネージャーのみ）
 * DELETE /api/categories/{id} - カテゴリ削除（認証必須、オーナー・マネージャーのみ）
 * GET /api/categories/shop/{shop_code} - 店舗コードからカテゴリ一覧取得（公開）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析
$pathParts = explode('/', trim(str_replace('/radish/api/categories/', '', $path), '/'));

// 店舗コードからカテゴリ一覧取得（公開エンドポイント）
if (isset($pathParts[0]) && $pathParts[0] === 'shop' && isset($pathParts[1])) {
    $shopCode = $pathParts[1];
    getCategoriesByShopCode($shopCode);
    exit;
}

$categoryId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;
$shopId = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : null;

switch ($method) {
    case 'GET':
        if ($categoryId) {
            getCategory($categoryId);
        } else {
            getCategories($shopId);
        }
        break;
    
    case 'POST':
        createCategory();
        break;
    
    case 'PUT':
        if ($categoryId) {
            updateCategory($categoryId);
        } else {
            sendErrorResponse(400, 'Category ID is required');
        }
        break;
    
    case 'DELETE':
        if ($categoryId) {
            deleteCategory($categoryId);
        } else {
            sendErrorResponse(400, 'Category ID is required');
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * カテゴリ一覧取得（認証必須）
 */
function getCategories($shopId = null) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $userShopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // shop_idが指定されていない場合は認証ユーザーのshop_idを使用
        if (!$shopId) {
            $shopId = $userShopId;
        }
        
        // 権限チェック：自分の店舗のカテゴリのみ取得可能
        if ($userShopId && $shopId != $userShopId) {
            sendForbiddenError('You can only access categories from your own shop');
        }
        
        if (!$shopId) {
            sendValidationError(['shop_id' => 'Shop ID is required']);
        }
        
        $stmt = $pdo->prepare("
            SELECT sc.*, s.code as shop_code, s.name as shop_name
            FROM shop_categories sc
            INNER JOIN shops s ON sc.shop_id = s.id
            WHERE sc.shop_id = :shop_id
            ORDER BY sc.display_order ASC, sc.name ASC
        ");
        $stmt->execute([':shop_id' => $shopId]);
        $categories = $stmt->fetchAll();
        
        $result = array_map(function($category) {
            return [
                'id' => (string)$category['id'],
                'shopId' => (string)$category['shop_id'],
                'shopCode' => $category['shop_code'],
                'shopName' => $category['shop_name'],
                'code' => $category['code'],
                'name' => $category['name'],
                'displayOrder' => (int)$category['display_order'],
                'isActive' => (bool)$category['is_active'],
                'createdAt' => $category['created_at'],
                'updatedAt' => $category['updated_at']
            ];
        }, $categories);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching categories');
    }
}

/**
 * カテゴリ情報取得（認証必須）
 */
function getCategory($categoryId) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $userShopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT sc.*, s.code as shop_code, s.name as shop_name
            FROM shop_categories sc
            INNER JOIN shops s ON sc.shop_id = s.id
            WHERE sc.id = :id
        ");
        $stmt->execute([':id' => $categoryId]);
        $category = $stmt->fetch();
        
        if (!$category) {
            sendNotFoundError('Category');
        }
        
        // 権限チェック：自分の店舗のカテゴリのみ取得可能
        if ($userShopId && $category['shop_id'] != $userShopId) {
            sendForbiddenError('You can only access categories from your own shop');
        }
        
        echo json_encode([
            'id' => (string)$category['id'],
            'shopId' => (string)$category['shop_id'],
            'shopCode' => $category['shop_code'],
            'shopName' => $category['shop_name'],
            'code' => $category['code'],
            'name' => $category['name'],
            'displayOrder' => (int)$category['display_order'],
            'isActive' => (bool)$category['is_active'],
            'createdAt' => $category['created_at'],
            'updatedAt' => $category['updated_at']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching category');
    }
}

/**
 * 店舗コードからカテゴリ一覧取得（公開）
 */
function getCategoriesByShopCode($shopCode) {
    try {
        $pdo = getDbConnection();
        
        $stmt = $pdo->prepare("
            SELECT sc.*, s.code as shop_code, s.name as shop_name
            FROM shop_categories sc
            INNER JOIN shops s ON sc.shop_id = s.id
            WHERE s.code = :shop_code
            AND sc.is_active = 1
            AND s.is_active = 1
            ORDER BY sc.display_order ASC, sc.name ASC
        ");
        $stmt->execute([':shop_code' => $shopCode]);
        $categories = $stmt->fetchAll();
        
        $result = array_map(function($category) {
            return [
                'id' => (string)$category['id'],
                'shopId' => (string)$category['shop_id'],
                'shopCode' => $category['shop_code'],
                'shopName' => $category['shop_name'],
                'code' => $category['code'],
                'name' => $category['name'],
                'displayOrder' => (int)$category['display_order']
            ];
        }, $categories);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching categories by shop code');
    }
}

/**
 * カテゴリ作成（認証必須、オーナー・マネージャーのみ）
 */
function createCategory() {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        $shopId = $auth['shop_id'] ?? null;
        
        if (!$shopId) {
            sendValidationError(['shop_id' => 'Shop ID is required']);
        }
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['code']) || !isset($input['name'])) {
            sendValidationError([
                'code' => 'Category code is required',
                'name' => 'Category name is required'
            ]);
        }
        
        // カテゴリコードの重複チェック
        $checkStmt = $pdo->prepare("
            SELECT id FROM shop_categories 
            WHERE shop_id = :shop_id AND code = :code
        ");
        $checkStmt->execute([
            ':shop_id' => $shopId,
            ':code' => $input['code']
        ]);
        if ($checkStmt->fetch()) {
            sendConflictError('Category code already exists in this shop');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO shop_categories 
            (shop_id, code, name, display_order, is_active)
            VALUES 
            (:shop_id, :code, :name, :display_order, :is_active)
        ");
        
        $stmt->execute([
            ':shop_id' => $shopId,
            ':code' => $input['code'],
            ':name' => $input['name'],
            ':display_order' => isset($input['displayOrder']) ? (int)$input['displayOrder'] : 0,
            ':is_active' => isset($input['isActive']) ? ($input['isActive'] ? 1 : 0) : 1
        ]);
        
        $categoryId = $pdo->lastInsertId();
        
        // 作成したカテゴリ情報を返す
        getCategory($categoryId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'creating category');
    }
}

/**
 * カテゴリ更新（認証必須、オーナー・マネージャーのみ）
 */
function updateCategory($categoryId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        $shopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // 既存のカテゴリ情報を取得
        $existingStmt = $pdo->prepare("SELECT shop_id FROM shop_categories WHERE id = :id");
        $existingStmt->execute([':id' => $categoryId]);
        $existing = $existingStmt->fetch();
        
        if (!$existing) {
            sendNotFoundError('Category');
        }
        
        // 権限チェック：自分の店舗のカテゴリのみ更新可能
        if ($shopId && $existing['shop_id'] != $shopId) {
            sendForbiddenError('You can only update categories from your own shop');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            sendValidationError(['data' => 'Request data is required']);
        }
        
        $updates = [];
        $params = [':id' => $categoryId];
        
        if (isset($input['code'])) {
            // カテゴリコードの重複チェック（自分以外）
            $checkStmt = $pdo->prepare("
                SELECT id FROM shop_categories 
                WHERE shop_id = :shop_id AND code = :code AND id != :id
            ");
            $checkStmt->execute([
                ':shop_id' => $existing['shop_id'],
                ':code' => $input['code'],
                ':id' => $categoryId
            ]);
            if ($checkStmt->fetch()) {
                sendConflictError('Category code already exists in this shop');
            }
            
            $updates[] = "code = :code";
            $params[':code'] = $input['code'];
        }
        
        if (isset($input['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $input['name'];
        }
        
        if (isset($input['displayOrder'])) {
            $updates[] = "display_order = :display_order";
            $params[':display_order'] = (int)$input['displayOrder'];
        }
        
        if (isset($input['isActive'])) {
            $updates[] = "is_active = :is_active";
            $params[':is_active'] = $input['isActive'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            sendValidationError(['fields' => 'No fields to update']);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE shop_categories SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // 更新したカテゴリ情報を返す
        getCategory($categoryId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'updating category');
    }
}

/**
 * カテゴリ削除（認証必須、オーナー・マネージャーのみ）
 */
function deleteCategory($categoryId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        $shopId = $auth['shop_id'] ?? null;
        
        $pdo = getDbConnection();
        
        // 既存のカテゴリ情報を取得
        $existingStmt = $pdo->prepare("SELECT shop_id FROM shop_categories WHERE id = :id");
        $existingStmt->execute([':id' => $categoryId]);
        $existing = $existingStmt->fetch();
        
        if (!$existing) {
            sendNotFoundError('Category');
        }
        
        // 権限チェック：自分の店舗のカテゴリのみ削除可能
        if ($shopId && $existing['shop_id'] != $shopId) {
            sendForbiddenError('You can only delete categories from your own shop');
        }
        
        // このカテゴリを使用しているメニューがあるかチェック
        $menuStmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM menus 
            WHERE shop_id = :shop_id AND category = (
                SELECT code FROM shop_categories WHERE id = :id
            )
        ");
        $menuStmt->execute([
            ':shop_id' => $existing['shop_id'],
            ':id' => $categoryId
        ]);
        $menuCount = $menuStmt->fetch()['count'];
        
        if ($menuCount > 0) {
            sendErrorResponse(400, "Cannot delete category: {$menuCount} menu(s) are using this category");
        }
        
        // 削除（物理削除）
        $stmt = $pdo->prepare("DELETE FROM shop_categories WHERE id = :id");
        $stmt->execute([':id' => $categoryId]);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('Category');
        }
        
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting category');
    }
}

