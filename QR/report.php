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
        body { font-family: Poppins, Arial, sans-serif; background: #f4f6f8; margin: 0; }
        header {
            background: #343a40; color: #fff; padding: 12px 20px;
            position: sticky; top: 0; z-index: 10;
        }
        header h1 { font-size: 1.25rem; margin: 0; }
        header h5 { margin: 4px 0 0; font-weight: normal; opacity: .9; }
        .layout { display: flex; min-height: calc(100vh - 70px); }
        .sidebar {
            width: 210px; background: #fff; padding: 20px 12px;
            border-right: 1px solid #e5e5e5; flex-shrink: 0;
        }
        .content { flex: 1; padding: 24px; max-width: 720px; }
        .panel {
            background: #fff; border-radius: 8px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }
        .quick a { margin-right: 8px; margin-bottom: 8px; }
        .form-group label { font-weight: 600; }
        @media (max-width: 768px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e5e5e5; }
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
            // overall hides action filter
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
                alert('Please select a period (or use View all logs).');
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
            if (period === 'monthly') {
                params.set('month', document.getElementById('month').value);
            }
            if (period === 'annual') {
                params.set('year', document.getElementById('year').value);
            }
            if (period === 'individual') {
                params.set('sn', document.getElementById('sn').value);
            }
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
    </script>
</head>
<body>
<header>
    <h1><?php echo htmlspecialchars($user_type); ?> | Reports</h1>
    <h5>Welcome, <?php echo htmlspecialchars($displayName); ?> — Computer Checks</h5>
</header>
<div class="layout">
    <aside class="sidebar">
        <h5>Menu</h5>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($dash); ?>"><i class="fa fa-home"></i> Dashboard</a></li>
            <?php if ($user_type !== 'Admin'): ?>
            <li class="nav-item"><a class="nav-link" href="view-laptops.php"><i class="fa fa-eye"></i> View Laptops</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link active" href="report.php"><i class="fa fa-book"></i> Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="content">
        <div class="panel mb-3">
            <h4>Quick view</h4>
            <p class="text-muted mb-2">See recent gate activity or download it immediately.</p>
            <div class="quick">
                <a class="btn btn-primary" href="generate_report.php?period=all">
                    <i class="fa fa-eye"></i> View all logs
                </a>
                <a class="btn btn-success" href="generate_report.php?period=all&amp;format=csv">
                    <i class="fa fa-download"></i> Download all (CSV)
                </a>
            </div>
        </div>

        <div class="panel">
            <h4>Custom report</h4>
            <form onsubmit="return false;">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="overall" onchange="toggleOverall()">
                        <label class="custom-control-label" for="overall">Overall (check-in + check-out together)</label>
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
                    <div class="form-row">
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
                    <label class="mt-2">End time</label>
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

                <button type="button" class="btn btn-primary" onclick="viewReport()">
                    <i class="fa fa-eye"></i> View report
                </button>
                <button type="button" class="btn btn-success" onclick="downloadReport()">
                    <i class="fa fa-download"></i> Download CSV
                </button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
