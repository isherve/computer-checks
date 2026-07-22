<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Checks | QR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            background: #f7f7f7;
        }
        .qr-card {
            display: inline-block;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
            background: #fff;
        }
        .qr-wrap {
            width: 220px;
            height: 220px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-wrap svg, .qr-wrap img {
            max-width: 220px;
            max-height: 220px;
            width: 220px;
            height: 220px;
        }
        .details {
            margin-top: 12px;
            text-align: left;
            font-size: 14px;
        }
        .scan-url {
            word-break: break-all;
            font-size: 12px;
            color: #555;
            max-width: 320px;
            margin: 10px auto;
        }
        @media print {
            title, nav, input, button, .no-print {
                display: none !important;
            }
            body { background: #fff; }
        }
    </style>
</head>
<body>
<?php
require_once 'connection.php';
require_once 'config.php';

$row = null;

if (!empty($_GET['name'])) {
    $stmt = $pdo->prepare(
        "SELECT sn, model, type, owno, owname
         FROM computer_info
         WHERE owname = ?
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->execute([trim($_GET['name'])]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $stmt = $pdo->prepare(
            "SELECT sn, model, type, owno, owname
             FROM computer_info
             WHERE owname LIKE ?
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute(['%' . trim($_GET['name']) . '%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} elseif (!empty($_GET['sn'])) {
    $stmt = $pdo->prepare(
        "SELECT sn, model, type, owno, owname
         FROM computer_info
         WHERE sn = ?"
    );
    $stmt->execute([$_GET['sn']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$row) {
    echo "<p style='color:red;'>No computer found for the given owner name or serial number.</p>";
    echo "<p class='no-print'><a href='view-laptops.php'>Back to View Laptops</a></p>";
    exit;
}

$serialNumber = $row['sn'];
$model = $row['model'];
$type = $row['type'];
$ownerNumber = $row['owno'];
$ownerName = $row['owname'];

// URL encoded in the QR – must be reachable from a phone
$url = app_log_form_url($row);

// Build QR markup (SVG inline – works on Vercel without GD)
$qrHtml = '';
$barcodeClass = __DIR__ . '/tcpdf/tcpdf_barcodes_2d.php';
if (is_file($barcodeClass)) {
    require_once $barcodeClass;
    if (class_exists('TCPDF2DBarcode')) {
        $barcode = new TCPDF2DBarcode($url, 'QRCODE,H');
        $svg = $barcode->getBarcodeSVGcode(6, 6, 'black');
        // Keep only the SVG element for embedding
        if (preg_match('/<svg[\s\S]*<\/svg>/i', $svg, $m)) {
            $qrHtml = $m[0];
        } else {
            $qrHtml = $svg;
        }
    }
}
if ($qrHtml === '') {
    // Fallback image endpoint
    $qrHtml = '<img src="' . htmlspecialchars('qr-image.php?details=' . urlencode($url), ENT_QUOTES, 'UTF-8') . '" width="220" height="220" alt="QR Code" />';
}
?>
    <h3>QR Code for <?php echo htmlspecialchars($ownerName); ?></h3>
    <div class="qr-card">
        <div class="qr-wrap">
            <?php echo $qrHtml; ?>
        </div>
        <div class="details">
            <p><strong>Owner:</strong> <?php echo htmlspecialchars($ownerName); ?></p>
            <p><strong>Serial Number:</strong> <?php echo htmlspecialchars($serialNumber); ?></p>
            <p><strong>Model:</strong> <?php echo htmlspecialchars($model); ?></p>
            <p><strong>Owner Number:</strong> <?php echo htmlspecialchars($ownerNumber); ?></p>
        </div>
        <p class="scan-url no-print"><strong>Scan opens:</strong><br><?php echo htmlspecialchars($url); ?></p>
        <p class="no-print">
            <button onclick="window.print()">Print QR Code</button>
            <a href="view-laptops.php">Back</a>
        </p>
        <p class="no-print" style="font-size:12px;color:#666;max-width:320px;margin:8px auto;">
            After scanning, choose In/Out and submit. You should see:
            <em>“A log for … whose the owner is … is recorded successfully! Status: …”</em>
        </p>
    </div>
</body>
</html>
