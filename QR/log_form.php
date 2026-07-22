<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Checks | Gate Log</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            margin-top: 24px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #343a40;
            font-size: 1.5rem;
        }
        .form-group { margin-bottom: 15px; }
        label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
            font-weight: 600;
            font-size: 0.9rem;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background-color: #0056b3; }
        .brand {
            text-align: center;
            color: #008080;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .error { color: #c0392b; text-align: center; }
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/connection.php';

$sn = isset($_GET['sn']) ? trim((string)$_GET['sn']) : '';
$model = isset($_GET['model']) ? (string)$_GET['model'] : '';
$type = isset($_GET['type']) ? (string)$_GET['type'] : '';
$owno = isset($_GET['owno']) ? (string)$_GET['owno'] : '';
$owname = isset($_GET['owname']) ? (string)$_GET['owname'] : '';

if ($sn === '') {
    echo '<div class="container"><h1>Invalid QR</h1><p class="error">No computer data found in the link. Generate a new QR code from Computer Checks.</p></div></body></html>';
    exit;
}

// Short QR links only carry ?sn=... — load the rest from DB
if ($model === '' || $owname === '') {
    $stmt = $pdo->prepare('SELECT sn, model, type, owno, owname FROM computer_info WHERE sn = ? LIMIT 1');
    $stmt->execute([$sn]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo '<div class="container"><h1>Not found</h1><p class="error">No laptop with serial number ' . htmlspecialchars($sn, ENT_QUOTES, 'UTF-8') . '.</p></div></body></html>';
        exit;
    }
    $sn = (string)$row['sn'];
    $model = (string)$row['model'];
    $type = (string)($row['type'] ?? '');
    $owno = (string)$row['owno'];
    $owname = (string)$row['owname'];
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
    <div class="container">
        <div class="brand">Computer Checks</div>
        <h1>Gate Log Form</h1>
        <form action="submit_log.php" method="post">
            <div class="form-group">
                <label for="sn">Serial Number</label>
                <input type="text" id="sn" name="sn" value="<?php echo h($sn); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" value="<?php echo h($model); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="type">Owner Type</label>
                <input type="text" id="type" name="type" value="<?php echo h($type); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="owno">Owner Identification</label>
                <input type="text" id="owno" name="owno" value="<?php echo h($owno); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="owname">Owner Name</label>
                <input type="text" id="owname" name="owname" value="<?php echo h($owname); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="action">Action</label>
                <select id="action" name="action" required>
                    <option value="check-in">Check-In</option>
                    <option value="check-out" selected>Check-Out</option>
                </select>
            </div>
            <div class="form-group">
                <label for="comment">Comment</label>
                <input type="text" id="comment" name="comment" placeholder="Optional">
            </div>
            <button type="submit">Commit</button>
        </form>
    </div>
</body>
</html>
