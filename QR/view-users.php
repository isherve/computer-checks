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

$search = '';
$users = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $search = trim((string)($_POST['names'] ?? ''));
}

try {
    if ($search !== '') {
        $stm = $pdo->prepare('SELECT nid, user_type, names, email FROM users WHERE names LIKE :search ORDER BY names ASC');
        $stm->execute([':search' => '%' . $search . '%']);
    } else {
        $stm = $pdo->query('SELECT nid, user_type, names, email FROM users ORDER BY names ASC');
    }
    $users = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $loadError = 'Could not load users.';
}

$flash = null;
if (isset($_SESSION['flash_user_added'])) {
    $flash = ['type' => 'success', 'text' => $_SESSION['flash_user_added']];
    unset($_SESSION['flash_user_added']);
} elseif (isset($_GET['added'])) {
    $flash = ['type' => 'success', 'text' => 'User added successfully.'];
} elseif (isset($_GET['updated'])) {
    $flash = ['type' => 'success', 'text' => 'User updated successfully.'];
} elseif (isset($_GET['deleted'])) {
    $flash = ['type' => 'success', 'text' => 'User deleted successfully.'];
} elseif (isset($_GET['error'])) {
    $flash = ['type' => 'danger', 'text' => (string)$_GET['error']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Users</title>
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
      box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px;
    }
    section { margin-top: 12vh; padding: 20px; max-width: 1100px; }
    .table-wrapper { max-height: 420px; overflow-y: auto; }
    .table th { background: #f2f3ff; }
  </style>
</head>
<body>
<header>
  <img src="img/QR-logo.jpg" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
  <h1>Admin | Dashboard</h1>
  <h5>Device Check</h5>
</header>

<div class="sidebar">
  <nav class="sidebar-sticky mt-5">
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="admin-dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
      <li class="nav-item"><a class="nav-link active" href="view-users.php"><i class="fa fa-eye"></i> View Users</a></li>
      <li class="nav-item"><a class="nav-link" href="add-users.php"><i class="fa fa-plus-circle"></i> Add new user</a></li>
      <li class="nav-item"><a class="nav-link" href="change-password.php"><i class="fa fa-pencil"></i> Change Password</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
    </ul>
  </nav>
</div>

<div class="main-content">
<section>
  <div class="record-summary">
    <p>Welcome, <span style="color:#3498db;font-weight:bold;"><?php echo htmlspecialchars($displayName); ?>!</span></p>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0" style="color:green;">Users in the system</h5>
      <a href="add-users.php" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Add User</a>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['text']); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($loadError)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($loadError); ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="form-inline mb-3">
      <input type="text" name="names" class="form-control mr-2" placeholder="Search by name" value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit" name="submit" class="btn btn-info btn-sm">Search</button>
      <?php if ($search !== ''): ?>
        <a href="view-users.php" class="btn btn-light btn-sm ml-2">Clear</a>
      <?php endif; ?>
    </form>

    <div class="table-wrapper">
      <table class="table table-striped table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>NID</th>
            <th>Role</th>
            <th>Name</th>
            <th>Email</th>
            <th colspan="2" class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted">No users found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $i => $row): ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo htmlspecialchars($row['nid']); ?></td>
              <td><?php echo htmlspecialchars($row['user_type'] === 'Guest' ? 'Gate Officer' : $row['user_type']); ?></td>
              <td><?php echo htmlspecialchars($row['names']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td class="text-center">
                <a href="update-users.php?nid=<?php echo urlencode($row['nid']); ?>" class="btn btn-info btn-sm">Edit</a>
              </td>
              <td class="text-center">
                <a href="delete-users.php?nid=<?php echo urlencode($row['nid']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <p class="text-muted small mt-3 mb-0">Total users: <strong><?php echo count($users); ?></strong></p>
  </div>
</section>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
