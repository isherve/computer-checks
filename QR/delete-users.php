<?php
// Include the database connection file
require_once 'connection.php';

// Check if the ID parameter is provided via GET request
if (isset($_GET['nid']) && !empty($_GET['nid'])) {
    // Sanitize the ID parameter to prevent SQL injection
    $nid = filter_var($_GET['nid'], FILTER_SANITIZE_STRING);

    try {
        // Prepare a DELETE statement
        $stmt = $pdo->prepare("DELETE FROM users WHERE nid = :nid");

        // Bind the ID parameter
        $stmt->bindParam(':nid', $nid, PDO::PARAM_STR);

        // Execute the DELETE statement
        $stmt->execute();

        // Check if any row was affected
        if ($stmt->rowCount() > 0) {
            echo "Row with ID $nid deleted successfully.";
            header("location:view-users.php");
        } else {
            echo "No rows deleted. Row with ID $nid may not exist.";
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No ID parameter provided.";
}
?>
