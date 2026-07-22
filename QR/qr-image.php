<?php
/**
 * QR image endpoint – returns SVG (no GD required) or PNG if GD is available.
 * Usage: qr-image.php?details=https://...
 */
declare(strict_types=1);

$details = isset($_GET['details']) ? (string)$_GET['details'] : '';
if ($details === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Missing details parameter';
    exit;
}

$barcodeClass = __DIR__ . '/tcpdf/tcpdf_barcodes_2d.php';
if (!is_file($barcodeClass)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Barcode library not found';
    exit;
}
require_once $barcodeClass;

if (!class_exists('TCPDF2DBarcode')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'TCPDF2DBarcode class missing';
    exit;
}

$barcode = new TCPDF2DBarcode($details, 'QRCODE,H');

// Prefer PNG when GD exists; otherwise SVG (works on Vercel)
if (function_exists('imagepng')) {
    // Drop outer output buffers so PNG headers/body stay clean
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $barcode->getBarcodePNG(6, 6, [0, 0, 0]);
    exit;
}

while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: public, max-age=300');
echo $barcode->getBarcodeSVGcode(6, 6, 'black');
exit;
