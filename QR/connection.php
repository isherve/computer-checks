<?php
/**
 * Database connection
 * - Local XAMPP: MySQL (default)
 * - Vercel / serverless: bundled SQLite (works without external DB)
 * - Optional remote MySQL via env: DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
 */

if (!function_exists('app_is_vercel')) {
    function app_is_vercel(): bool
    {
        return getenv('VERCEL') === '1'
            || getenv('VERCEL_ENV') !== false
            || isset($_SERVER['VERCEL'])
            || isset($_ENV['VERCEL']);
    }
}

if (!function_exists('app_pdo')) {
    function app_pdo(): PDO
    {
        $driver = getenv('DB_DRIVER') ?: '';
        $host = getenv('DB_HOST') ?: '';

        // Prefer explicit remote MySQL when configured
        if ($driver === 'mysql' || ($host !== '' && $host !== 'localhost' && $host !== '127.0.0.1')) {
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'computer_records';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        // Vercel (or DB_DRIVER=sqlite): use bundled SQLite
        if ($driver === 'sqlite' || app_is_vercel()) {
            $seed = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'computer_checks.sqlite';
            if (!is_file($seed)) {
                throw new PDOException('SQLite seed database missing at QR/data/computer_checks.sqlite');
            }

            // Vercel functions can only write under /tmp
            $runtimeDir = sys_get_temp_dir();
            $runtime = $runtimeDir . DIRECTORY_SEPARATOR . 'computer_checks.sqlite';
            if (!is_file($runtime) || filesize($runtime) === 0) {
                if (!@copy($seed, $runtime)) {
                    // Fall back to read-only seed (login works; writes may fail)
                    $runtime = $seed;
                }
            }

            $pdo = new PDO('sqlite:' . $runtime, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON');
            return $pdo;
        }

        // Local XAMPP MySQL default
        $dbname = getenv('DB_NAME') ?: 'computer_records';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $localHost = $host !== '' ? $host : 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $dsn = "mysql:host={$localHost};port={$port};dbname={$dbname};charset=utf8mb4";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $pdo = app_pdo();
    } catch (PDOException $e) {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database</title></head><body style="font-family:Arial;padding:2rem;">';
        echo '<h2>Computer Checks – Database connection failed</h2>';
        echo '<p>Could not connect to the database.</p>';
        echo '<p style="color:#666;font-size:0.9rem;">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</body></html>';
        exit;
    }
}
