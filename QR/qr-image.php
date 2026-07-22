<?php
/**
 * QR image endpoint – scannable SVG with quiet zone (no GD required).
 * Usage: qr-image.php?details=https://...
 */
declare(strict_types=1);

require_once __DIR__ . '/qr_helper.php';

$details = isset($_GET['details']) ? (string)$_GET['details'] : '';
if ($details === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Missing details parameter';
    exit;
}

$svg = app_qr_svg($details, 10, 4, 'M');
if ($svg === '') {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Could not generate QR';
    exit;
}

while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: public, max-age=300');
echo '<?xml version="1.0" encoding="UTF-8"?>' . $svg;
exit;
