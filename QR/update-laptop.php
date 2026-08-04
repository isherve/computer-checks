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
$stmt = $pdo->prepare('SELECT names FROM users WHERE user_type = ? AND email = ?');
$stmt->execute([$user_type, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$displayName = $user['names'] ?? $email;

$sn = isset($_GET['sn']) ? trim((string)$_GET['sn']) : '';
$error = isset($_GET['error']) ? (string)$_GET['error'] : '';
$laptop = null;

if ($sn !== '') {
    $q = $pdo->prepare('SELECT * FROM computer_info WHERE sn = :sn LIMIT 1');
    $q->execute([':sn' => $sn]);
    $laptop = $q->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Laptop</title>
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
      box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px; max-width: 640px;
    }
    section { margin-top: 12vh; padding: 20px; }
    .form-control { max-width: 420px; }
  </style>
</head>
<body>
<header>
  <img src="img/QR-logo.jpg" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
  <h1>User | Dashboard</h1>
  <h5>QR-BASED COMPUTER TRACKING SOLUTION</h5>
</header>

<div class="sidebar">
  <nav class="sidebar-sticky mt-5">
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="user-dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
      <li class="nav-item"><a class="nav-link active" href="view-laptops.php"><i class="fa fa-eye"></i> View Laptops</a></li>
      <li class="nav-item"><a class="nav-link" href="record-computers.php"><i class="fa fa-plus-circle"></i> Record New Laptop</a></li>
      <li class="nav-item"><a class="nav-link" href="report.php"><i class="fa fa-book"></i> Logs</a></li>
      <li class="nav-item"><a class="nav-link" href="change-password.php"><i class="fa fa-pencil"></i> Change Password</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
    </ul>
  </nav>
</div>

<div class="main-content">
<section>
  <div class="record-summary">
    <p>Welcome, <span style="color:#3498db;font-weight:bold;"><?php echo htmlspecialchars($displayName); ?>!</span></p>
    <h5 style="color:green;font-weight:bold;">Edit Laptop Information</h5>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!$laptop): ?>
      <div class="alert alert-warning">Laptop not found. <a href="view-laptops.php">Back to laptops</a></div>
    <?php else: ?>
    <form action="updateLaptopBack.php" method="post">
      <input type="hidden" name="original_sn" value="<?php echo htmlspecialchars($laptop['sn']); ?>">

      <div class="form-group">
        <label for="sn">Serial Number</label>
        <input class="form-control" type="text" id="sn" name="sn" value="<?php echo htmlspecialchars($laptop['sn']); ?>" required>
      </div>

      <div class="form-group">
        <label for="model">Model</label>
        <select class="form-control" id="model" name="model" required>
          <?php
          $models = ['LENOVO', 'HP', 'DELL', 'MAC BOOK', 'SAMSUNG', 'LG'];
          foreach ($models as $m) {
              $sel = (strcasecmp((string)$laptop['model'], $m) === 0) ? 'selected' : '';
              echo '<option value="' . htmlspecialchars($m) . '" ' . $sel . '>' . htmlspecialchars($m) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="type">Owner Type</label>
        <select class="form-control" id="type" name="type" required>
          <?php
          $types = ['student', 'staff', 'other'];
          foreach ($types as $t) {
              $sel = (strcasecmp((string)($laptop['type'] ?? ''), $t) === 0) ? 'selected' : '';
              echo '<option value="' . htmlspecialchars($t) . '" ' . $sel . '>' . ucfirst($t) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="owno">Owner's Identification</label>
        <input class="form-control" type="text" id="owno" name="owno" value="<?php echo htmlspecialchars($laptop['owno']); ?>" required>
      </div>

      <div class="form-group">
        <label for="owname">Owner's Name</label>
        <input class="form-control" type="text" id="owname" name="owname" value="<?php echo htmlspecialchars($laptop['owname']); ?>" required>
      </div>

      <button class="btn btn-primary" type="submit" name="update">Save Changes</button>
      <a class="btn btn-secondary" href="view-laptops.php">Cancel</a>
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
