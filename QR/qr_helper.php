<?php
/**
 * Build a scannable QR as SVG with quiet zone + white background.
 */
if (!function_exists('app_qr_svg')) {
    function app_qr_svg(string $data, int $moduleSize = 8, int $quiet = 4, string $ecc = 'M'): string
    {
        $barcodeClass = __DIR__ . '/tcpdf/tcpdf_barcodes_2d.php';
        if (!is_file($barcodeClass)) {
            return '';
        }
        require_once $barcodeClass;
        if (!class_exists('TCPDF2DBarcode')) {
            return '';
        }

        $barcode = new TCPDF2DBarcode($data, 'QRCODE,' . $ecc);
        $arr = $barcode->getBarcodeArray();
        if (empty($arr['bcode']) || empty($arr['num_rows']) || empty($arr['num_cols'])) {
            return '';
        }

        $rows = (int)$arr['num_rows'];
        $cols = (int)$arr['num_cols'];
        $size = ($cols + 2 * $quiet) * $moduleSize;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
        $svg .= '<g fill="#000000">';

        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if (!empty($arr['bcode'][$r][$c])) {
                    $x = ($c + $quiet) * $moduleSize;
                    $y = ($r + $quiet) * $moduleSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $moduleSize . '" height="' . $moduleSize . '"/>';
                }
            }
        }
        $svg .= '</g></svg>';
        return $svg;
    }
}
