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
$isAdmin = ($user_type === 'Admin');

// Recent logs for on-page preview
$recent = app_build_report($pdo, ['period' => 'all']);
$recentFlat = app_report_flat_rows($recent);
$preview = array_slice($recentFlat, 0, 20);

// All rows that have a real comment
$commentsStmt = $pdo->query(
    "SELECT sn, model, type, owno, owname, action, comment, date
     FROM logs
     WHERE comment IS NOT NULL AND TRIM(comment) != ''
     ORDER BY date DESC"
);
$commentRows = $commentsStmt ? $commentsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Checks | Logs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="icons/css/all.css">
    <style>
        :root {
            --ink: #1a2332;
            --teal: #0d7377;
            --teal-dark: #095c5f;
            --line: #e2e8f0;
            --bg: #f0f4f7;
            --card: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Poppins, "Segoe UI", Arial, sans-serif;
            background: var(--bg);
            color: var(--ink);
        }
        header.app-header {
            background: #343a40;
            color: #fff;
            padding: 14px 20px;
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        header.app-header .logo {
            max-width: 72px;
            border-radius: 50%;
        }
        header.app-header h1 {
            font-size: 1.35rem;
            margin: 0;
        }
        header.app-header h5 {
            margin: 2px 0 0;
            font-weight: 400;
            opacity: 0.9;
        }
        .shell {
            display: flex;
            min-height: calc(100vh - 76px);
        }
        .sidebar {
            width: 220px;
            background: #f8f9fa;
            padding: 28px 12px;
            border-right: 1px solid var(--line);
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #3498db;
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 6px;
        }
        .sidebar .nav-link i { color: #111; margin-right: 8px; width: 18px; }
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background: #e8eef3;
        }
        .main {
            flex: 1;
            padding: 24px 28px 40px;
            max-width: 1100px;
        }
        .page-title {
            color: var(--teal);
            font-weight: 700;
            margin: 0 0 6px;
        }
        .page-sub {
            color: #64748b;
            margin-bottom: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 960px) {
            .shell { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--line); }
            .grid { grid-template-columns: 1fr; }
        }
        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 18px 20px;
        }
        .panel h4 {
            margin: 0 0 12px;
            font-size: 1.05rem;
            color: var(--ink);
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .btn-teal {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
        }
        .btn-teal:hover {
            background: var(--teal-dark);
            border-color: var(--teal-dark);
            color: #fff;
        }
        .form-group label { font-weight: 600; font-size: 0.9rem; }
        .table-wrap {
            overflow-x: auto;
        }
        table.logs-table {
            width: 100%;
            font-size: 0.88rem;
            margin: 0;
        }
        table.logs-table thead th {
            background: #343a40;
            color: #fff;
            border: none;
            white-space: nowrap;
        }
        .badge-in { background: #0d7377; }
        .badge-out { background: #c0392b; }
        .stat-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .stat {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px 16px;
            min-width: 120px;
        }
        .stat b { display: block; font-size: 1.4rem; color: var(--teal); }
        table.logs-table td.comment-cell {
            min-width: 160px;
            max-width: 280px;
            white-space: normal;
            word-break: break-word;
            font-weight: 600;
            color: #0d7377;
        }
        table.logs-table td.comment-empty {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }
        .comment-card {
            border: 1px solid var(--line);
            border-left: 4px solid var(--teal);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            background: #f8fffe;
        }
        .comment-card .meta { font-size: 0.8rem; color: #64748b; margin-bottom: 4px; }
        .comment-card .text { font-size: 1rem; font-weight: 600; color: var(--ink); }
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
                alert('Please select a period, or use View / Download all logs.');
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
        function viewReport() {
            var url = buildUrl('html');
            if (url) window.location.href = url;
        }
        function downloadReport() {
            var url = buildUrl('csv');
            if (url) window.location.href = url;
        }
        function downloadPdf() {
            var url = buildUrl('pdf');
            if (url) window.location.href = url;
        }
    </script>
</head>
<body>
<header class="app-header">
    <img src="img/QR-logo.JPG" alt="Logo" class="logo">
    <div>
        <h1><?php echo htmlspecialchars($user_type); ?> | Logs</h1>
        <h5>Welcome, <?php echo htmlspecialchars($displayName); ?> — Computer Checks · UTBrubavu</h5>
    </div>
</header>

<div class="shell">
    <aside class="sidebar">
        <nav>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo htmlspecialchars($dash); ?>"><i class="fa fa-home"></i> Dashboard</a>
                </li>
                <?php if (!$isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="view-laptops.php"><i class="fa fa-eye"></i> View Laptops</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="record-computers.php"><i class="fa fa-plus-circle"></i> Record New Laptop</a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="view-users.php"><i class="fa fa-eye"></i> View Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="add-users.php"><i class="fa fa-plus-circle"></i> Add new user</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link active" href="report.php"><i class="fa fa-book"></i> Logs</a>
                </li>
                <?php if (!$isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="change-password.php"><i class="fa fa-pencil"></i> Change Password</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main">
        <h2 class="page-title">Gate Logs</h2>
        <p class="page-sub">View check-in / check-out activity, then download as PDF or CSV.</p>

        <div class="stat-row">
            <div class="stat">
                <b><?php echo count($recentFlat); ?></b>
                <span>Recent log rows</span>
            </div>
            <div class="stat">
                <b><?php echo count(array_filter($recentFlat, function ($r) { return ($r[6] ?? '') === 'check-in'; })); ?></b>
                <span>Check-ins (in list)</span>
            </div>
            <div class="stat">
                <b><?php echo count($commentRows); ?></b>
                <span>With comments</span>
            </div>
        </div>

        <section class="panel">
            <h4>All comments entered <small class="text-muted">(<?php echo count($commentRows); ?>)</small></h4>
            <?php if (count($commentRows) === 0): ?>
                <p class="text-muted mb-0">No comments yet. When you scan a QR and type in the Comment field before Commit, it will show here.</p>
            <?php else: ?>
                <div class="actions mb-3">
                    <a class="btn btn-teal btn-sm" href="generate_report.php?period=all&amp;comments_only=1"><i class="fa fa-eye"></i> View all with comments</a>
                    <a class="btn btn-danger btn-sm" href="generate_report.php?period=all&amp;comments_only=1&amp;format=pdf"><i class="fa fa-file-pdf"></i> PDF (comments)</a>
                    <a class="btn btn-success btn-sm" href="generate_report.php?period=all&amp;comments_only=1&amp;format=csv"><i class="fa fa-download"></i> CSV (comments)</a>
                </div>
                <?php foreach ($commentRows as $cr): ?>
                    <div class="comment-card">
                        <div class="meta">
                            <?php echo htmlspecialchars($cr['date']); ?> ·
                            <?php echo htmlspecialchars($cr['owname']); ?> ·
                            <?php echo htmlspecialchars($cr['sn']); ?> ·
                            <span class="badge <?php echo $cr['action'] === 'check-in' ? 'badge-in' : 'badge-out'; ?>">
                                <?php echo htmlspecialchars($cr['action']); ?>
                            </span>
                        </div>
                        <div class="text"><?php echo htmlspecialchars($cr['comment']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div class="grid">
            <section class="panel">
                <h4>Quick actions</h4>
                <div class="actions mb-3">
                    <a class="btn btn-teal" href="generate_report.php?period=all"><i class="fa fa-eye"></i> View all logs</a>
                    <a class="btn btn-danger" href="generate_report.php?period=all&amp;format=pdf"><i class="fa fa-file-pdf"></i> Download PDF</a>
                    <a class="btn btn-success" href="generate_report.php?period=all&amp;format=csv"><i class="fa fa-download"></i> Download CSV</a>
                </div>
                <p class="text-muted mb-0" style="font-size:0.85rem;">Opens the full log list (up to 500 newest records) or downloads it immediately.</p>
            </section>

            <section class="panel">
                <h4>Filter logs</h4>
                <form onsubmit="return false;">
                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="overall" onchange="toggleOverall()">
                            <label class="custom-control-label" for="overall">Overall (in + out)</label>
                        </div>
                    </div>
                    <div class="form-group" id="action-wrap">
                        <label for="action">Type</label>
                        <select class="form-control form-control-sm" id="action">
                            <option value="check-in">Check-In</option>
                            <option value="check-out">Check-Out</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="period">Period</label>
                        <select class="form-control form-control-sm" id="period" onchange="updateFormFields()">
                            <option value="#">Select period</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                            <option value="individual">By serial number</option>
                        </select>
                    </div>
                    <div class="form-group" id="date-fields" style="display:none;">
                        <label for="date">Date</label>
                        <input type="date" class="form-control form-control-sm mb-2" id="date">
                        <label>Start</label>
                        <div class="form-row mb-2">
                            <div class="col">
                                <select class="form-control form-control-sm" id="start-hour">
                                    <?php for ($i = 0; $i < 24; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col">
                                <select class="form-control form-control-sm" id="start-minute">
                                    <?php for ($i = 0; $i < 60; $i += 5): ?>
                                        <option value="<?php echo $i; ?>"><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <label>End</label>
                        <div class="form-row">
                            <div class="col">
                                <select class="form-control form-control-sm" id="end-hour">
                                    <?php for ($i = 0; $i < 24; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === 23 ? 'selected' : ''; ?>><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col">
                                <select class="form-control form-control-sm" id="end-minute">
                                    <?php for ($i = 0; $i < 60; $i += 5): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === 55 ? 'selected' : ''; ?>><?php echo str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="week-fields" style="display:none;">
                        <label for="start-date">Start date</label>
                        <input type="date" class="form-control form-control-sm mb-2" id="start-date">
                        <label for="end-date">End date</label>
                        <input type="date" class="form-control form-control-sm" id="end-date">
                    </div>
                    <div class="form-group" id="month-field" style="display:none;">
                        <label for="month">Month</label>
                        <input type="month" class="form-control form-control-sm" id="month">
                    </div>
                    <div class="form-group" id="year-field" style="display:none;">
                        <label for="year">Year</label>
                        <input type="number" class="form-control form-control-sm" id="year" min="2020" max="2100" value="<?php echo date('Y'); ?>">
                    </div>
                    <div class="form-group" id="sn-field" style="display:none;">
                        <label for="sn">Serial number</label>
                        <input type="text" class="form-control form-control-sm" id="sn" placeholder="e.g. PF4L50WX">
                    </div>
                    <div class="actions">
                        <button type="button" class="btn btn-sm btn-teal" onclick="viewReport()"><i class="fa fa-eye"></i> View</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="downloadPdf()"><i class="fa fa-file-pdf"></i> PDF</button>
                        <button type="button" class="btn btn-sm btn-success" onclick="downloadReport()"><i class="fa fa-download"></i> CSV</button>
                    </div>
                </form>
            </section>
        </div>

        <section class="panel">
            <h4>Latest activity <small class="text-muted">(showing <?php echo count($preview); ?> of <?php echo count($recentFlat); ?>)</small></h4>
            <div class="table-wrap">
                <table class="table table-bordered table-sm logs-table">
                    <thead>
                        <tr>
                            <?php foreach ($recent['columns'] as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($preview) === 0): ?>
                        <tr><td colspan="<?php echo count($recent['columns']); ?>" class="text-center text-muted">No logs yet. Scan a QR and submit a gate log.</td></tr>
                    <?php else: ?>
                        <?php foreach ($preview as $row): ?>
                            <tr>
                                <?php foreach ($row as $idx => $cell): ?>
                                    <?php if ($idx === 6): ?>
                                        <td>
                                            <?php $act = (string)$cell; ?>
                                            <span class="badge <?php echo $act === 'check-in' ? 'badge-in' : 'badge-out'; ?>">
                                                <?php echo htmlspecialchars($act); ?>
                                            </span>
                                        </td>
                                    <?php elseif ($idx === 8): ?>
                                        <?php $cmt = trim((string)$cell); ?>
                                        <td class="<?php echo $cmt === '' ? 'comment-empty' : 'comment-cell'; ?>">
                                            <?php echo $cmt === '' ? '—' : htmlspecialchars($cmt); ?>
                                        </td>
                                    <?php else: ?>
                                        <td><?php echo htmlspecialchars((string)$cell); ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="actions mt-3">
                <a class="btn btn-teal btn-sm" href="generate_report.php?period=all">See full log table</a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
