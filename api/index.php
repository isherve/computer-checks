<?php
/**
 * Vercel PHP front controller – routes requests to files in /QR
 */
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = $uri === null ? '/' : $uri;
$uri = rawurldecode($uri);

// Normalize
if ($uri === '' || $uri === '/') {
    $target = 'index.php';
} else {
    $target = ltrim($uri, '/');
}

// Block sensitive paths
$blocked = ['vendor/', 'composer.', '.env', 'Database/', 'phpqr/'];
foreach ($blocked as $b) {
    if (stripos($target, $b) !== false) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'QR' . DIRECTORY_SEPARATOR;
$file = realpath($root . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target));

// Directory → index.php
if ($file && is_dir($file)) {
    $file = realpath($file . DIRECTORY_SEPARATOR . 'index.php');
}

// Allow extension-less → .php
if ((!$file || !is_file($file)) && substr($target, -4) !== '.php') {
    $try = realpath($root . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target) . '.php');
    if ($try && is_file($try)) {
        $file = $try;
    }
}

// Default document
if (!$file || !is_file($file) || strpos($file, realpath($root)) !== 0) {
    // try as php page name
    $fallback = realpath($root . 'index.php');
    if (preg_match('/\.php$/i', $target)) {
        http_response_code(404);
        echo 'Page not found: ' . htmlspecialchars($target);
        exit;
    }
    $file = $fallback;
}

// Serve static assets directly if they slipped through
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$static = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'];
if (in_array($ext, $static, true)) {
    $types = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'map' => 'application/json',
    ];
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    readfile($file);
    exit;
}

chdir(dirname($file));
require $file;
