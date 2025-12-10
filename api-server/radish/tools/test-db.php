<?php
/**
 * データベース接続テスト用ファイル
 * http://mameq.xsrv.jp/radish/test-db.php でアクセス
 */

// config.phpから設定を読み込む
require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データベース接続テスト</title>
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
        .success {
            color: #155724;
            background: #d4edda;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>データベース接続テスト</h1>
        
        <?php
        // 設定情報の表示
        echo '<div class="info">';
        echo '<h3>設定情報</h3>';
        echo '<table>';
        echo '<tr><th>項目</th><th>値</th></tr>';
        echo '<tr><td>DB_HOST</td><td>' . htmlspecialchars(DB_HOST) . '</td></tr>';
        echo '<tr><td>DB_NAME</td><td>' . htmlspecialchars(DB_NAME) . '</td></tr>';
        echo '<tr><td>DB_USER</td><td>' . htmlspecialchars(DB_USER) . '</td></tr>';
        echo '<tr><td>DB_PASS</td><td>' . (DB_PASS ? '***（設定済み）' : '未設定') . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        // データベース接続テスト
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            echo '<div class="success">';
            echo '<h3>✓ データベース接続成功！</h3>';
            echo '</div>';
            
            // テーブル一覧を取得
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="info">';
            echo '<h3>テーブル一覧</h3>';
            if (count($tables) > 0) {
                echo '<ul>';
                foreach ($tables as $table) {
                    echo '<li>' . htmlspecialchars($table) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>テーブルが存在しません。database.sqlをインポートしてください。</p>';
            }
            echo '</div>';
            
            // メニューテーブルの確認
            if (in_array('menus', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM menus");
                $count = $stmt->fetchColumn();
                
                echo '<div class="info">';
                echo '<h3>メニューテーブル</h3>';
                echo '<p>レコード数: ' . $count . '件</p>';
                
                if ($count > 0) {
                    $stmt = $pdo->query("SELECT * FROM menus LIMIT 5");
                    $menus = $stmt->fetchAll();
                    
                    echo '<table>';
                    echo '<tr><th>ID</th><th>番号</th><th>名前</th><th>価格</th><th>カテゴリ</th></tr>';
                    foreach ($menus as $menu) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($menu['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($menu['number']) . '</td>';
                        echo '<td>' . htmlspecialchars($menu['name']) . '</td>';
                        echo '<td>¥' . number_format($menu['price']) . '</td>';
                        echo '<td>' . htmlspecialchars($menu['category']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '<h3>✗ メニューテーブルが存在しません</h3>';
                echo '<p>database.sqlをインポートしてください。</p>';
                echo '</div>';
            }
            
            // 注文テーブルの確認
            if (in_array('orders', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
                $count = $stmt->fetchColumn();
                
                echo '<div class="info">';
                echo '<h3>注文テーブル</h3>';
                echo '<p>レコード数: ' . $count . '件</p>';
                echo '</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<h3>✗ データベース接続エラー</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<h4>確認事項:</h4>';
            echo '<ul>';
            echo '<li>DB_HOSTが正しいか確認（エックスサーバーの場合、通常は「localhost」）</li>';
            echo '<li>DB_NAMEが正しいか確認（サーバーパネルで確認）</li>';
            echo '<li>DB_USERが正しいか確認（サーバーパネルで確認）</li>';
            echo '<li>DB_PASSが正しいか確認（サーバーパネルで確認）</li>';
            echo '<li>データベースが作成されているか確認</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div class="info">
            <h3>次のステップ</h3>
            <ul>
                <li>データベース接続が成功したら、<a href="api/menus">メニューAPI</a>をテストしてください</li>
                <li>エラーが発生した場合は、<a href="TROUBLESHOOTING.md">トラブルシューティングガイド</a>を参照してください</li>
            </ul>
        </div>
    </div>
</body>
</html>

