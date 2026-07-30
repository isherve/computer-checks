<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$serialNumber = trim((string)($_POST['sn'] ?? ''));
$model = trim((string)($_POST['model'] ?? ''));
$type = trim((string)($_POST['type'] ?? ''));
$ownerNumber = trim((string)($_POST['owno'] ?? ''));
$ownerName = trim((string)($_POST['owname'] ?? ''));
$action = trim((string)($_POST['action'] ?? ''));
$comment = trim((string)($_POST['comment'] ?? ''));
$officerEmail = trim((string)($_POST['officer_email'] ?? ''));
$officerPassword = (string)($_POST['officer_password'] ?? '');

function redirect_log_form(string $sn, string $error, string $email = ''): void
{
    $q = 'sn=' . urlencode($sn) . '&error=' . urlencode($error);
    if ($email !== '') {
        $q .= '&email=' . urlencode($email);
    }
    header('Location: log_form.php?' . $q);
    exit;
}

if ($serialNumber === '' || $action === '') {
    header('Location: index.php');
    exit;
}

if (!in_array($action, ['check-in', 'check-out'], true)) {
    redirect_log_form($serialNumber, 'Invalid action selected.', $officerEmail);
}

if ($officerEmail === '' || $officerPassword === '') {
    redirect_log_form($serialNumber, 'Officer email and password are required.', $officerEmail);
}

try {
    app_ensure_logs_checked_by($pdo);

    $auth = $pdo->prepare('SELECT names, email, password, user_type FROM users WHERE email = :email LIMIT 1');
    $auth->execute([':email' => $officerEmail]);
    $officer = $auth->fetch(PDO::FETCH_ASSOC);

    $passwordOk = false;
    if ($officer) {
        $stored = (string)($officer['password'] ?? '');
        if ($stored !== '' && password_verify($officerPassword, $stored)) {
            $passwordOk = true;
        } elseif ($stored !== '' && hash_equals($stored, $officerPassword)) {
            $passwordOk = true;
        }
    }

    if (!$passwordOk) {
        redirect_log_form($serialNumber, 'Incorrect email or password. Log was not saved.', $officerEmail);
    }

    $checkedBy = trim((string)($officer['names'] ?? ''));
    if ($checkedBy === '') {
        $checkedBy = (string)$officer['email'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO logs (sn, model, type, owno, owname, action, comment, checked_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $serialNumber,
        $model,
        $type,
        $ownerNumber,
        $ownerName,
        $action,
        $comment,
        $checkedBy,
    ]);

    if (function_exists('app_db_persist')) {
        app_db_persist();
    }
} catch (PDOException $e) {
    redirect_log_form($serialNumber, 'Could not save log. Please try again.', $officerEmail);
}

$sn = htmlspecialchars($serialNumber, ENT_QUOTES, 'UTF-8');
$name = htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8');
$status = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
$commentSafe = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
$officerSafe = htmlspecialchars($checkedBy, ENT_QUOTES, 'UTF-8');
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
        .comment-box {
            margin-top: 18px;
            padding: 12px 14px;
            border-left: 4px solid teal;
            background: #f3fbfb;
            font-size: 1.1rem;
            color: #222;
            max-width: 640px;
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
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="msg">
        A log for <span class="hl"><?php echo $sn; ?></span>
        whose the owner is <span class="hl"><?php echo $name; ?></span>
        is recorded successfully!
        Status: <span class="hl"><?php echo $status; ?></span>
        Checked by: <span class="hl"><?php echo $officerSafe; ?></span>
    </div>
    <?php if ($commentSafe !== ''): ?>
        <div class="comment-box"><strong>Comment:</strong> <?php echo $commentSafe; ?></div>
    <?php endif; ?>
    <a class="ok" href="javascript:history.back()">Back</a>
    <a class="ok" href="report.php" style="background:#343a40;">View Logs</a>
</body>
</html>
