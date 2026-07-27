<?php
/**
 * Handle Change Password form submit (logged-in users).
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user_type'], $_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

function redirect_password(string $status, string $message = ''): void
{
    $q = 'status=' . urlencode($status);
    if ($message !== '') {
        $q .= '&msg=' . urlencode($message);
    }
    header('Location: change-password.php?' . $q);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit'])) {
    header('Location: change-password.php');
    exit();
}

$sessionEmail = $_SESSION['email'];
$sessionType = $_SESSION['user_type'];
$currentPassword = (string)($_POST['currentPassword'] ?? '');
$newPassword = (string)($_POST['newPassword'] ?? '');
$confirmPassword = (string)($_POST['confirmPassword'] ?? '');

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    redirect_password('error', 'Please fill in all password fields.');
}

if (strlen($newPassword) < 5) {
    redirect_password('error', 'New password must be at least 5 characters.');
}

if ($newPassword !== $confirmPassword) {
    redirect_password('error', 'New password and confirm password do not match.');
}

try {
    $stmt = $pdo->prepare('SELECT nid, password FROM users WHERE email = :email AND user_type = :user_type LIMIT 1');
    $stmt->execute([
        ':email' => $sessionEmail,
        ':user_type' => $sessionType,
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        redirect_password('error', 'Logged-in account was not found.');
    }

    $stored = (string)($user['password'] ?? '');
    $currentOk = false;
    if ($stored !== '' && password_verify($currentPassword, $stored)) {
        $currentOk = true;
    } elseif ($stored !== '' && hash_equals($stored, $currentPassword)) {
        // Legacy plain-text password support
        $currentOk = true;
    }

    if (!$currentOk) {
        redirect_password('error', 'Current password is incorrect.');
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email AND user_type = :user_type');
    $update->execute([
        ':password' => $hashedPassword,
        ':email' => $sessionEmail,
        ':user_type' => $sessionType,
    ]);

    redirect_password('success', 'Password changed successfully.');
} catch (PDOException $e) {
    redirect_password('error', 'Could not update password. Please try again.');
}
