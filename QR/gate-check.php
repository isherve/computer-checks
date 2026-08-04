<?php
/**
 * Gate Check Portal — enter serial number to open authenticated gate log flow.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/connection.php';

$loggedIn = isset($_SESSION['user_type'], $_SESSION['email']);
$isAdmin = $loggedIn && ($_SESSION['user_type'] === 'Admin');
$error = '';
$sn = trim((string)($_GET['sn'] ?? $_POST['sn'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sn = trim((string)($_POST['sn'] ?? ''));
    if ($sn === '') {
        $error = 'Please enter a computer serial number.';
    } else {
        $stmt = $pdo->prepare('SELECT sn FROM computer_info WHERE sn = ? LIMIT 1');
        $stmt->execute([$sn]);
        if (!$stmt->fetch()) {
            $error = 'No laptop found with that serial number.';
        } else {
            header('Location: gate-auth.php?sn=' . rawurlencode($sn));
            exit;
        }
    }
}

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$activePage = 'gate-check';
$dash = $isAdmin ? 'admin-dashboard.php' : 'user-dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gate Check Portal | QR-BASED COMPUTER TRACKING SOLUTION</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="icons/css/all.css">
  <style>
    body { font-family: Poppins, Arial, sans-serif; background: #f8f9fa; }
    header {
      background-color: #343a40; color: #fff; padding: 8px 0; text-align: center;
      position: fixed; top: 0; width: 100%; z-index: 999;
    }
    .logo { max-width: 120px; }
    .sidebar {
      position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
      padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0,0,0,.1);
      width: 220px; background: #f8f9fa;
    }
    .main-content { margin-left: <?php echo $loggedIn ? '230px' : '0'; ?>; padding: 100px 20px 40px; }
    .card-box {
      max-width: 520px; margin: 0 auto; background: #fff; border-radius: 10px;
      box-shadow: 0 0 12px rgba(0,0,0,.08); padding: 24px;
    }
    .hint { color: #6c757d; font-size: 0.9rem; }
  </style>
</head>
<body>
<header>
  <?php if ($loggedIn): ?>
  <img src="img/QR-logo.jpg" alt="Logo" class="logo img-fluid float-left ml-2">
  <?php endif; ?>
  <h1><?php echo $isAdmin ? 'Admin' : ($loggedIn ? 'User' : 'Gate'); ?> | Gate Check Portal</h1>
  <h5>QR-BASED COMPUTER TRACKING SOLUTION</h5>
</header>

<?php if ($loggedIn): ?>
<div class="sidebar">
  <?php require __DIR__ . '/sidebar_nav.php'; ?>
</div>
<?php endif; ?>

<div class="main-content">
  <div class="card-box">
    <h4 class="mb-3">Gate Check Portal</h4>
    <p class="hint">
      Enter a registered computer serial number to open the gate authentication and
      check-in / check-out form. You can also scan a printed QR code with a phone camera.
    </p>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>
    <form method="post" action="gate-check.php">
      <div class="form-group">
        <label for="sn">Serial Number</label>
        <input class="form-control" type="text" id="sn" name="sn" value="<?php echo h($sn); ?>" required placeholder="e.g. PF4AJ1SM">
      </div>
      <button class="btn btn-primary btn-block" type="submit">Continue to Gate Auth</button>
    </form>
    <hr>
    <p class="mb-1"><strong>Available portals in this system:</strong></p>
    <ul class="mb-3">
      <li>Admin portal — manage users, laptops, logs, gate check</li>
      <li>Gate Officer portal — record/view laptops, QR, logs, gate check</li>
      <li>Gate Check portal — authenticate and log check-in/out after QR or serial entry</li>
    </ul>
    <?php if ($loggedIn): ?>
      <a class="btn btn-secondary btn-sm" href="<?php echo h($dash); ?>">Back to Dashboard</a>
    <?php else: ?>
      <a class="btn btn-secondary btn-sm" href="login.php">Staff Login</a>
      <a class="btn btn-link btn-sm" href="index.php">Home</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
