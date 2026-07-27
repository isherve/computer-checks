<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

$sessionType = $_SESSION['user_type'];
$sessionEmail = $_SESSION['email'];
$stmt = $pdo->prepare('SELECT names FROM users WHERE user_type = ? AND email = ?');
$stmt->execute([$sessionType, $sessionEmail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$displayName = $user['names'] ?? $sessionEmail;

$message = null;
$Error = null;
$defaultPasswordHint = null;

// Keep form values after failed submit
$form = [
    'user_type' => 'Guest',
    'nid' => '',
    'names' => '',
    'email' => '',
    'password_mode' => 'default',
];

if (isset($_POST['submit'])) {
    $form['user_type'] = trim((string)($_POST['user_type'] ?? 'Guest'));
    $form['nid'] = trim((string)($_POST['nid'] ?? ''));
    $form['names'] = trim((string)($_POST['names'] ?? ''));
    $form['email'] = trim((string)($_POST['email'] ?? ''));
    $form['password_mode'] = ($_POST['password_mode'] ?? 'default') === 'custom' ? 'custom' : 'default';

    $user_type = $form['user_type'];
    $nid = $form['nid'];
    $names = $form['names'];
    $email = $form['email'];
    $passwordMode = $form['password_mode'];
    $customPassword = (string)($_POST['custom_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $allowedRoles = ['Admin', 'Guest'];

    if ($nid === '' || $names === '' || $email === '' || !in_array($user_type, $allowedRoles, true)) {
        $Error = 'Please fill all required fields with a valid role.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $Error = 'Please enter a valid email address.';
    } else {
        if ($passwordMode === 'custom') {
            if (strlen($customPassword) < 5) {
                $Error = 'Custom password must be at least 5 characters.';
            } elseif ($customPassword !== $confirmPassword) {
                $Error = 'Custom password and confirmation do not match.';
            } else {
                $plainPassword = $customPassword;
            }
        } else {
            // Default password depends on selected role
            $plainPassword = $user_type === 'Admin' ? 'admin123' : 'gate@2026';
        }

        if (!isset($Error)) {
            try {
                $sql_check = 'SELECT COUNT(*) FROM users WHERE nid = :nid OR email = :email';
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([':nid' => $nid, ':email' => $email]);
                $count = (int)$stmt_check->fetchColumn();

                if ($count > 0) {
                    $Error = "Error: User's NID or email already exists.";
                } else {
                    $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (user_type, nid, names, email, password) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$user_type, $nid, $names, $email, $hashed]);
                    app_db_persist();

                    if ($passwordMode === 'default') {
                        $defaultPasswordHint = $user_type === 'Admin' ? 'admin123' : 'gate@2026';
                        $_SESSION['flash_user_added'] = 'User added successfully. Default password: ' . $defaultPasswordHint;
                    } else {
                        $_SESSION['flash_user_added'] = 'User added successfully with the custom password you set.';
                    }

                    header('Location: view-users.php?added=1');
                    exit();
                }
            } catch (PDOException $e) {
                $Error = 'Could not add user. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Users</title>
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
    .password-box {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 14px;
      background: #f8f9fa;
    }
    #customPasswordFields { display: none; }
  </style>
</head>
<body>
<header>
  <img src="img/QR-logo.jpg" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
  <h1>Admin | Dashboard</h1>
  <h5>Computer Checks</h5>
</header>

<div class="sidebar">
  <nav class="sidebar-sticky mt-5">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="admin-dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="view-users.php"><i class="fa fa-eye"></i> View Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="add-users.php"><i class="fa fa-plus-circle"></i> Add new user</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="change-password.php"><i class="fa fa-pencil"></i> Change Password</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
      </li>
    </ul>
  </nav>
</div>

<div class="main-content">
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title text-center mb-0">Add New User</h5>
            <p class="text-center text-muted small mb-0">Welcome, <?php echo htmlspecialchars($displayName); ?></p>
          </div>
          <div class="card-body">
            <?php if (isset($message) || isset($Error)) : ?>
              <p class="<?php echo isset($message) ? 'alert alert-success' : 'alert alert-danger'; ?>" style="text-align:center;">
                <?php echo htmlspecialchars(isset($message) ? $message : $Error); ?>
              </p>
            <?php endif; ?>

            <form class="form-container" action="" method="POST" id="addUserForm">
              <div class="form-group">
                <label for="user_type">User Type</label>
                <select class="form-control" name="user_type" id="user_type" required>
                  <option value="Admin" <?php echo $form['user_type'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                  <option value="Guest" <?php echo $form['user_type'] === 'Guest' ? 'selected' : ''; ?>>Gate_Officer (Guest)</option>
                </select>
              </div>

              <div class="form-group">
                <label for="nid">NID</label>
                <input type="text" class="form-control" id="nid" name="nid" placeholder="Enter National ID / Staff ID" value="<?php echo htmlspecialchars($form['nid']); ?>" required>
              </div>

              <div class="form-group">
                <label for="userName">User's Name</label>
                <input type="text" class="form-control" id="userName" name="names" placeholder="Enter User's Name" value="<?php echo htmlspecialchars($form['names']); ?>" required>
              </div>

              <div class="form-group">
                <label for="email">User's Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter User's Email" value="<?php echo htmlspecialchars($form['email']); ?>" required>
              </div>

              <div class="form-group password-box">
                <label class="font-weight-bold">Password option</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="password_mode" id="pwdDefault" value="default" <?php echo $form['password_mode'] === 'default' ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="pwdDefault">
                    Use default password <span class="text-muted">(Admin: admin123, Gate_Officer: gate@2026)</span>
                  </label>
                </div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="password_mode" id="pwdCustom" value="custom" <?php echo $form['password_mode'] === 'custom' ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="pwdCustom">
                    Set a new custom password
                  </label>
                </div>

                <div id="customPasswordFields">
                  <div class="form-group mb-2">
                    <label for="custom_password">New password</label>
                    <input type="password" class="form-control" id="custom_password" name="custom_password" minlength="5" placeholder="At least 5 characters">
                  </div>
                  <div class="form-group mb-0">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="5" placeholder="Re-enter password">
                  </div>
                </div>
              </div>

              <button type="submit" name="submit" class="btn btn-primary btn-block">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
(function () {
  var customBox = document.getElementById('customPasswordFields');
  var pwdCustom = document.getElementById('pwdCustom');
  var pwdDefault = document.getElementById('pwdDefault');
  var customPassword = document.getElementById('custom_password');
  var confirmPassword = document.getElementById('confirm_password');

  function syncPasswordMode() {
    var isCustom = pwdCustom.checked;
    customBox.style.display = isCustom ? 'block' : 'none';
    customPassword.required = isCustom;
    confirmPassword.required = isCustom;
    if (!isCustom) {
      customPassword.value = '';
      confirmPassword.value = '';
    }
  }

  pwdCustom.addEventListener('change', syncPasswordMode);
  pwdDefault.addEventListener('change', syncPasswordMode);
  syncPasswordMode();
})();
</script>
</body>
</html>
