<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type'])) {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

$user_type = $_SESSION['user_type'];
$email = $_SESSION['email'];
$stmt = $pdo->prepare('SELECT names FROM users WHERE user_type = ? AND email = ?');
$stmt->execute([$user_type, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$displayName = $user['names'] ?? $email;
$dash = ($user_type === 'Admin') ? 'admin-dashboard.php' : 'user-dashboard.php';
$isAdmin = ($user_type === 'Admin');
$activeNav = 'reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Checks | Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="icons/css/all.css">
    <style>
        :root { --teal: #0d7377; --teal-dark: #095c5f; --line: #e2e8f0; --bg: #f0f4f7; }
        body { margin: 0; font-family: Poppins, "Segoe UI", Arial, sans-serif; background: var(--bg); }
        header.app-header {
            background: #343a40; color: #fff; padding: 14px 20px;
            position: sticky; top: 0; z-index: 20;
            display: flex; align-items: center; gap: 16px;
        }
        header.app-header .logo { max-width: 72px; border-radius: 50%; }
        header.app-header h1 { font-size: 1.35rem; margin: 0; }
        header.app-header h5 { margin: 2px 0 0; font-weight: 400; opacity: .9; }
        .shell { display: flex; min-height: calc(100vh - 76px); }
        .sidebar {
            width: 220px; background: #f8f9fa; padding: 28px 12px;
            border-right: 1px solid var(--line); flex-shrink: 0;
        }
        .sidebar .nav-link { color: #3498db; font-weight: 600; padding: 10px 12px; border-radius: 6px; }
        .sidebar .nav-link i { color: #111; margin-right: 8px; width: 18px; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: #e8eef3; }
        .main { flex: 1; padding: 24px 28px 40px; max-width: 720px; }
        .page-title { color: var(--teal); font-weight: 700; margin: 0 0 6px; }
        .page-sub { color: #64748b; margin-bottom: 20px; }
        .panel {
            background: #fff; border: 1px solid var(--line);
            border-radius: 10px; padding: 20px; margin-bottom: 16px;
        }
        .btn-teal { background: var(--teal); border-color: var(--teal); color: #fff; }
        .btn-teal:hover { background: var(--teal-dark); border-color: var(--teal-dark); color: #fff; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .form-group label { font-weight: 600; }
        @media (max-width: 768px) {
            .shell { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--line); }
        }
    </style>
    <script>
        function updateFormFields() {
            var period = document.getElementById('period').value;
            ['date-fields','week-fields','month-field','year-field','sn-field','action-wrap'].forEach(function(id) {
                document.getElementById(id).style.display = 'none';
            });
            if (period === 'daily') {
                document.getElementById('date-fields').style.display = 'block';
                document.getElementById('action-wrap').style.display = 'block';
            } else if (period === 'weekly') {
                document.getElementById('week-fields').style.display = 'block';
                document.getElementById('action-wrap').style.display = 'block';
            } else if (period === 'monthly') {
                document.getElementById('month-field').style.display = 'block';
                document.getElementById('action-wrap').style.display = 'block';
            } else if (period === 'annual') {
                document.getElementById('year-field').style.display = 'block';
                document.getElementById('action-wrap').style.display = 'block';
            } else if (period === 'individual') {
                document.getElementById('sn-field').style.display = 'block';
                document.getElementById('date-fields').style.display = 'block';
                document.getElementById('action-wrap').style.display = 'block';
            }
            toggleOverall();
        }
        function toggleOverall() {
            var overall = document.getElementById('overall').checked;
            var period = document.getElementById('period').value;
            if (overall || period === 'all') {
                document.getElementById('action-wrap').style.display = 'none';
            } else if (period && period !== 'all' && period !== '#') {
                document.getElementById('action-wrap').style.display = 'block';
            }
        }
        function buildUrl(format) {
            var period = document.getElementById('period').value;
            if (!period || period === '#') {
                alert('Please select a report period.');
                return null;
            }
            var params = new URLSearchParams();
            params.set('period', period);
            params.set('format', format);
            if (document.getElementById('overall').checked) {
                params.set('overall', '1');
            } else {
                params.set('action', document.getElementById('action').value);
            }
            if (period === 'daily' || period === 'individual') {
                params.set('date', document.getElementById('date').value);
                params.set('start_hour', document.getElementById('start-hour').value);
                params.set('start_minute', document.getElementById('start-minute').value);
                params.set('end_hour', document.getElementById('end-hour').value);
                params.set('end_minute', document.getElementById('end-minute').value);
            }
            if (period === 'weekly') {
                params.set('start_date', document.getElementById('start-date').value);
                params.set('end_date', document.getElementById('end-date').value);
            }
            if (period === 'monthly') params.set('month', document.getElementById('month').value);
            if (period === 'annual') params.set('year', document.getElementById('year').value);
            if (period === 'individual') params.set('sn', document.getElementById('sn').value);
            return 'generate_report.php?' + params.toString();
        }
        function viewReport() { var u = buildUrl('html'); if (u) location.href = u; }
        function downloadCsv() { var u = buildUrl('csv'); if (u) location.href = u; }
        function downloadPdf() { var u = buildUrl('pdf'); if (u) location.href = u; }
    </script>
</head>
<body>
<header class="app-header">
    <img src="img/QR-logo.JPG" alt="Logo" class="logo">
    <div>
        <h1><?php echo htmlspecialchars($user_type); ?> | Reports</h1>
        <h5>Welcome, <?php echo htmlspecialchars($displayName); ?> — Computer Checks · UTBrubavu</h5>
    </div>
</header>
<div class="shell">
    <aside class="sidebar">
        <nav><?php include 'nav_menu.php'; ?></nav>
    </aside>
    <main class="main">
        <h2 class="page-title">Generate Report</h2>
        <p class="page-sub">Filter gate activity by period, then view or download PDF / CSV.</p>

        <section class="panel">
            <h4>Quick report</h4>
            <div class="actions">
                <a class="btn btn-teal" href="generate_report.php?period=all"><i class="fa fa-eye"></i> View full report</a>
                <a class="btn btn-danger" href="generate_report.php?period=all&amp;format=pdf"><i class="fa fa-file-pdf"></i> Download PDF</a>
                <a class="btn btn-success" href="generate_report.php?period=all&amp;format=csv"><i class="fa fa-download"></i> Download CSV</a>
                <a class="btn btn-outline-secondary" href="report.php"><i class="fa fa-book"></i> Open Logs</a>
            </div>
        </section>

        <section class="panel">
            <h4>Custom report</h4>
            <form onsubmit="return false;">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="overall" onchange="toggleOverall()">
                        <label class="custom-control-label" for="overall">Overall (check-in + check-out)</label>
                    </div>
                </div>
                <div class="form-group" id="action-wrap">
                    <label for="action">Report type</label>
                    <select class="form-control" id="action">
                        <option value="check-in">Computers Checked In</option>
                        <option value="check-out">Computers Checked Out</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="period">Period</label>
                    <select class="form-control" id="period" onchange="updateFormFields()">
                        <option value="#">Select period</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="annual">Annual</option>
                        <option value="individual">Individual (by serial)</option>
                    </select>
                </div>
                <div class="form-group" id="date-fields" style="display:none;">
                    <label for="date">Date</label>
                    <input type="date" class="form-control mb-2" id="date">
                    <label>Start time</label>
                    <div class="form-row mb-2">
                        <div class="col">
                            <select class="form-control" id="start-hour">
                                <?php for ($i = 0; $i < 24; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-control" id="start-minute">
                                <?php for ($i = 0; $i < 60; $i += 5): ?>
                                    <option value="<?php echo $i; ?>"><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <label>End time</label>
                    <div class="form-row">
                        <div class="col">
                            <select class="form-control" id="end-hour">
                                <?php for ($i = 0; $i < 24; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i === 23 ? 'selected' : ''; ?>><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-control" id="end-minute">
                                <?php for ($i = 0; $i < 60; $i += 5): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i === 55 ? 'selected' : ''; ?>><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="week-fields" style="display:none;">
                    <label for="start-date">Start date</label>
                    <input type="date" class="form-control mb-2" id="start-date">
                    <label for="end-date">End date</label>
                    <input type="date" class="form-control" id="end-date">
                </div>
                <div class="form-group" id="month-field" style="display:none;">
                    <label for="month">Month</label>
                    <input type="month" class="form-control" id="month">
                </div>
                <div class="form-group" id="year-field" style="display:none;">
                    <label for="year">Year</label>
                    <input type="number" class="form-control" id="year" min="2020" max="2100" value="<?php echo date('Y'); ?>">
                </div>
                <div class="form-group" id="sn-field" style="display:none;">
                    <label for="sn">Serial number</label>
                    <input type="text" class="form-control" id="sn" placeholder="e.g. PF4L50WX">
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-teal" onclick="viewReport()"><i class="fa fa-eye"></i> View report</button>
                    <button type="button" class="btn btn-danger" onclick="downloadPdf()"><i class="fa fa-file-pdf"></i> Download PDF</button>
                    <button type="button" class="btn btn-success" onclick="downloadCsv()"><i class="fa fa-download"></i> Download CSV</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
