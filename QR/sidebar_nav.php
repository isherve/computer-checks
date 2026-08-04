<?php
/**
 * Shared sidebar for all authenticated portals.
 * Set $activePage before include, e.g. 'dashboard', 'view-users', 'view-laptops', ...
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$role = $_SESSION['user_type'] ?? '';
$isAdmin = ($role === 'Admin');
$activePage = $activePage ?? '';

function app_nav_active(string $key, string $active): string
{
    return $key === $active ? ' active' : '';
}

$dash = $isAdmin ? 'admin-dashboard.php' : 'user-dashboard.php';
?>
<nav class="sidebar-sticky mt-5">
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('dashboard', $activePage); ?>" href="<?php echo htmlspecialchars($dash); ?>">
        <i class="fa fa-home" aria-hidden="true"></i> Dashboard
      </a>
    </li>

    <?php if ($isAdmin): ?>
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('view-users', $activePage); ?>" href="view-users.php">
        <i class="fa fa-users" aria-hidden="true"></i> View Users
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('add-users', $activePage); ?>" href="add-users.php">
        <i class="fa fa-user-plus" aria-hidden="true"></i> Add New User
      </a>
    </li>
    <?php endif; ?>

    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('view-laptops', $activePage); ?>" href="view-laptops.php">
        <i class="fa fa-laptop" aria-hidden="true"></i> View Laptops
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('record-computers', $activePage); ?>" href="record-computers.php">
        <i class="fa fa-plus-circle" aria-hidden="true"></i> Record New Laptop
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('logs', $activePage); ?>" href="report.php">
        <i class="fa fa-book" aria-hidden="true"></i> Logs
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('gate-check', $activePage); ?>" href="gate-check.php">
        <i class="fa fa-qrcode" aria-hidden="true"></i> Gate Check Portal
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link<?php echo app_nav_active('change-password', $activePage); ?>" href="change-password.php">
        <i class="fa fa-pencil" aria-hidden="true"></i> Change Password
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="logout.php">
        <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
      </a>
    </li>
  </ul>
</nav>
