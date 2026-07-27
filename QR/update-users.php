<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
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

$nid = isset($_GET['nid']) ? trim((string)$_GET['nid']) : '';
$error = isset($_GET['error']) ? (string)$_GET['error'] : '';
$result = null;

if ($nid !== '') {
    $query = 'SELECT * FROM users WHERE nid = :nid LIMIT 1';
    $statement = $pdo->prepare($query);
    $statement->execute([':nid' => $nid]);
    $result = $statement->fetch(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Users</title>
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
    .main-content { margin-left: 220px; padding: 10px; }
    .logo { max-width: 120px; }
    .image-container { text-align: center; border-radius: 50%; }
    header {
      background-color: #343a40; color: #ffffff; padding: 3px 0;
      text-align: center; position: fixed; top: 0; width: 100%; z-index: 999;
    }
    i { color: black; }
    .record-summary {
      background-color: #fff; border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px; max-width: 640px;
    }
    section { margin-top: 12vh; padding: 20px; }
    .form-control { max-width: 420px; }
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
        <a class="nav-link active" href="view-users.php"><i class="fa fa-eye"></i> View Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="add-users.php"><i class="fa fa-plus-circle"></i> Add new user</a>
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
<section>
  <div class="record-summary">
    <p>Welcome, <span style="color:#3498db;font-weight:bold;"><?php echo htmlspecialchars($displayName); ?>!</span></p>
    <h5 style="color:green;font-weight:bold;">Update User's Data</h5>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!$result): ?>
      <div class="alert alert-warning">User not found. <a href="view-users.php">Back to users</a></div>
    <?php else: ?>
    <form action="updateUsersBack.php" method="post">
      <div class="form-group">
        <label for="user_type">Role</label>
        <select class="form-control" id="user_type" name="user_type" required>
          <option value="Admin" <?php echo $result->user_type === 'Admin' ? 'selected' : ''; ?>>Admin</option>
          <option value="Guest" <?php echo $result->user_type === 'Guest' ? 'selected' : ''; ?>>Guest</option>
        </select>
      </div>

      <div class="form-group">
        <label for="names">Names</label>
        <input class="form-control" type="text" id="names" name="names" value="<?php echo htmlspecialchars($result->names); ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($result->email); ?>" required>
      </div>

      <div class="form-group">
        <label for="nid">NID</label>
        <input class="form-control" type="text" id="nid" name="nid" value="<?php echo htmlspecialchars($result->nid); ?>" required>
        <input type="hidden" name="original_nid" value="<?php echo htmlspecialchars($result->nid); ?>">
      </div>

      <button class="btn btn-primary" type="submit" name="update">Save Changes</button>
      <a class="btn btn-secondary" href="view-users.php">Cancel</a>
    </form>
    <?php endif; ?>
  </div>
</section>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
