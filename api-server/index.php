<?php
/**
 * エントリーポイント（オプション）
 * APIの動作確認用
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>モバイルオーダーシステム API</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .endpoint {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
            border-radius: 4px;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            margin-right: 10px;
        }
        .get { background: #2196F3; color: white; }
        .post { background: #4CAF50; color: white; }
        .put { background: #FF9800; color: white; }
        code {
            background: #e8e8e8;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .status {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>モバイルオーダーシステム API</h1>
        
        <p>APIサーバーが正常に動作しています。</p>
        
        <h2>利用可能なエンドポイント</h2>
        
        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/menus</strong>
            <p>メニュー一覧を取得します。</p>
            <p><a href="api/menus" target="_blank">テスト: api/menus</a></p>
        </div>
        
        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/orders</strong>
            <p>注文一覧を取得します。</p>
            <p><a href="api/orders" target="_blank">テスト: api/orders</a></p>
        </div>
        
        <div class="endpoint">
            <span class="method post">POST</span>
            <strong>/api/orders</strong>
            <p>新しい注文を作成します。</p>
        </div>
        
        <div class="endpoint">
            <span class="method put">PUT</span>
            <strong>/api/orders/{id}</strong>
            <p>注文のステータスを更新します。</p>
        </div>
        
        <h2>接続状態</h2>
        <?php
        require_once __DIR__ . '/config.php';
        
        try {
            $pdo = getDbConnection();
            echo '<div class="status success">';
            echo '✓ データベース接続成功';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="status error">';
            echo '✗ データベース接続エラー: ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <h2>設定情報</h2>
        <ul>
            <li>APIベースURL: <code>http://mameq.xsrv.jp/radish/api</code></li>
            <li>データベース: <?php echo defined('DB_NAME') ? DB_NAME : '未設定'; ?></li>
        </ul>
    </div>
</body>
</html>

