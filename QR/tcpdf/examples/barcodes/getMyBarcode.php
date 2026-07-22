<?php
/**
 * Generate a QR code image from ?details=...
 * PNG when GD is available; SVG otherwise (Vercel-safe).
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');

$details = isset($_GET['details']) ? $_GET['details'] : '';
if ($details === '') {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Missing details parameter';
    exit;
}

$barcodeClass = dirname(__FILE__) . '/../../tcpdf_barcodes_2d.php';
if (!file_exists($barcodeClass)) {
    $barcodeClass = dirname(__FILE__) . '/../../../tcpdf/tcpdf_barcodes_2d.php';
}
if (!file_exists($barcodeClass)) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'Barcode library not found';
    exit;
}
require_once $barcodeClass;

if (!class_exists('TCPDF2DBarcode')) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'TCPDF2DBarcode class missing';
    exit;
}

$barcodeobj = new TCPDF2DBarcode($details, 'QRCODE,H');

while (ob_get_level() > 0) {
    ob_end_clean();
}

if (function_exists('imagepng')) {
    $barcodeobj->getBarcodePNG(6, 6, array(0, 0, 0));
    exit;
}

header('Content-Type: image/svg+xml; charset=UTF-8');
echo $barcodeobj->getBarcodeSVGcode(6, 6, 'black');
exit;
