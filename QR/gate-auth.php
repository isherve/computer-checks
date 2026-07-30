<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/connection.php';

$sn = isset($_GET['sn']) ? trim((string)$_GET['sn']) : trim((string)($_POST['sn'] ?? ''));
$error = '';

if ($sn === '') {
    http_response_code(400);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Auth</title></head><body style="font-family:Arial;padding:24px;"><h2>Invalid QR</h2><p>No serial number found. Scan a valid QR code.</p></body></html>';
    exit;
}

// Already authenticated for gate (or main app login) → go to form
if (!empty($_SESSION['gate_auth']['names']) || (!empty($_SESSION['email']) && !empty($_SESSION['user_type']))) {
    if (empty($_SESSION['gate_auth']['names']) && !empty($_SESSION['email'])) {
        $q = $pdo->prepare('SELECT names, email FROM users WHERE email = ? LIMIT 1');
        $q->execute([$_SESSION['email']]);
        $u = $q->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $_SESSION['gate_auth'] = [
                'names' => (string)($u['names'] ?: $u['email']),
                'email' => (string)$u['email'],
                'at' => time(),
            ];
        }
    }
    if (!empty($_SESSION['gate_auth']['names'])) {
        header('Location: log_form.php?sn=' . rawurlencode($sn));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT names, email, password, user_type FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $ok = false;
        if ($user) {
            $stored = (string)($user['password'] ?? '');
            if ($stored !== '' && password_verify($password, $stored)) {
                $ok = true;
            } elseif ($stored !== '' && hash_equals($stored, $password)) {
                $ok = true;
            }
        }

        if (!$ok) {
            $error = 'Incorrect email or password.';
        } else {
            $name = trim((string)($user['names'] ?? ''));
            if ($name === '') {
                $name = (string)$user['email'];
            }
            $_SESSION['gate_auth'] = [
                'names' => $name,
                'email' => (string)$user['email'],
                'at' => time(),
            ];
            // Also align with normal app session so View Logs works after commit
            $_SESSION['email'] = (string)$user['email'];
            $_SESSION['user_type'] = (string)$user['user_type'];

            header('Location: log_form.php?sn=' . rawurlencode($sn));
            exit;
        }
    }
}

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Authentication</title>
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
            background: #fff;
            padding: 24px 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            width: 100%;
            max-width: 420px;
            margin-top: 40px;
        }
        .brand { text-align: center; color: #008080; font-weight: bold; margin-bottom: 6px; }
        h1 { text-align: center; font-size: 1.35rem; color: #343a40; margin: 0 0 8px; }
        .sub { text-align: center; color: #6c757d; font-size: 0.9rem; margin-bottom: 18px; }
        .sn { font-weight: bold; color: #000; }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #495057; font-size: 0.9rem; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 70px; }
        .pw-wrap .toggle {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            border: none; background: transparent; color: #495057; font-size: 12px; cursor: pointer; padding: 4px 8px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 6px;
        }
        button[type="submit"]:hover { background: #0056b3; }
        .error {
            color: #c0392b;
            background: #fdecea;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 14px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="brand">Computer Checks</div>
    <h1>Authentication Required</h1>
    <p class="sub">Enter your account password to continue.<br>Laptop SN: <span class="sn"><?php echo h($sn); ?></span></p>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="post" action="gate-auth.php" autocomplete="on">
        <input type="hidden" name="sn" value="<?php echo h($sn); ?>">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="your@email.com" autocomplete="username"
                   value="<?php echo h($_POST['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
                <button type="button" class="toggle" onclick="togglePw()">Show</button>
            </div>
        </div>
        <button type="submit">Continue</button>
    </form>
</div>
<script>
function togglePw() {
    var input = document.getElementById('password');
    var btn = document.querySelector('.pw-wrap .toggle');
    if (input.type === 'password') { input.type = 'text'; btn.textContent = 'Hide'; }
    else { input.type = 'password'; btn.textContent = 'Show'; }
}
</script>
</body>
</html>
