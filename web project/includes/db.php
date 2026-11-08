<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px;border:1px solid #ccc;border-radius:8px;">'
      . '<h2 style="color:#d9534f;margin-top:0;">Database Connection Error</h2>'
      . '<p>Could not connect to MySQL database <strong>' . htmlspecialchars(DB_NAME) . '</strong>.</p>'
      . '<p><strong>Quick Fix on Linux:</strong></p>'
      . '<ul>'
      . '<li>Start MySQL: <code>sudo systemctl start mysql</code></li>'
      . '<li>Import database: <code>mysql -u root -p &lt; database.sql</code></li>'
      . '<li>Check password in <code>includes/config.php</code></li>'
      . '</ul>'
      . '</div>');
}
