# エラーハンドリング統一仕様

## 概要

すべてのAPIエンドポイントで統一的なエラーハンドリングを実装しています。

## エラーレスポンス形式

### 基本形式

```json
{
  "error": "エラーメッセージ",
  "status": 400
}
```

### デバッグモード有効時

```json
{
  "error": "エラーメッセージ",
  "status": 400,
  "details": {
    "validation_errors": {
      "field1": "エラーメッセージ1",
      "field2": "エラーメッセージ2"
    }
  },
  "debug": {
    "message": "例外メッセージ",
    "file": "/path/to/file.php",
    "line": 123
  }
}
```

## エラーハンドリング関数

### `sendErrorResponse($statusCode, $message, $details = [], $exception = null)`

汎用的なエラーレスポンスを送信します。

**パラメータ:**
- `$statusCode`: HTTPステータスコード（例: 400, 404, 500）
- `$message`: エラーメッセージ
- `$details`: 追加の詳細情報（オプション）
- `$exception`: 例外オブジェクト（ログ用、オプション）

**使用例:**
```php
sendErrorResponse(400, 'Invalid request');
```

### `sendValidationError($errors)`

バリデーションエラーを送信します（HTTP 400）。

**パラメータ:**
- `$errors`: バリデーションエラーの配列

**使用例:**
```php
sendValidationError([
    'username' => 'Username is required',
    'password' => 'Password is required'
]);
```

### `sendUnauthorizedError($message = 'Unauthorized')`

認証エラーを送信します（HTTP 401）。

**使用例:**
```php
sendUnauthorizedError('Invalid credentials');
```

### `sendForbiddenError($message = 'Forbidden')`

権限エラーを送信します（HTTP 403）。

**使用例:**
```php
sendForbiddenError('Insufficient permissions');
```

### `sendNotFoundError($resource = 'Resource')`

リソース未検出エラーを送信します（HTTP 404）。

**使用例:**
```php
sendNotFoundError('User');
// 結果: "User not found"
```

### `sendConflictError($message)`

競合エラーを送信します（HTTP 409）。

**使用例:**
```php
sendConflictError('Username already exists');
```

### `sendServerError($message = 'Internal server error', $exception = null)`

サーバーエラーを送信します（HTTP 500）。

**使用例:**
```php
try {
    // 処理
} catch (PDOException $e) {
    sendServerError('Failed to process request', $e);
}
```

### `handleDatabaseError($e, $operation)`

データベースエラーを処理します。

**パラメータ:**
- `$e`: PDOExceptionオブジェクト
- `$operation`: 操作名（例: 'fetching orders', 'creating user'）

**使用例:**
```php
try {
    // データベース操作
} catch (PDOException $e) {
    handleDatabaseError($e, 'fetching orders');
}
```

## HTTPステータスコードの使い分け

| ステータスコード | 用途 | 関数 |
|----------------|------|------|
| 400 | バリデーションエラー、不正なリクエスト | `sendValidationError()`, `sendErrorResponse(400, ...)` |
| 401 | 認証エラー | `sendUnauthorizedError()` |
| 403 | 権限エラー | `sendForbiddenError()` |
| 404 | リソース未検出 | `sendNotFoundError()` |
| 405 | メソッドが許可されていない | `sendErrorResponse(405, ...)` |
| 409 | 競合（重複など） | `sendConflictError()` |
| 500 | サーバーエラー | `sendServerError()`, `handleDatabaseError()` |

## エラーログ

- すべての例外は自動的にエラーログに記録されます
- ログ形式: `[YYYY-MM-DD HH:MM:SS] ExceptionClass: message in file:line`
- デバッグモードが有効な場合のみ、スタックトレース情報がレスポンスに含まれます

## 実装例

### バリデーションエラー

```php
if (!$input || !isset($input['username'])) {
    sendValidationError(['username' => 'Username is required']);
}
```

### 認証エラー

```php
if (!$user || !password_verify($password, $user['password_hash'])) {
    sendUnauthorizedError('Invalid credentials');
}
```

### リソース未検出

```php
$user = $stmt->fetch();
if (!$user) {
    sendNotFoundError('User');
}
```

### データベースエラー

```php
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    handleDatabaseError($e, 'fetching users');
}
```

## 注意事項

1. すべてのエラーハンドリング関数は`exit`を呼び出すため、関数の実行が終了します
2. デバッグモードが無効な場合、詳細なエラー情報はクライアントに返されません（セキュリティ上の理由）
3. 本番環境では`DEBUG_MODE`を`false`に設定してください

