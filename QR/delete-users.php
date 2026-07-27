<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

if (isset($_GET['nid']) && $_GET['nid'] !== '') {
    $nid = trim((string)$_GET['nid']);

    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE nid = :nid');
        $stmt->execute([':nid' => $nid]);

        if ($stmt->rowCount() > 0) {
            app_db_persist();
            header('Location: view-users.php?deleted=1');
            exit();
        }

        header('Location: view-users.php?error=' . urlencode('User not found.'));
        exit();
    } catch (PDOException $e) {
        header('Location: view-users.php?error=' . urlencode('Could not delete user.'));
        exit();
    }
}

header('Location: view-users.php');
exit();
