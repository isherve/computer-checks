<?php
include 'connection.php'; // Include your connection file

// Function to check if the current password matches the stored password
function checkCurrentPassword($pdo, $nid, $currentPassword) {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE nid = :nid");
    $stmt->execute(array(':nid' => $nid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify($currentPassword, $row['password'])) {
        return true;
    } else {
        return false;
    }
}

if (isset($_POST['submit'])) {
    $nid = $_POST['nid'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if (checkCurrentPassword($pdo, $nid, $currentPassword)) {
        if ($newPassword === $confirmPassword) {
            // Hash the new password before storing it in the database
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE nid = :nid");
            $stmt->execute(array(':password' => $hashedPassword, ':nid' => $nid));

            echo "Password changed successfully.";
            header("location:success.php");
        } else {
            echo "New password and confirm password do not match.";
        }
    } else {
        echo "Current password is incorrect.";
    }
}
?>
