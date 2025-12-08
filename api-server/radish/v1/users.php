<?php
/**
 * ユーザー管理API（店舗スタッフ管理）
 * GET /api/users - ユーザー一覧取得（認証必要、オーナー・管理者のみ）
 * POST /api/users - ユーザー追加（認証必要、オーナー・管理者のみ）
 * GET /api/users/{id} - ユーザー情報取得（認証必要）
 * PUT /api/users/{id} - ユーザー情報更新（認証必要）
 * PUT /api/users/{id}/password - パスワード変更（認証必要）
 * DELETE /api/users/{id} - ユーザー削除（認証必要、オーナーのみ）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析（index.php経由で呼び出される場合を考慮）
// v1/index.php経由で呼び出された場合、環境変数から残りのパスを取得
if (isset($_ENV['USERS_REMAINING_PATH']) && !empty($_ENV['USERS_REMAINING_PATH'])) {
    $path = $_ENV['USERS_REMAINING_PATH'];
} else {
    // 直接呼び出された場合、/radish/v1/users/ または /radish/api/users/ を削除
    $path = preg_replace('#^/radish/(v1|api)/users/#', '', $path);
}
$path = trim($path, '/');
$pathParts = explode('/', $path);
$userId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? $pathParts[0] : null;
$action = isset($pathParts[1]) ? $pathParts[1] : null;

switch ($method) {
    case 'GET':
        if ($userId) {
            getUser($userId);
        } else {
            getUsers();
        }
        break;
    
    case 'POST':
        createUser();
        break;
    
    case 'PUT':
        if ($userId && $action === 'password') {
            changePassword($userId);
        } elseif ($userId) {
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
 * ユーザー一覧取得（店舗内のスタッフ一覧）
 */
function getUsers() {
    try {
        // 認証チェック（オーナー・管理者のみ）
        $auth = checkPermission('manager');
        $shopId = $auth['shop_id'];
        
        $pdo = getDbConnection();
        
        $sql = "SELECT id, shop_id, username, name, role, email, is_active, last_login_at, created_at, updated_at
                FROM users 
                WHERE shop_id = :shop_id 
                ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':shop_id' => $shopId]);
        $users = $stmt->fetchAll();
        
        $result = array_map(function($user) {
            return [
                'id' => (string)$user['id'],
                'shopId' => (string)$user['shop_id'],
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
        handleDatabaseError($e, 'fetching users');
    }
}

/**
 * ユーザー情報取得（単一）
 */
function getUser($userId) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $shopId = $auth['shop_id'];
        
        // 自分の情報、または同じ店舗のユーザー情報のみ取得可能
        if ($userId != $auth['user_id']) {
            checkPermission('manager');
        }
        
        $pdo = getDbConnection();
        
        $sql = "SELECT id, shop_id, username, name, role, email, is_active, last_login_at, created_at, updated_at
                FROM users 
                WHERE id = :id AND shop_id = :shop_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $userId,
            ':shop_id' => $shopId
        ]);
        $user = $stmt->fetch();
        
        if (!$user) {
            sendNotFoundError('User');
        }
        
        echo json_encode([
            'id' => (string)$user['id'],
            'shopId' => (string)$user['shop_id'],
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
 * ユーザー作成（スタッフ追加）
 */
function createUser() {
    try {
        // 認証チェック（オーナー・管理者のみ）
        $auth = checkPermission('manager');
        $shopId = $auth['shop_id'];
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['username']) || !isset($input['password']) || !isset($input['name'])) {
            sendValidationError([
                'username' => 'Username is required',
                'password' => 'Password is required',
                'name' => 'Name is required'
            ]);
        }
        
        // ユーザー名の重複チェック
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $checkStmt->execute([':username' => $input['username']]);
        if ($checkStmt->fetch()) {
            sendConflictError('Username already exists');
        }
        
        // パスワードハッシュ化
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // 役割の設定（デフォルトはstaff、オーナーのみmanagerを設定可能）
        $role = $input['role'] ?? 'staff';
        if ($auth['role'] !== 'owner' && $role !== 'staff') {
            $role = 'staff';
        }
        
        $sql = "INSERT INTO users (shop_id, username, password_hash, name, role, email, is_active, created_at, updated_at)
                VALUES (:shop_id, :username, :password_hash, :name, :role, :email, 1, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':shop_id' => $shopId,
            ':username' => $input['username'],
            ':password_hash' => $passwordHash,
            ':name' => $input['name'],
            ':role' => $role,
            ':email' => $input['email'] ?? null
        ]);
        
        $newUserId = $pdo->lastInsertId();
        
        // 作成したユーザー情報を返す
        getUser($newUserId);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'creating user');
    }
}

/**
 * ユーザー情報更新（メールアドレス変更など）
 */
function updateUser($userId) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $shopId = $auth['shop_id'];
        
        // 自分の情報、または管理者権限が必要
        if ($userId != $auth['user_id']) {
            checkPermission('manager');
        }
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            return;
        }
        
        // 更新可能なフィールド
        $updates = [];
        $params = [':id' => $userId, ':shop_id' => $shopId];
        
        if (isset($input['name'])) {
            $updates[] = "name = :name";
            $params[':name'] = $input['name'];
        }
        
        if (isset($input['email'])) {
            $updates[] = "email = :email";
            $params[':email'] = $input['email'];
        }
        
        // 役割の変更はオーナーのみ
        if (isset($input['role']) && $auth['role'] === 'owner') {
            $updates[] = "role = :role";
            $params[':role'] = $input['role'];
        }
        
        // 有効フラグの変更は管理者のみ
        if (isset($input['isActive']) && $auth['role'] !== 'staff') {
            $updates[] = "is_active = :is_active";
            $params[':is_active'] = $input['isActive'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            sendValidationError(['fields' => 'No fields to update']);
        }
        
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " 
                WHERE id = :id AND shop_id = :shop_id";
        
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
 * パスワード変更
 */
function changePassword($userId) {
    try {
        // 認証チェック
        $auth = checkAuth();
        $shopId = $auth['shop_id'];
        
        // 自分のパスワードのみ変更可能（管理者は他のユーザーのパスワードも変更可能）
        if ($userId != $auth['user_id']) {
            checkPermission('manager');
        }
        
        $pdo = getDbConnection();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['currentPassword']) || !isset($input['newPassword'])) {
            sendValidationError([
                'currentPassword' => 'Current password is required',
                'newPassword' => 'New password is required'
            ]);
        }
        
        // 自分のパスワード変更の場合は現在のパスワードを確認
        if ($userId == $auth['user_id']) {
            $userStmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id AND shop_id = :shop_id");
            $userStmt->execute([':id' => $userId, ':shop_id' => $shopId]);
            $user = $userStmt->fetch();
            
            if (!$user || !password_verify($input['currentPassword'], $user['password_hash'])) {
                sendUnauthorizedError('Current password is incorrect');
            }
        }
        
        // 新しいパスワードのハッシュ化
        $newPasswordHash = password_hash($input['newPassword'], PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password_hash = :password_hash, updated_at = NOW() 
                WHERE id = :id AND shop_id = :shop_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':password_hash' => $newPasswordHash,
            ':id' => $userId,
            ':shop_id' => $shopId
        ]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'changing password');
    }
}

/**
 * ユーザー削除
 */
function deleteUser($userId) {
    try {
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        $shopId = $auth['shop_id'];
        
        // 自分自身は削除できない
        if ($userId == $auth['user_id']) {
            sendErrorResponse(400, 'Cannot delete yourself');
        }
        
        $pdo = getDbConnection();
        
        $sql = "DELETE FROM users WHERE id = :id AND shop_id = :shop_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $userId,
            ':shop_id' => $shopId
        ]);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('User');
        }
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting user');
    }
}

