<?php
/**
 * メール送信テスト用スクリプト
 * パスワードリセットメールの送信をテストします
 * 使用方法: ?email=test@example.com&username=testuser&name=テストユーザー
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

try {
    // クエリパラメータから情報を取得
    $testEmail = $_GET['email'] ?? null;
    $testUsername = $_GET['username'] ?? 'testuser';
    $testName = $_GET['name'] ?? 'テストユーザー';
    
    if (!$testEmail) {
        echo json_encode([
            'error' => 'Email parameter is required',
            'message' => 'emailパラメータが必要です。例: ?email=test@example.com',
            'usage' => '?email=test@example.com&username=testuser&name=テストユーザー'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // テスト用のトークンを生成
    $testToken = bin2hex(random_bytes(32));
    
    // メール設定の確認
    $mailFrom = getEnvValue('MAIL_FROM', 'noreply@towndx.com');
    $mailFromName = getEnvValue('MAIL_FROM_NAME', 'Radish System');
    $useSMTP = getEnvValue('MAIL_USE_SMTP', 'false') === 'true';
    $smtpHost = getEnvValue('MAIL_SMTP_HOST', 'not set');
    $smtpPort = getEnvValue('MAIL_SMTP_PORT', 'not set');
    $frontendBaseUrl = getEnvValue('FRONTEND_BASE_URL', 'https://mameq.xsrv.jp');
    
    // メール送信
    $emailSent = sendPasswordResetEmail(
        $testEmail,
        $testUsername,
        $testName,
        $testToken
    );
    
    // 結果を返す
    echo json_encode([
        'success' => $emailSent,
        'message' => $emailSent 
            ? 'テストメールを送信しました。メールボックスを確認してください。' 
            : 'メールの送信に失敗しました。エラーログを確認してください。',
        'test_info' => [
            'email' => $testEmail,
            'username' => $testUsername,
            'name' => $testName,
            'token' => $testToken,
            'reset_url' => $frontendBaseUrl . '/staff/reset-password?token=' . urlencode($testToken)
        ],
        'mail_config' => [
            'from' => $mailFrom,
            'from_name' => $mailFromName,
            'use_smtp' => $useSMTP,
            'smtp_host' => $useSMTP ? $smtpHost : 'N/A (using mail())',
            'smtp_port' => $useSMTP ? $smtpPort : 'N/A (using mail())',
            'frontend_base_url' => $frontendBaseUrl
        ],
        'debug_info' => defined('DEBUG_MODE') && DEBUG_MODE ? [
            'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown',
            'debug_mode' => true,
            'note' => 'メール送信の詳細はエラーログを確認してください'
        ] : null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Exception occurred',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

