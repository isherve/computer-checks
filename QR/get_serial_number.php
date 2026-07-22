
<?php
// Include the database connection file
require_once 'connection.php';

// Query to fetch information based on serial number
$stmt = $pdo->prepare("SELECT sn, model, owno, owname FROM computer_info ORDER BY RAND() LIMIT 1");
$stmt->execute();
$serialInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Output the serial information as JSON
header('Content-Type: application/json');
echo json_encode($serialInfo);
?>
