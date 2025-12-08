<?php
/**
 * エラーログ管理API
 * GET /api/error-logs - エラーログ一覧取得（認証必要、オーナーのみ）
 * GET /api/error-logs/{id} - エラーログ詳細取得（認証必要、オーナーのみ）
 * DELETE /api/error-logs/{id} - エラーログ削除（認証必要、オーナーのみ）
 * DELETE /api/error-logs - エラーログ一括削除（認証必要、オーナーのみ）
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// パスの解析
$pathParts = explode('/', trim(str_replace('/radish/api/error-logs/', '', $path), '/'));
$logId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? $pathParts[0] : null;

switch ($method) {
    case 'GET':
        if ($logId) {
            getErrorLog($logId);
        } else {
            getErrorLogs();
        }
        break;
    
    case 'DELETE':
        if ($logId) {
            deleteErrorLog($logId);
        } else {
            deleteAllErrorLogs();
        }
        break;
    
    default:
        sendErrorResponse(405, 'Method not allowed');
        break;
}

/**
 * エラーログ一覧取得
 */
function getErrorLogs() {
    try {
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
        $pdo = getDbConnection();
        
        // クエリパラメータを取得
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 50;
        $offset = ($page - 1) * $limit;
        
        $level = isset($_GET['level']) ? $_GET['level'] : null;
        $shopId = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : null;
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        
        // WHERE句の構築
        $where = [];
        $params = [];
        
        if ($level && in_array($level, ['error', 'warning', 'info', 'debug'])) {
            $where[] = "level = :level";
            $params[':level'] = $level;
        }
        
        if ($shopId) {
            $where[] = "shop_id = :shop_id";
            $params[':shop_id'] = $shopId;
        }
        
        if ($startDate) {
            $where[] = "created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // 総件数を取得
        $countSql = "SELECT COUNT(*) as total FROM error_logs $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // エラーログ一覧を取得
        $sql = "SELECT el.id, el.level, el.message, el.file, el.line, el.trace, 
                       el.user_id, el.shop_id, el.request_method, el.request_uri, el.ip_address, el.created_at,
                       u.name as user_name, u.username as user_username,
                       s.name as shop_name, s.code as shop_code
                FROM error_logs el
                LEFT JOIN users u ON el.user_id = u.id
                LEFT JOIN shops s ON el.shop_id = s.id
                $whereClause
                ORDER BY el.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        
        // パラメータをバインド
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $logs = $stmt->fetchAll();
        
        $result = array_map(function($log) {
            $trace = null;
            if ($log['trace']) {
                $trace = json_decode($log['trace'], true);
            }
            
            return [
                'id' => (string)$log['id'],
                'level' => $log['level'],
                'message' => $log['message'],
                'file' => $log['file'],
                'line' => $log['line'] ? (int)$log['line'] : null,
                'trace' => $trace,
                'userId' => $log['user_id'] ? (string)$log['user_id'] : null,
                'user' => $log['user_id'] ? [
                    'id' => (string)$log['user_id'],
                    'name' => $log['user_name'],
                    'username' => $log['user_username']
                ] : null,
                'shopId' => $log['shop_id'] ? (string)$log['shop_id'] : null,
                'shop' => $log['shop_id'] ? [
                    'id' => (string)$log['shop_id'],
                    'name' => $log['shop_name'],
                    'code' => $log['shop_code']
                ] : null,
                'requestMethod' => $log['request_method'],
                'requestUri' => $log['request_uri'],
                'ipAddress' => $log['ip_address'],
                'createdAt' => $log['created_at']
            ];
        }, $logs);
        
        echo json_encode([
            'logs' => $result,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'totalPages' => ceil($total / $limit)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching error logs');
    }
}

/**
 * エラーログ詳細取得
 */
function getErrorLog($logId) {
    try {
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
        $pdo = getDbConnection();
        
        $sql = "SELECT el.id, el.level, el.message, el.file, el.line, el.trace, 
                       el.user_id, el.shop_id, el.request_method, el.request_uri, el.ip_address, el.created_at,
                       u.name as user_name, u.username as user_username,
                       s.name as shop_name, s.code as shop_code
                FROM error_logs el
                LEFT JOIN users u ON el.user_id = u.id
                LEFT JOIN shops s ON el.shop_id = s.id
                WHERE el.id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $logId]);
        $log = $stmt->fetch();
        
        if (!$log) {
            sendNotFoundError('Error log');
        }
        
        $trace = null;
        if ($log['trace']) {
            $trace = json_decode($log['trace'], true);
        }
        
        echo json_encode([
            'id' => (string)$log['id'],
            'level' => $log['level'],
            'message' => $log['message'],
            'file' => $log['file'],
            'line' => $log['line'] ? (int)$log['line'] : null,
            'trace' => $trace,
            'userId' => $log['user_id'] ? (string)$log['user_id'] : null,
            'user' => $log['user_id'] ? [
                'id' => (string)$log['user_id'],
                'name' => $log['user_name'],
                'username' => $log['user_username']
            ] : null,
            'shopId' => $log['shop_id'] ? (string)$log['shop_id'] : null,
            'shop' => $log['shop_id'] ? [
                'id' => (string)$log['shop_id'],
                'name' => $log['shop_name'],
                'code' => $log['shop_code']
            ] : null,
            'requestMethod' => $log['request_method'],
            'requestUri' => $log['request_uri'],
            'ipAddress' => $log['ip_address'],
            'createdAt' => $log['created_at']
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'fetching error log');
    }
}

/**
 * エラーログ削除（単一）
 */
function deleteErrorLog($logId) {
    try {
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
        $pdo = getDbConnection();
        
        $sql = "DELETE FROM error_logs WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $logId]);
        
        if ($stmt->rowCount() === 0) {
            sendNotFoundError('Error log');
        }
        
        echo json_encode(['success' => true, 'message' => 'Error log deleted successfully']);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting error log');
    }
}

/**
 * エラーログ一括削除
 */
function deleteAllErrorLogs() {
    try {
        // 認証チェック（オーナーのみ）
        $auth = checkPermission('owner');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // 削除条件を取得
        $level = isset($input['level']) ? $input['level'] : null;
        $shopId = isset($input['shop_id']) ? intval($input['shop_id']) : null;
        $startDate = isset($input['start_date']) ? $input['start_date'] : null;
        $endDate = isset($input['end_date']) ? $input['end_date'] : null;
        $olderThan = isset($input['older_than_days']) ? intval($input['older_than_days']) : null;
        
        $pdo = getDbConnection();
        
        // WHERE句の構築
        $where = [];
        $params = [];
        
        if ($level && in_array($level, ['error', 'warning', 'info', 'debug'])) {
            $where[] = "level = :level";
            $params[':level'] = $level;
        }
        
        if ($shopId) {
            $where[] = "shop_id = :shop_id";
            $params[':shop_id'] = $shopId;
        }
        
        if ($startDate) {
            $where[] = "created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        if ($olderThan) {
            $where[] = "created_at < DATE_SUB(NOW(), INTERVAL :older_than DAY)";
            $params[':older_than'] = $olderThan;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // 削除実行
        $sql = "DELETE FROM error_logs $whereClause";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $deletedCount = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => "Deleted {$deletedCount} error log(s)",
            'deletedCount' => $deletedCount
        ]);
        
    } catch (PDOException $e) {
        handleDatabaseError($e, 'deleting error logs');
    }
}

