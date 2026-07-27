<?php
/**
 * Database connection
 * - Local XAMPP: MySQL (default)
 * - Vercel / serverless: bundled SQLite with optional Blob persistence
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

if (!function_exists('app_blob_token')) {
    function app_blob_token(): ?string
    {
        $token = getenv('BLOB_READ_WRITE_TOKEN');
        if ($token === false || $token === '') {
            $token = getenv('VERCEL_BLOB_READ_WRITE_TOKEN');
        }
        return ($token !== false && $token !== '') ? $token : null;
    }
}

if (!function_exists('app_sqlite_runtime_path')) {
    function app_sqlite_runtime_path(): string
    {
        if (!isset($GLOBALS['app_sqlite_runtime_path'])) {
            $GLOBALS['app_sqlite_runtime_path'] = sys_get_temp_dir()
                . DIRECTORY_SEPARATOR
                . 'computer_checks_utb.sqlite';
        }
        return $GLOBALS['app_sqlite_runtime_path'];
    }
}

if (!function_exists('app_blob_list')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function app_blob_list(string $prefix): array
    {
        $token = app_blob_token();
        if ($token === null) {
            return [];
        }

        $url = 'https://blob.vercel-storage.com?prefix=' . rawurlencode($prefix);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $code < 200 || $code >= 300) {
            return [];
        }

        $json = json_decode($body, true);
        if (!is_array($json) || !isset($json['blobs']) || !is_array($json['blobs'])) {
            return [];
        }

        return $json['blobs'];
    }
}

if (!function_exists('app_blob_download_url')) {
    function app_blob_download_url(string $blobUrl, string $targetPath): bool
    {
        $ch = curl_init($blobUrl);
        $fp = fopen($targetPath, 'wb');
        if ($fp === false) {
            curl_close($ch);
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $ok = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        return $ok !== false && $code >= 200 && $code < 300 && is_file($targetPath) && filesize($targetPath) > 0;
    }
}

if (!function_exists('app_blob_upload_sqlite')) {
    function app_blob_upload_sqlite(string $localPath): ?string
    {
        $token = app_blob_token();
        if ($token === null || !is_file($localPath)) {
            return null;
        }

        $bytes = file_get_contents($localPath);
        if ($bytes === false) {
            return null;
        }

        $ch = curl_init('https://blob.vercel-storage.com/upload');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'x-vercel-filename: computer-checks/database.sqlite',
                'x-vercel-add-random-suffix: false',
                'Content-Type: application/octet-stream',
            ],
            CURLOPT_POSTFIELDS => $bytes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $code < 200 || $code >= 300) {
            return null;
        }

        $json = json_decode($body, true);
        if (!is_array($json) || empty($json['url'])) {
            return null;
        }

        return (string)$json['url'];
    }
}

if (!function_exists('app_sqlite_bootstrap')) {
    function app_sqlite_bootstrap(string $seed): string
    {
        $runtime = app_sqlite_runtime_path();
        $loaded = false;

        $blobUrl = getenv('BLOB_DATABASE_URL');
        if ($blobUrl !== false && $blobUrl !== '') {
            $loaded = app_blob_download_url($blobUrl, $runtime);
        }

        if (!$loaded) {
            $blobs = app_blob_list('computer-checks/database.sqlite');
            if (!empty($blobs[0]['url'])) {
                $loaded = app_blob_download_url((string)$blobs[0]['url'], $runtime);
            }
        }

        if (!$loaded) {
            if (!is_file($runtime) || filesize($runtime) === 0) {
                if (!@copy($seed, $runtime)) {
                    return $seed;
                }
            }
        }

        return $runtime;
    }
}

if (!function_exists('app_db_persist')) {
  /**
   * Save SQLite changes on Vercel so new users/data appear on all requests.
   */
    function app_db_persist(): void
    {
        if (!app_is_vercel()) {
            return;
        }

        $runtime = app_sqlite_runtime_path();
        if (!is_file($runtime) || filesize($runtime) === 0) {
            return;
        }

        app_blob_upload_sqlite($runtime);
    }
}

if (!function_exists('app_apply_runtime_migrations')) {
    /**
     * Apply small data migrations to the live SQLite copy.
     */
    function app_apply_runtime_migrations(PDO $pdo): bool
    {
        $changed = false;

        $updates = [
            [
                'nid' => '21UTB03769',
                'names' => 'Ahadibash Alice Cecile',
                'email' => 'ahadibashalicecile@gmail.com',
            ],
            [
                'nid' => '21UTB06834',
                'names' => 'Hitiyise Mupenzi',
                'email' => 'hitiyisemupenzi@gmail.com',
            ],
        ];

        $select = $pdo->prepare('SELECT names, email FROM users WHERE nid = :nid LIMIT 1');
        $update = $pdo->prepare('UPDATE users SET names = :names, email = :email WHERE nid = :nid');

        foreach ($updates as $row) {
            $select->execute([':nid' => $row['nid']]);
            $current = $select->fetch(PDO::FETCH_ASSOC);
            if (!$current) {
                continue;
            }
            if (($current['names'] ?? '') !== $row['names'] || ($current['email'] ?? '') !== $row['email']) {
                $update->execute([
                    ':names' => $row['names'],
                    ':email' => $row['email'],
                    ':nid' => $row['nid'],
                ]);
                $changed = true;
            }
        }

        return $changed;
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

            $runtime = app_sqlite_bootstrap($seed);

            $pdo = new PDO('sqlite:' . $runtime, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON');
            if (app_apply_runtime_migrations($pdo)) {
                app_db_persist();
            }
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

if (!isset($GLOBALS['pdo']) || !($GLOBALS['pdo'] instanceof PDO)) {
    try {
        $GLOBALS['pdo'] = app_pdo();
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
$pdo = $GLOBALS['pdo'];
