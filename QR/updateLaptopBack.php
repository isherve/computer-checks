<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_type'], $_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update'])) {
    header('Location: view-laptops.php');
    exit();
}

$originalSn = trim((string)($_POST['original_sn'] ?? ''));
$sn = trim((string)($_POST['sn'] ?? ''));
$model = trim((string)($_POST['model'] ?? ''));
$type = trim((string)($_POST['type'] ?? ''));
$owno = trim((string)($_POST['owno'] ?? ''));
$owname = trim((string)($_POST['owname'] ?? ''));

$allowedModels = ['LENOVO', 'HP', 'DELL', 'MAC BOOK', 'SAMSUNG', 'LG'];
$allowedTypes = ['student', 'staff', 'other'];

if ($originalSn === '' || $sn === '' || $model === '' || $type === '' || $owno === '' || $owname === '') {
    header('Location: update-laptop.php?sn=' . urlencode($originalSn) . '&error=' . urlencode('Please fill all fields.'));
    exit();
}

if (!in_array($model, $allowedModels, true) || !in_array($type, $allowedTypes, true)) {
    header('Location: update-laptop.php?sn=' . urlencode($originalSn) . '&error=' . urlencode('Invalid model or owner type.'));
    exit();
}

try {
    // Ensure laptop exists
    $exists = $pdo->prepare('SELECT id FROM computer_info WHERE sn = :sn LIMIT 1');
    $exists->execute([':sn' => $originalSn]);
    if (!$exists->fetch()) {
        header('Location: view-laptops.php?error=' . urlencode('Laptop not found.'));
        exit();
    }

    // Serial number must remain unique
    if ($sn !== $originalSn) {
        $snCheck = $pdo->prepare('SELECT id FROM computer_info WHERE sn = :sn LIMIT 1');
        $snCheck->execute([':sn' => $sn]);
        if ($snCheck->fetch()) {
            header('Location: update-laptop.php?sn=' . urlencode($originalSn) . '&error=' . urlencode('That serial number already exists.'));
            exit();
        }
    }

    // Owner number must remain unique for other laptops
    $ownoCheck = $pdo->prepare('SELECT id FROM computer_info WHERE owno = :owno AND sn <> :original_sn LIMIT 1');
    $ownoCheck->execute([':owno' => $owno, ':original_sn' => $originalSn]);
    if ($ownoCheck->fetch()) {
        header('Location: update-laptop.php?sn=' . urlencode($originalSn) . '&error=' . urlencode('That owner identification is already used by another laptop.'));
        exit();
    }

    $stmt = $pdo->prepare(
        'UPDATE computer_info
         SET sn = :sn, model = :model, type = :type, owno = :owno, owname = :owname
         WHERE sn = :original_sn'
    );
    $ok = $stmt->execute([
        ':sn' => $sn,
        ':model' => $model,
        ':type' => $type,
        ':owno' => $owno,
        ':owname' => $owname,
        ':original_sn' => $originalSn,
    ]);

    if ($ok) {
        if (function_exists('app_db_persist')) {
            app_db_persist();
        }
        header('Location: view-laptops.php?updated=1');
        exit();
    }

    header('Location: update-laptop.php?sn=' . urlencode($originalSn) . '&error=' . urlencode('No changes were saved.'));
    exit();
} catch (PDOException $e) {
    header('Location: update-laptop.php?sn=' . urlencode($originalSn) . '&error=' . urlencode('Update failed. Please try again.'));
    exit();
}
