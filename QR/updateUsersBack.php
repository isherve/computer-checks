<?php
/**
 * Handle admin "Edit user" form submit.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update'])) {
    header('Location: view-users.php');
    exit();
}

$user_type = trim((string)($_POST['user_type'] ?? ''));
$nid = trim((string)($_POST['nid'] ?? ''));
$names = trim((string)($_POST['names'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

$allowedRoles = ['Admin', 'Guest'];
if ($nid === '' || $names === '' || $email === '' || !in_array($user_type, $allowedRoles, true)) {
    header('Location: update-users.php?nid=' . urlencode($nid) . '&error=' . urlencode('Please fill all fields with a valid role (Admin or Guest).'));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: update-users.php?nid=' . urlencode($nid) . '&error=' . urlencode('Invalid email address.'));
    exit();
}

try {
    // Ensure email is not used by a different user
    $check = $pdo->prepare('SELECT nid FROM users WHERE email = :email AND nid <> :nid LIMIT 1');
    $check->execute([':email' => $email, ':nid' => $nid]);
    if ($check->fetch()) {
        header('Location: update-users.php?nid=' . urlencode($nid) . '&error=' . urlencode('That email is already used by another user.'));
        exit();
    }

    $query = 'UPDATE users SET user_type = :user_type, names = :names, email = :email WHERE nid = :nid';
    $statement = $pdo->prepare($query);
    $ok = $statement->execute([
        ':user_type' => $user_type,
        ':names' => $names,
        ':email' => $email,
        ':nid' => $nid,
    ]);

    if ($ok && $statement->rowCount() >= 0) {
        app_db_persist();
        header('Location: view-users.php?updated=1');
        exit();
    }

    header('Location: update-users.php?nid=' . urlencode($nid) . '&error=' . urlencode('No changes were saved.'));
    exit();
} catch (PDOException $e) {
    header('Location: update-users.php?nid=' . urlencode($nid) . '&error=' . urlencode('Update failed. Please try again.'));
    exit();
}
