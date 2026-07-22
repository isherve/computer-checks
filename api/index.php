<?php
/**
 * Vercel PHP front controller – routes requests to files in /QR
 */
declare(strict_types=1);

// Buffer all output so accidental BOM/whitespace cannot break session_start()/header()
ob_start();

// Hide PHP 8 deprecations from library code (TCPDF) on the public site
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = $uri === null ? '/' : $uri;
$uri = rawurldecode($uri);

if ($uri === '' || $uri === '/') {
    $target = 'index.php';
} else {
    $target = ltrim($uri, '/');
}

$blocked = ['vendor/', 'composer.', '.env', 'Database/', 'phpqr/', 'data/'];
foreach ($blocked as $b) {
    if (stripos($target, $b) !== false) {
        http_response_code(403);
        echo 'Forbidden';
        ob_end_flush();
        exit;
    }
}

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'QR' . DIRECTORY_SEPARATOR;
$rootReal = realpath($root);
$file = realpath($root . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target));

if ($file && is_dir($file)) {
    $file = realpath($file . DIRECTORY_SEPARATOR . 'index.php');
}

if ((!$file || !is_file($file)) && substr($target, -4) !== '.php') {
    $try = realpath($root . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target) . '.php');
    if ($try && is_file($try)) {
        $file = $try;
    }
}

if (!$file || !is_file($file) || !$rootReal || strpos($file, $rootReal) !== 0) {
    if (preg_match('/\.php$/i', $target)) {
        http_response_code(404);
        echo 'Page not found: ' . htmlspecialchars($target);
        ob_end_flush();
        exit;
    }
    $file = realpath($root . 'index.php');
}

$ext = strtolower(pathinfo((string)$file, PATHINFO_EXTENSION));
$static = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'];
if (in_array($ext, $static, true)) {
    ob_end_clean();
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

// Start session once for all app pages (avoids per-file header issues)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

chdir(dirname($file));
require $file;
ob_end_flush();
