<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type'])) {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';
require_once 'report_lib.php';

$user_type = $_SESSION['user_type'];
$email = $_SESSION['email'];
$stmt = $pdo->prepare('SELECT names FROM users WHERE user_type = ? AND email = ?');
$stmt->execute([$user_type, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$displayName = $user['names'] ?? $email;
$dash = ($user_type === 'Admin') ? 'admin-dashboard.php' : 'user-dashboard.php';

try {
    $report = app_build_report($pdo, $_GET);
} catch (Throwable $e) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><body style="font-family:Arial;padding:2rem;"><h2>Report error</h2><p>'
        . htmlspecialchars($e->getMessage())
        . '</p><p><a href="report.php">Back to Logs</a></p></body></html>';
    exit;
}

$flat = app_report_flat_rows($report);
$title = $report['title'];
$format = isset($_GET['format']) ? strtolower((string)$_GET['format']) : 'html';

// ----- CSV download -----
if ($format === 'csv') {
    $filename = 'computer-checks-report-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $out = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, [$title]);
    fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
    fputcsv($out, ['Campus', 'UTBrubavu – Computer Checks']);
    fputcsv($out, []);
    fputcsv($out, $report['columns']);
    foreach ($flat as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

// ----- PDF download (lightweight writer – works on Vercel) -----
if ($format === 'pdf') {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    require_once __DIR__ . '/simple_pdf.php';

    try {
        $landscape = count($report['columns']) > 6;
        $pdf = new SimpleReportPdf($landscape);
        $pdf->setTitle($title);
        $pdf->heading('Computer Checks - UTBrubavu', 14);
        $pdf->heading($title, 11);
        $pdf->line(
            'Generated: ' . date('Y-m-d H:i:s') .
            ' | By: ' . $displayName .
            ' | Records: ' . count($flat),
            8
        );
        $pdf->line('');
        $pdf->table($report['columns'], $flat, $landscape ? 7 : 8);
        $pdf->line('');
        $pdf->line('UTBrubavu Computer Checks', 8);
        $filename = 'computer-checks-report-' . date('Ymd-His') . '.pdf';
        $pdf->outputDownload($filename);
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'PDF generation failed: ' . $e->getMessage();
    }
    exit;
}

// Build download links keeping current filters
$csvParams = $_GET;
$csvParams['format'] = 'csv';
$csvUrl = 'generate_report.php?' . http_build_query($csvParams);
$pdfParams = $_GET;
$pdfParams['format'] = 'pdf';
$pdfUrl = 'generate_report.php?' . http_build_query($pdfParams);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="icons/css/all.css">
    <style>
        body { font-family: Poppins, Arial, sans-serif; background: #f4f6f8; margin: 0; }
        header {
            background: #343a40; color: #fff; padding: 12px 20px;
            position: sticky; top: 0; z-index: 10;
        }
        header h1 { font-size: 1.25rem; margin: 0; }
        header h5 { margin: 4px 0 0; font-weight: normal; opacity: .9; }
        .wrap { max-width: 1100px; margin: 24px auto; padding: 0 16px 40px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .card-panel {
            background: #fff; border-radius: 8px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }
        .meta { color: #666; font-size: 0.9rem; margin-bottom: 12px; }
        table { font-size: 0.92rem; }
        @media print {
            header, .toolbar, .no-print { display: none !important; }
            body { background: #fff; }
            .card-panel { box-shadow: none; padding: 0; }
            .wrap { margin: 0; max-width: none; }
        }
    </style>
</head>
<body>
<header class="no-print">
    <h1><?php echo htmlspecialchars($user_type); ?> | Logs</h1>
    <h5>Computer Checks — UTBrubavu</h5>
</header>
<div class="wrap">
    <div class="toolbar no-print">
        <a class="btn btn-secondary" href="report.php"><i class="fa fa-arrow-left"></i> Back to Logs</a>
        <a class="btn btn-secondary" href="<?php echo htmlspecialchars($dash); ?>">Dashboard</a>
        <a class="btn btn-danger" href="<?php echo htmlspecialchars($pdfUrl); ?>">
            <i class="fa fa-file-pdf"></i> Download PDF
        </a>
        <a class="btn btn-success" href="<?php echo htmlspecialchars($csvUrl); ?>">
            <i class="fa fa-download"></i> Download CSV
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fa fa-print"></i> Print
        </button>
    </div>

    <div class="card-panel" id="report-print">
        <h4><?php echo htmlspecialchars($title); ?></h4>
        <p class="meta">
            Generated <?php echo date('Y-m-d H:i'); ?> ·
            By <?php echo htmlspecialchars($displayName); ?> ·
            <?php echo count($flat); ?> record(s)
        </p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead class="thead-dark">
                    <tr>
                        <?php foreach ($report['columns'] as $col): ?>
                            <th><?php echo htmlspecialchars($col); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($flat) === 0): ?>
                    <tr><td colspan="<?php echo count($report['columns']); ?>" class="text-center">No records found</td></tr>
                <?php else: ?>
                    <?php foreach ($flat as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?php echo htmlspecialchars((string)$cell); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
