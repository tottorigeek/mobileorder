<?php
/**
 * 会社ユーザー管理API（全店舗のユーザー管理）
 * GET /api/company-users - 全店舗のユーザー一覧取得（認証必要、オーナー・マネージャーのみ）
 * GET /api/company-users/{id} - ユーザー情報取得（認証必要、オーナー・マネージャーのみ）
 * PUT /api/company-users/{id} - ユーザー情報更新（認証必要、オーナー・マネージャーのみ）
 * DELETE /api/company-users/{id} - ユーザー削除（認証必要、オーナーのみ）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析
$pathParts = explode('/', trim(str_replace('/radish/api/company-users/', '', $path), '/'));
$userId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? $pathParts[0] : null;

switch ($method) {
    case 'GET':
        if ($userId) {
            getUser($userId);
        } else {
            getUsers();
        }
        break;
    
    case 'PUT':
        if ($userId) {
            updateUser($userId);
        } else {
            sendErrorResponse(400, 'User ID required');
        }
        break;
    
    case 'DELETE':
        if ($userId) {
            deleteUser($userId);
        } else {
            sendErrorResponse(400, 'User ID required');
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * 全店舗のユーザー一覧取得
 */
function getUsers() {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        
        $pdo = getDbConnection();
        
        // 全店舗のユーザーを取得（店舗情報も含む）
        $sql = "SELECT u.id, u.shop_id, u.username, u.name, u.role, u.email, u.is_active, u.last_login_at, u.created_at, u.updated_at,
                       s.code as shop_code, s.name as shop_name
                FROM users u
                LEFT JOIN shops s ON u.shop_id = s.id
                ORDER BY s.name ASC, u.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $result = array_map(function($user) {
            return [
                'id' => (string)$user['id'],
                'shopId' => (string)$user['shop_id'],
                'shop' => $user['shop_id'] ? [
                    'id' => (string)$user['shop_id'],
                    'code' => $user['shop_code'],
                    'name' => $user['shop_name']
                ] : null,
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role'],
                'email' => $user['email'] ?? null,
                'isActive' => (bool)$user['is_active'],
                'lastLoginAt' => $user['last_login_at'],
                'createdAt' => $user['created_at'],
                'updatedAt' => $user['updated_at']
            ];
        }, $users);
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching all users');
    }
}

/**
 * ユーザー情報取得（単一）
 */
function getUser($userId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        
        $pdo = getDbConnection();
        
        $sql = "SELECT u.id, u.shop_id, u.username, u.name, u.role, u.email, u.is_active, u.last_login_at, u.created_at, u.updated_at,
                       s.code as shop_code, s.name as shop_name
                FROM users u
                LEFT JOIN shops s ON u.shop_id = s.id
                WHERE u.id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            sendNotFoundError('User');
        }
        
        echo json_encode([
            'id' => (string)$user['id'],
            'shopId' => (string)$user['shop_id'],
            'shop' => $user['shop_id'] ? [
                'id' => (string)$user['shop_id'],
                'code' => $user['shop_code'],
                'name' => $user['shop_name']
            ] : null,
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
            'email' => $user['email'] ?? null,
            'isActive' => (bool)$user['is_active'],
            'lastLoginAt' => $user['last_login_at'],
            'createdAt' => $user['created_at'],
            'updatedAt' => $user['updated_at']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching user');
    }
}

/**
 * ユーザー情報更新
 */
function updateUser($userId) {
    try {
        // 認証チェック（オーナー・マネージャーのみ）
        $auth = checkPermission(['owner', 'manager']);
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }
        
        // 更新可能なフィールド
        $updates = [];
        $params = [':id' => $userId];
        
        if (isset($input['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $input['name'];
        }
        
        if (isset($input['email'])) {
            $updates[] = "email = :email";
            $params[':email'] = $input['email'];
        }
        
        // 役割の変更（自分自身の役割変更は不可）
        if (isset($input['role'])) {
            // 自分自身の役割は変更できない
            if ($userId == $auth['user_id']) {
                sendErrorResponse(400, 'Cannot change your own role');
            }
            $updates[] = "role = :role";
            $params[':role'] = $input['role'];
        }
        
        // 有効フラグの変更（自分自身の有効フラグ変更は不可）
        if (isset($input['isActive'])) {
            // 自分自身の有効フラグは変更できない
            if ($userId == $auth['user_id']) {
                sendErrorResponse(400, 'Cannot change your own active status');
            }
            $updates[] = "is_active = :is_active";
            $params[':is_active'] = $input['isActive'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            sendValidationError(['fields' => 'No fields to update']);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        // 更新したユーザー情報を返す
        getUser($userId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'updating user');
    }
}

/**
 * ユーザー削除
 */
function deleteUser($userId) {
    try {
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
        // 自分自身は削除できない
        if ($userId == $auth['user_id']) {
            sendErrorResponse(400, 'Cannot delete yourself');
        }
        
        $pdo = getDbConnection();
        
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('User');
        }
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting user');
    }
}

