<?php
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$serialNumber = $_POST['sn'] ?? '';
$model = $_POST['model'] ?? '';
$type = $_POST['type'] ?? '';
$ownerNumber = $_POST['owno'] ?? '';
$ownerName = $_POST['owname'] ?? '';
$action = $_POST['action'] ?? '';
$comment = $_POST['comment'] ?? '';

$stmt = $pdo->prepare(
    "INSERT INTO logs (sn, model, type, owno, owname, action, comment)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([$serialNumber, $model, $type, $ownerNumber, $ownerName, $action, $comment]);

$sn = htmlspecialchars($serialNumber, ENT_QUOTES, 'UTF-8');
$name = htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8');
$status = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Recorded</title>
    <style>
        body {
            margin: 0;
            padding: 24px 16px;
            font-family: Poppins, Arial, sans-serif;
            background: #ffffff;
            color: teal;
        }
        .msg {
            font-weight: bold;
            font-size: clamp(28px, 8vw, 48px);
            line-height: 1.35;
            max-width: 960px;
        }
        .hl {
            color: #000;
            font-weight: 800;
        }
        .ok {
            margin-top: 28px;
            display: inline-block;
            padding: 12px 18px;
            background: teal;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="msg">
        A log for <span class="hl"><?php echo $sn; ?></span>
        whose the owner is <span class="hl"><?php echo $name; ?></span>
        is recorded successfully!
        Status: <span class="hl"><?php echo $status; ?></span>
    </div>
    <a class="ok" href="javascript:history.back()">Back</a>
</body>
</html>
