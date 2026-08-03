<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type'], $_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

$user_type = $_SESSION['user_type'];
$email = $_SESSION['email'];
$isAdmin = ($user_type === 'Admin');
$dash = $isAdmin ? 'admin-dashboard.php' : 'user-dashboard.php';

$stmt = $pdo->prepare('SELECT names FROM users WHERE user_type = ? AND email = ?');
$stmt->execute([$user_type, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$displayName = $user['names'] ?? $email;

$status = $_GET['status'] ?? '';
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="icons/css/all.css">
  <style>
    body { font-family: poppins, Arial, sans-serif; }
    .sidebar {
      position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
      padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0,0,0,.1);
      width: 220px; background: #f8f9fa;
    }
    .sidebar-sticky { padding-top: .5rem; overflow-y: auto; height: calc(100vh - 48px); }
    .main-content { margin-left: 220px; padding: 20px; }
    .logo { max-width: 120px; }
    .image-container { text-align: center; border-radius: 50%; }
    header {
      background-color: #343a40; color: #ffffff; padding: 3px 0;
      text-align: center; position: fixed; top: 0; width: 100%; z-index: 999;
    }
    i { color: black; }
    .record-summary {
      background-color: #fff; border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 24px; max-width: 520px;
    }
    section { margin-top: 12vh; padding: 20px; }
    input[type="password"] { width: 100%; max-width: 360px; }
  </style>
</head>
<body>
<header>
  <img src="img/QR-logo.jpg" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
  <h1><?php echo $isAdmin ? 'Admin' : 'User'; ?> | Change Password</h1>
  <h5>Device Check</h5>
</header>

<div class="sidebar">
  <nav class="sidebar-sticky mt-5">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="<?php echo htmlspecialchars($dash); ?>">
          <i class="fa fa-home"></i> Dashboard
        </a>
      </li>
      <?php if ($isAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" href="view-users.php"><i class="fa fa-eye"></i> View Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="add-users.php"><i class="fa fa-plus-circle"></i> Add new user</a>
      </li>
      <?php else: ?>
      <li class="nav-item">
        <a class="nav-link" href="view-laptops.php"><i class="fa fa-eye"></i> View Laptops</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="record-computers.php"><i class="fa fa-plus-circle"></i> Record New Laptop</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="report.php"><i class="fa fa-book"></i> Logs</a>
      </li>
      <?php endif; ?>
      <li class="nav-item">
        <a class="nav-link active" href="change-password.php"><i class="fa fa-pencil"></i> Change Password</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
      </li>
    </ul>
  </nav>
</div>

<div class="main-content">
<section>
  <div class="record-summary">
    <p>Welcome, <span style="color:#3498db;font-weight:bold;"><?php echo htmlspecialchars($displayName); ?>!</span></p>
    <h5 style="color:green;">Change your Password</h5>
    <p class="text-muted small mb-3">Account: <?php echo htmlspecialchars($email); ?></p>

    <?php if ($status === 'success'): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($msg ?: 'Password changed successfully.'); ?></div>
    <?php elseif ($status === 'error'): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($msg ?: 'Could not change password.'); ?></div>
    <?php endif; ?>

    <form action="update-account.php" method="post">
      <div class="form-group">
        <label for="currentPassword">Current Password</label><br>
        <input type="password" id="currentPassword" name="currentPassword" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="newPassword">New Password</label><br>
        <input type="password" id="newPassword" name="newPassword" class="form-control" minlength="5" required>
      </div>
      <div class="form-group">
        <label for="confirmPassword">Confirm New Password</label><br>
        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" minlength="5" required>
      </div>
      <button type="submit" name="submit" class="btn btn-success">Save Changes</button>
    </form>
  </div>
</section>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
