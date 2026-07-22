<?php
/**
 * Database connection – uses environment variables on Vercel / cloud hosts.
 * Falls back to local XAMPP defaults.
 */
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'computer_records';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database</title></head><body style="font-family:Arial;padding:2rem;">';
    echo '<h2>Computer Checks – Database connection failed</h2>';
    echo '<p>This deployment needs a MySQL database.</p>';
    echo '<p>Set Vercel environment variables: <code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code>.</p>';
    echo '<p style="color:#666;font-size:0.9rem;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</body></html>';
    exit;
}
