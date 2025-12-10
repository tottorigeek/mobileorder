<?php
/**
 * メール送信診断ツール
 * メール送信の設定と動作を確認します
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$diagnostics = [];

// 1. PHP設定の確認
$diagnostics['php_config'] = [
    'mail_function_exists' => function_exists('mail'),
    'sendmail_path' => ini_get('sendmail_path'),
    'smtp' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_from' => ini_get('sendmail_from'),
];

// 2. 環境変数の確認
$diagnostics['env_config'] = [
    'MAIL_FROM' => getEnvValue('MAIL_FROM', 'not set'),
    'MAIL_FROM_NAME' => getEnvValue('MAIL_FROM_NAME', 'not set'),
    'MAIL_USE_SMTP' => getEnvValue('MAIL_USE_SMTP', 'false'),
    'MAIL_SMTP_HOST' => getEnvValue('MAIL_SMTP_HOST', 'not set'),
    'MAIL_SMTP_PORT' => getEnvValue('MAIL_SMTP_PORT', 'not set'),
    'MAIL_SMTP_SECURE' => getEnvValue('MAIL_SMTP_SECURE', 'not set'),
    'MAIL_SMTP_USER' => getEnvValue('MAIL_SMTP_USER', 'not set') ? 'set (hidden)' : 'not set',
    'MAIL_SMTP_PASS' => getEnvValue('MAIL_SMTP_PASS', 'not set') ? 'set (hidden)' : 'not set',
    'MAIL_SMTP_AUTH' => getEnvValue('MAIL_SMTP_AUTH', 'not set'),
];

// 3. メール送信テスト（実際には送信しない）
$testEmail = $_GET['test_email'] ?? null;
$testResult = null;

if ($testEmail) {
    // 簡単なテストメールを送信
    $testSubject = 'メール送信テスト - ' . date('Y-m-d H:i:s');
    $testMessage = '<html><body><h1>メール送信テスト</h1><p>このメールは診断ツールからのテストメールです。</p></body></html>';
    
    $useSMTP = getEnvValue('MAIL_USE_SMTP', 'false') === 'true';
    
    if ($useSMTP) {
        // SMTP接続テストと実際のメール送信テスト
        $smtpHost = getEnvValue('MAIL_SMTP_HOST', 'localhost');
        $smtpPort = (int)getEnvValue('MAIL_SMTP_PORT', 587);
        $smtpSecure = getEnvValue('MAIL_SMTP_SECURE', 'tls');
        $smtpUser = getEnvValue('MAIL_SMTP_USER', '');
        $smtpPass = getEnvValue('MAIL_SMTP_PASS', '');
        
        $host = ($smtpSecure === 'ssl') ? 'ssl://' . $smtpHost : $smtpHost;
        $socket = @fsockopen($host, $smtpPort, $errno, $errstr, 5);
        
        $connectionTest = [
            'success' => $socket ? true : false,
            'error' => $socket ? null : "{$errstr} ({$errno})",
            'host' => $host,
            'port' => $smtpPort
        ];
        
        if ($socket) {
            fclose($socket);
        }
        
        // 実際にメールを送信してみる
        $from = getEnvValue('MAIL_FROM', 'noreply@towndx.com');
        $fromName = getEnvValue('MAIL_FROM_NAME', 'Radish System');
        $emailSent = sendEmail($testEmail, $testSubject, $testMessage, $from);
        
        $testResult = [
            'method' => 'SMTP',
            'connection_test' => $connectionTest,
            'email_send_test' => [
                'success' => $emailSent,
                'from' => $from,
                'to' => $testEmail,
                'subject' => $testSubject
            ],
            'smtp_config' => [
                'host' => $smtpHost,
                'port' => $smtpPort,
                'secure' => $smtpSecure,
                'user' => $smtpUser ? 'set' : 'not set',
                'auth' => getEnvValue('MAIL_SMTP_AUTH', 'true')
            ],
            'note' => $emailSent 
                ? 'メール送信は成功しました。メールボックス（スパムフォルダも含む）を確認してください。' 
                : 'メール送信に失敗しました。エラーログを確認してください。'
        ];
    } else {
        // mail()関数のテスト
        $from = getEnvValue('MAIL_FROM', 'noreply@towndx.com');
        $fromName = getEnvValue('MAIL_FROM_NAME', 'Radish System');
        
        $headers = [
            'From: ' . ($fromName ? "{$fromName} <{$from}>" : $from),
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        $headersString = implode("\r\n", $headers);
        
        // 実際にメールを送信してみる
        $result = @mail($testEmail, $testSubject, $testMessage, $headersString);
        
        $testResult = [
            'method' => 'mail()',
            'send_result' => $result,
            'from' => $from,
            'to' => $testEmail,
            'subject' => $testSubject,
            'note' => $result 
                ? 'mail()関数はtrueを返しましたが、実際にメールが届くかはサーバー設定によります。' 
                : 'mail()関数がfalseを返しました。サーバーのメール設定を確認してください。'
        ];
    }
}

// 4. エラーログの確認（最後のメール関連エラー）
$diagnostics['error_log_note'] = 'エラーログはサーバーのログファイルを確認してください。';

// 5. 推奨事項
$recommendations = [];
if (getEnvValue('MAIL_USE_SMTP', 'false') !== 'true') {
    $recommendations[] = 'PHPのmail()関数を使用しています。共有サーバーではメールが届かない場合があります。SMTPの使用を推奨します。';
}
if (empty(ini_get('sendmail_path'))) {
    $recommendations[] = 'sendmail_pathが設定されていません。サーバー管理者に確認してください。';
}
if (getEnvValue('MAIL_SMTP_HOST', '') === 'not set' || getEnvValue('MAIL_SMTP_HOST', '') === '') {
    $recommendations[] = 'SMTP設定が不完全です。.envファイルでSMTP設定を確認してください。';
}

$diagnostics['recommendations'] = $recommendations;
$diagnostics['test_result'] = $testResult;

echo json_encode([
    'success' => true,
    'diagnostics' => $diagnostics,
    'usage' => '?test_email=your-email@example.com で実際にメールを送信してテストできます'
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

