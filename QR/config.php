<?php
/**
 * App configuration helpers for Computer Checks
 */

/**
 * Public base URL used inside generated QR codes (no trailing slash).
 * Priority:
 *  1) APP_BASE_URL environment variable (set this on Vercel)
 *  2) QR/app_url.local.php returning a string
 *  3) Auto-detect from current request (replaces localhost with LAN IP when possible)
 */
if (!function_exists('app_base_url')) {
    function app_base_url(): string
    {
        $env = getenv('APP_BASE_URL');
        if (is_string($env) && trim($env) !== '') {
            return rtrim(trim($env), '/');
        }

        $localFile = __DIR__ . DIRECTORY_SEPARATOR . 'app_url.local.php';
        if (is_file($localFile)) {
            $local = include $localFile;
            if (is_string($local) && trim($local) !== '') {
                return rtrim(trim($local), '/');
            }
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
        $scheme = $https ? 'https' : 'http';

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Phones cannot open localhost – prefer LAN IP for local XAMPP demos
        if (preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/i', $host)) {
            $lan = app_detect_lan_ip();
            if ($lan) {
                $port = '';
                if (preg_match('/:(\d+)$/', $host, $m)) {
                    $port = ':' . $m[1];
                }
                $host = $lan . $port;
            }
        }

        // When served via Vercel front controller, SCRIPT_NAME may be /api/index.php
        $script = $_SERVER['SCRIPT_NAME'] ?? '/QR/index.php';
        if (strpos($script, '/api/') !== false || basename($script) === 'index.php' && strpos($script, '/api') !== false) {
            return $scheme . '://' . $host;
        }

        $basePath = str_replace('\\', '/', dirname($script));
        if ($basePath === '/' || $basePath === '.' || $basePath === '\\') {
            $basePath = '';
        }

        // If path still contains /api, strip it for public URLs
        $basePath = preg_replace('#/api$#', '', $basePath);

        return $scheme . '://' . $host . $basePath;
    }
}

if (!function_exists('app_detect_lan_ip')) {
    function app_detect_lan_ip(): ?string
    {
        // Windows / common environments
        $candidates = [];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $out = @shell_exec('ipconfig');
            if (is_string($out) && preg_match_all('/IPv4[^:]+:\s*([0-9.]+)/i', $out, $m)) {
                foreach ($m[1] as $ip) {
                    if (!preg_match('/^127\./', $ip)) {
                        $candidates[] = $ip;
                    }
                }
            }
        } else {
            $out = @shell_exec("hostname -I 2>/dev/null");
            if (is_string($out)) {
                foreach (preg_split('/\s+/', trim($out)) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !preg_match('/^127\./', $ip)) {
                        $candidates[] = $ip;
                    }
                }
            }
        }

        // Prefer private LAN ranges
        foreach ($candidates as $ip) {
            if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip)) {
                return $ip;
            }
        }
        return $candidates[0] ?? null;
    }
}

if (!function_exists('app_log_form_url')) {
    function app_log_form_url(array $row): string
    {
        // Short URL = denser, easier-to-scan QR (details loaded from DB on open)
        return app_base_url() . '/log_form.php?sn=' . rawurlencode((string)$row['sn']);
    }
}
