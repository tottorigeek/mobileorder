<?php
/**
 * メール送信診断ツール
 * メール送信の設定と動作を確認します
 */

require_once __DIR__ . '/../config.php';

setJsonHeader();

$diagnostics = [];
$executionLog = [];

// ログをキャプチャする関数
$captureLog = function($message) use (&$executionLog) {
    $executionLog[] = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message
    ];
    error_log($message);
};

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

// 3. メール送信テスト
$testEmail = $_GET['test_email'] ?? null;
$testResult = null;
$smtpSteps = [];

if ($testEmail) {
    $testSubject = 'メール送信テスト - ' . date('Y-m-d H:i:s');
    $testMessage = '<html><body><h1>メール送信テスト</h1><p>このメールは診断ツールからのテストメールです。</p></body></html>';
    
    $useSMTP = getEnvValue('MAIL_USE_SMTP', 'false') === 'true';
    
    if ($useSMTP) {
        // SMTP接続の詳細テスト
        $smtpHost = getEnvValue('MAIL_SMTP_HOST', 'localhost');
        $smtpPort = (int)getEnvValue('MAIL_SMTP_PORT', 587);
        $smtpSecure = getEnvValue('MAIL_SMTP_SECURE', 'tls');
        $smtpUser = getEnvValue('MAIL_SMTP_USER', '');
        $smtpPass = getEnvValue('MAIL_SMTP_PASS', '');
        $smtpAuth = getEnvValue('MAIL_SMTP_AUTH', 'true') === 'true';
        
        $host = ($smtpSecure === 'ssl') ? 'ssl://' . $smtpHost : $smtpHost;
        
        $captureLog("=== SMTP診断開始 ===");
        $captureLog("接続先: {$host}:{$smtpPort}");
        
        // ステップ1: 接続テスト
        $socket = @fsockopen($host, $smtpPort, $errno, $errstr, 10);
        $smtpSteps[] = [
            'step' => '1. 接続',
            'command' => "fsockopen({$host}, {$smtpPort})",
            'success' => $socket !== false,
            'response' => $socket ? '接続成功' : "失敗: {$errstr} ({$errno})",
            'error' => $socket ? null : "{$errstr} ({$errno})"
        ];
        
        if (!$socket) {
            $captureLog("接続失敗: {$errstr} ({$errno})");
        } else {
            $captureLog("接続成功");
            
            // レスポンスを読み取る関数
            $readResponse = function($socket, $expectedCode = null) use (&$smtpSteps, &$captureLog) {
                $response = '';
                while ($line = fgets($socket, 515)) {
                    $response .= $line;
                    if (substr($line, 3, 1) === ' ') {
                        break;
                    }
                }
                $trimmed = trim($response);
                $captureLog("レスポンス: {$trimmed}");
                return $trimmed;
            };
            
            // SMTPコマンドを送信する関数
            $sendCommand = function($socket, $command, $expectedCode, $stepName) use ($readResponse, &$smtpSteps, &$captureLog) {
                $captureLog("コマンド送信: {$command}");
                fputs($socket, $command . "\r\n");
                $response = $readResponse($socket);
                $success = $expectedCode === null || strpos($response, $expectedCode) === 0;
                
                $smtpSteps[] = [
                    'step' => $stepName,
                    'command' => $command,
                    'expected' => $expectedCode,
                    'success' => $success,
                    'response' => $response,
                    'error' => $success ? null : "期待値: {$expectedCode}, 実際: {$response}"
                ];
                
                return $success;
            };
            
            // ステップ2: 初期応答
            $initialResponse = $readResponse($socket);
            $smtpSteps[] = [
                'step' => '2. 初期応答',
                'command' => 'サーバーからの応答待機',
                'success' => strpos($initialResponse, '220') === 0,
                'response' => $initialResponse,
                'error' => strpos($initialResponse, '220') === 0 ? null : '220で始まる応答がありません'
            ];
            
            if (strpos($initialResponse, '220') !== 0) {
                fclose($socket);
                $captureLog("初期応答エラー: {$initialResponse}");
            } else {
                // ステップ3: EHLO
                $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
                if (!$sendCommand($socket, "EHLO {$hostname}", '250', '3. EHLO')) {
                    fclose($socket);
                    $captureLog("EHLO失敗");
                } else {
                    // ステップ4: STARTTLS (tlsの場合)
                    if ($smtpSecure === 'tls') {
                        if (!$sendCommand($socket, 'STARTTLS', '220', '4. STARTTLS')) {
                            fclose($socket);
                            $captureLog("STARTTLS失敗");
                        } else {
                            $captureLog("TLS暗号化を開始");
                            $cryptoResult = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                            $smtpSteps[] = [
                                'step' => '5. TLS暗号化',
                                'command' => 'stream_socket_enable_crypto()',
                                'success' => $cryptoResult !== false,
                                'response' => $cryptoResult ? 'TLS暗号化成功' : 'TLS暗号化失敗',
                                'error' => $cryptoResult ? null : 'TLS暗号化に失敗しました'
                            ];
                            
                            if (!$cryptoResult) {
                                $cryptoError = error_get_last();
                                $captureLog("TLS暗号化失敗: " . ($cryptoError ? $cryptoError['message'] : 'Unknown'));
                                fclose($socket);
                            } else {
                                $captureLog("TLS暗号化成功");
                                // EHLO再送信
                                $sendCommand($socket, "EHLO {$hostname}", '250', '6. EHLO (TLS後)');
                            }
                        }
                    }
                    
                    // ステップ5: 認証
                    if ($socket && $smtpAuth && !empty($smtpUser) && !empty($smtpPass)) {
                        $captureLog("認証開始");
                        if (!$sendCommand($socket, 'AUTH LOGIN', '334', '7. AUTH LOGIN')) {
                            fclose($socket);
                            $captureLog("AUTH LOGIN失敗");
                        } else {
                            if (!$sendCommand($socket, base64_encode($smtpUser), '334', '8. ユーザー名送信')) {
                                fclose($socket);
                                $captureLog("ユーザー名認証失敗");
                            } else {
                                if (!$sendCommand($socket, base64_encode($smtpPass), '235', '9. パスワード送信')) {
                                    fclose($socket);
                                    $captureLog("パスワード認証失敗");
                                } else {
                                    $captureLog("認証成功");
                                    
                                    // ステップ6: メール送信テスト
                                    $from = getEnvValue('MAIL_FROM', 'noreply@towndx.com');
                                    $fromName = getEnvValue('MAIL_FROM_NAME', 'Radish System');
                                    
                                    if ($sendCommand($socket, "MAIL FROM: <{$from}>", '250', '10. MAIL FROM')) {
                                        if ($sendCommand($socket, "RCPT TO: <{$testEmail}>", '250', '11. RCPT TO')) {
                                            if ($sendCommand($socket, 'DATA', '354', '12. DATA')) {
                                                $emailData = "From: {$fromName} <{$from}>\r\n";
                                                $emailData .= "To: <{$testEmail}>\r\n";
                                                $emailData .= "Subject: {$testSubject}\r\n";
                                                $emailData .= "MIME-Version: 1.0\r\n";
                                                $emailData .= "Content-Type: text/html; charset=UTF-8\r\n";
                                                $emailData .= "\r\n{$testMessage}\r\n.\r\n";
                                                
                                                fputs($socket, $emailData);
                                                $finalResponse = $readResponse($socket);
                                                $sendSuccess = strpos($finalResponse, '250') === 0;
                                                
                                                $smtpSteps[] = [
                                                    'step' => '13. メールデータ送信',
                                                    'command' => 'DATA送信',
                                                    'success' => $sendSuccess,
                                                    'response' => $finalResponse,
                                                    'error' => $sendSuccess ? null : 'メール送信がサーバーに拒否されました'
                                                ];
                                                
                                                if ($sendSuccess) {
                                                    $captureLog("メール送信成功");
                                                } else {
                                                    $captureLog("メール送信失敗: {$finalResponse}");
                                                }
                                                
                                                $sendCommand($socket, 'QUIT', '221', '14. QUIT');
                                            }
                                        }
                                    }
                                    
                                    if ($socket) {
                                        fclose($socket);
                                    }
                                }
                            }
                        }
                    } elseif ($socket && (!$smtpAuth || empty($smtpUser) || empty($smtpPass))) {
                        $captureLog("認証スキップ（設定されていないか無効）");
                    }
                }
            }
        }
        
        // 実際のメール送信関数も試す
        $from = getEnvValue('MAIL_FROM', 'noreply@towndx.com');
        $captureLog("=== sendEmail関数による送信テスト ===");
        $emailSent = sendEmail($testEmail, $testSubject, $testMessage, $from);
        
        $testResult = [
            'method' => 'SMTP',
            'smtp_steps' => $smtpSteps,
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
                'auth' => $smtpAuth
            ],
            'note' => $emailSent 
                ? 'メール送信は成功しました。メールボックス（スパムフォルダも含む）を確認してください。' 
                : 'メール送信に失敗しました。上記のSMTPステップを確認してください。'
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

// 4. 推奨事項
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
$diagnostics['execution_log'] = $executionLog;

echo json_encode([
    'success' => true,
    'diagnostics' => $diagnostics,
    'usage' => '?test_email=your-email@example.com で実際にメールを送信してテストできます'
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
