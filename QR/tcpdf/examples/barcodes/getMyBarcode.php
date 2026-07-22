<?php
/**
 * Generate a QR code PNG from ?details=...
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

// Load TCPDF 2D barcode class reliably
$barcodeClass = dirname(__FILE__) . '/../../tcpdf_barcodes_2d.php';
if (!file_exists($barcodeClass)) {
    // Fallback absolute from htdocs QR root
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

if (!function_exists('imagepng')) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'PHP GD extension is required to render QR PNG images. Enable extension=gd in php.ini and restart Apache.';
    exit;
}

$barcodeobj = new TCPDF2DBarcode($details, 'QRCODE,H');
$barcodeobj->getBarcodePNG(6, 6, array(0, 0, 0));
