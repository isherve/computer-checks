<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type'], $_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

if (!isset($_GET['sn']) || trim((string)$_GET['sn']) === '') {
    header('Location: view-laptops.php');
    exit();
}

$sn = trim((string)$_GET['sn']);

try {
    $stmt = $pdo->prepare('DELETE FROM computer_info WHERE sn = :sn');
    $stmt->execute([':sn' => $sn]);

    if ($stmt->rowCount() > 0) {
        if (function_exists('app_db_persist')) {
            app_db_persist();
        }
        header('Location: view-laptops.php?deleted=1');
        exit();
    }

    header('Location: view-laptops.php?error=' . urlencode('Laptop not found.'));
    exit();
} catch (PDOException $e) {
    header('Location: view-laptops.php?error=' . urlencode('Could not delete laptop.'));
    exit();
}
