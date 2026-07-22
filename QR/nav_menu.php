<?php
/**
 * Shared sidebar nav for Guest / Admin (Logs + Reports both listed).
 * Expects: $dash, $isAdmin, $activeNav ('dashboard'|'laptops'|'record'|'users'|'adduser'|'logs'|'reports'|'password')
 */
if (!isset($activeNav)) {
    $activeNav = '';
}
$isAdmin = !empty($isAdmin);
?>
<ul class="nav flex-column">
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($dash); ?>">
      <i class="fa fa-home" aria-hidden="true"></i> Dashboard
    </a>
  </li>
  <?php if ($isAdmin): ?>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'users' ? ' active' : ''; ?>" href="view-users.php">
      <i class="fa fa-eye" aria-hidden="true"></i> View Users
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'adduser' ? ' active' : ''; ?>" href="add-users.php">
      <i class="fa fa-plus-circle" aria-hidden="true"></i> Add new user
    </a>
  </li>
  <?php else: ?>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'laptops' ? ' active' : ''; ?>" href="view-laptops.php">
      <i class="fa fa-eye" aria-hidden="true"></i> View Laptops
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'record' ? ' active' : ''; ?>" href="record-computers.php">
      <i class="fa fa-plus-circle" aria-hidden="true"></i> Record New Laptop
    </a>
  </li>
  <?php endif; ?>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'logs' ? ' active' : ''; ?>" href="report.php">
      <i class="fa fa-book" aria-hidden="true"></i> Logs
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'reports' ? ' active' : ''; ?>" href="reports.php">
      <i class="fa fa-file-alt" aria-hidden="true"></i> Reports
    </a>
  </li>
  <?php if (!$isAdmin): ?>
  <li class="nav-item">
    <a class="nav-link<?php echo $activeNav === 'password' ? ' active' : ''; ?>" href="change-password.php">
      <i class="fa fa-pencil" aria-hidden="true"></i> Change Password
    </a>
  </li>
  <?php endif; ?>
  <li class="nav-item">
    <a class="nav-link" href="logout.php">
      <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
    </a>
  </li>
</ul>
