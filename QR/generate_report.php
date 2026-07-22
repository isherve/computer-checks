<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

// Include the database connection
require_once 'connection.php';

// Fetch username based on user Type
$user_type = $_SESSION['user_type'];
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT names FROM users WHERE user_type = ? AND email = ?");
$stmt->execute([$user_type, $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Display the email
$email = $user['names'];
?>

<?php
// Database connection
require_once 'connection.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$period = isset($_GET['period']) ? $_GET['period'] : '';
$overall = isset($_GET['overall']) ? $_GET['overall'] : '';

$query = "";
$title = "";
$params = [];

if ($overall) {
    switch ($period) {
        case 'daily':
            $date = $_GET['date'];
            $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE DATE(date) = :date";
            $title = "Overall Daily Report on " . $date;
            $params = [':date' => $date];
            break;
        case 'weekly':
            $startDate = $_GET['start_date'];
            $endDate = $_GET['end_date'];
            $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE DATE(date) BETWEEN :start_date AND :end_date";
            $title = "Overall Weekly Report from " . $startDate . " to " . $endDate;
            $params = [':start_date' => $startDate, ':end_date' => $endDate];
            break;
        case 'monthly':
            $month = $_GET['month'];
            $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE DATE_FORMAT(date, '%Y-%m') = :month";
            $title = "Overall Monthly Report for " . $month;
            $params = [':month' => $month];
            break;
        case 'annual':
            $year = $_GET['year'];
            $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE YEAR(date) = :year";
            $title = "Overall Annual Report for " . $year;
            $params = [':year' => $year];
            break;
        case 'individual':
            $date = $_GET['date'];
            $sn = $_GET['sn'];
            $query = "SELECT log_id, sn, model, type, owno, owname, action, comment, date FROM logs WHERE DATE(date) = :date AND sn = :sn";
            $title = "Overall Individual Report on " . $date . " for Serial Number " . $sn;
            $params = [':date' => $date, ':sn' => $sn];
            break;
        default:
            die("Invalid period selected.");
    }
} else {
    switch ($period) {
        case 'daily':
            $date = $_GET['date'];
            $start_hour = $_GET['start_hour'];
            $start_minute = $_GET['start_minute'];
            $end_hour = $_GET['end_hour'];
            $end_minute = $_GET['end_minute'];
            
            // Combine date and hour into a single datetime string
            $start_datetime = $date . ' ' . $start_hour . ':' . $start_minute . ':00';
            $end_datetime = $date . ' ' . $end_hour . ':' . $end_minute . ':59';

            $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment 
                      FROM logs 
                      WHERE action = :action 
                      AND date BETWEEN :start_datetime AND :end_datetime";
            
            $title = "Daily Report of Computers " . ($action == 'check-in' ? "Checked In" : "Checked Out") . 
                     " on " . $date . " from " . $start_hour . ":" . $start_minute . " to " . $end_hour . ":" . $end_minute;
            
            $params = [
                ':action' => $action,
                ':start_datetime' => $start_datetime,
                ':end_datetime' => $end_datetime
            ];
            break;
        case 'weekly':
            $startDate = $_GET['start_date'];
            $endDate = $_GET['end_date'];
            $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND DATE(date) BETWEEN :start_date AND :end_date";
            $title = "Weekly Report of Computers " . ($action == 'check-in' ? "Checked In" : "Checked Out") . " from " . $startDate . " to " . $endDate;
            $params = [':action' => $action, ':start_date' => $startDate, ':end_date' => $endDate];
            break;
        case 'monthly':
            $month = $_GET['month'];
            $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND DATE_FORMAT(date, '%Y-%m') = :month";
            $title = "Monthly Report of Computers " . ($action == 'check-in' ? "Checked In" : "Checked Out") . " for " . $month;
            $params = [':action' => $action, ':month' => $month];
            break;
        case 'annual':
            $year = $_GET['year'];
            $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND YEAR(date) = :year";
            $title = "Annual Report of Computers " . ($action == 'check-in' ? "Checked In" : "Checked Out") . " for " . $year;
            $params = [':action' => $action, ':year' => $year];
            break;
        case 'individual':
            $date = $_GET['date'];
            $sn = $_GET['sn'];
            $query = "SELECT log_id, sn, type, owname AS name, date AS check_time, comment FROM logs WHERE action = :action AND DATE(date) = :date AND sn = :sn";
            $title = "Individual Report of Computers " . ($action == 'check-in' ? "Checked In" : "Checked Out") . " on " . $date . " with Serial Number " . $sn;
            $params = [':action' => $action, ':date' => $date, ':sn' => $sn];
            break;
        default:
            die("Invalid period selected.");
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="icons/css/all.css">
    <style>
        body {
            display: flex;
            font-family: poppins;
        }
        .container {
            margin-top: 50px;
            margin-left: 300px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .sidebar {
            width: 150px;
            background-color: #f8f9fa;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 19vh;
            left: 0;
        }
        .content {
            flex: 1;
            padding: 10px;
            margin-left: 150px;
        }
        .header {
            color: #ffffff;
            padding: 20px 0;
        }
        .logo {
            max-width: 120px; /* Adjust width as needed */
        }
        .image-container {
            text-align: center;
            border-radius: 50%;
        }
        header {
            background-color: #343a40;
            color: #ffffff;
            padding: 1em;
            text-align: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999; /* Ensure header stays above other content */
        }
        section {
            margin-top: 19Svh;
            display: flex;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        @media print {
            .btn, .sidebar {
                display: none;
            }
            .content {
                width: 100%;
                padding: 0;
            }
            title {
                display: none;
            }
            nav {
                display: none;
            }
            input {
                display: none;
            }
            button {
                display: none;
            }
            header {
                display: none;
            }
        }
    </style>
</head>
<body>
<header>
    <img src="img/QR-logo.JPG" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
    <h1>User | Dashboard</h1>
    <h5>Computer Checks</h5>
</header>
<section>
    <div class="sidebar">
        <h4>Menu</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="user-dashboard.php"><i class="fa fa-home" aria-hidden="true"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="report.php"><i class="fa fa-book" aria-hidden="true"></i>Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <button class="btn btn-primary mb-3" onclick="window.print()">Print Report</button>
        <h5><?php echo htmlspecialchars($title); ?></h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <?php if ($overall): ?>
                        <th>No</th>
                        <th>Serial Number</th>
                        <th>Model</th>
                        <th>Owner's Type</th>
                        <th>Owner ID</th>
                        <th>Owner's Name</th>
                        <th>Status</th>
                        <th>Check Time</th>
                        <th>Comment</th>
                        
                    <?php else: ?>
                        <th>No</th>
                        <th>SN</th>
                        <th>Owner's Type</th>
                        <th>Name</th>
                        <th>Check Time</th>
                        <th>Comment</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($result) > 0) {
                    $no = 1;
                    foreach ($result as $row) {
                        echo "<tr>";
                        if ($overall) {
                            echo "<td>" . $no++ . "</td>
                                  <td>" . htmlspecialchars($row['sn']) . "</td>
                                  <td>" . htmlspecialchars($row['model']) . "</td>
                                  <td>" . htmlspecialchars($row['type']) . "</td>
                                  <td>" . htmlspecialchars($row['owno']) . "</td>
                                  <td>" . htmlspecialchars($row['owname']) . "</td>
                                  <td>" . htmlspecialchars($row['action']) . "</td>
                                  <td>" . htmlspecialchars($row['date']) . "</td>
                                  <td>" . htmlspecialchars($row['comment']) . "</td>";
                        } else {
                            echo "<td>" . $no++ . "</td>
                                  <td>" . htmlspecialchars($row['sn']) . "</td>
                                  <td>" . htmlspecialchars($row['type']) . "</td>
                                  <td>" . htmlspecialchars($row['name']) . "</td>
                                  <td>" . htmlspecialchars($row['check_time']) . "</td>
                                  <td>" . htmlspecialchars($row['comment']) . "</td>";
            
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</section>
</body>
</html>
