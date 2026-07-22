<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}


// Check if the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.html');
//     exit();
// }

// Include the database connection
require_once 'connection.php';

// Fetch username based on user Type
$user_type = $_SESSION['user_type'];
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT names FROM users WHERE user_type = ? AND email = ?");
$stmt->execute([$user_type,$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Display the email
$email = $user['names'];
?>


<!DOCTYPE html>
<html>
<head>
    <title>Generate Report</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="icons/css/all.css">
    <style>
        .container {
            margin-top: 50px;
            margin-left:250px;
        }
        .form-group {
            margin-bottom: 20px;
            
        }
        body {
            display: flex;
            font-family:poppins;
        }
        .sidebar {
            width: 200px;
            background-color: #f8f9fa;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 16vh;
            left: 0;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .header {
      /* background-color: #007bff; */
      color: #ffffff;
      padding: 20px 0;
    }
    .logo {
      max-width: 120px; /* Adjust width as needed */
    }
        /* Custom Styling */
    .image-container {
      text-align: center;
      border-radius: 50%;
    }
      header {
            background-color: #343a40;
            color: #ffffff;
            padding: 1em;
            text-align: center;

/*      background-color: #007bff;*/
      color: #ffffff;
      padding: 3px 0;
      text-align: center;
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 999; /* Ensure header stays above other content */
        }
        section{
            margin-top: 10vh;
            display: flex;
        }
    </style>
    <script>
        function updateFormFields() {
            var period = document.getElementById('period').value;
            document.getElementById('date-fields').style.display = 'none';
            document.getElementById('week-fields').style.display = 'none';
            document.getElementById('month-field').style.display = 'none';
            document.getElementById('year-field').style.display = 'none';
            document.getElementById('sn-field').style.display = 'none';

            if (period === 'daily') {
                document.getElementById('date-fields').style.display = 'block';
            } else if (period === 'weekly') {
                document.getElementById('week-fields').style.display = 'block';
            } else if (period === 'monthly') {
                document.getElementById('month-field').style.display = 'block';
            } else if (period === 'annual') {
                document.getElementById('year-field').style.display = 'block';
            } else if (period === 'individual') {
                document.getElementById('sn-field').style.display = 'block';
                document.getElementById('date-fields').style.display = 'block';
            }
        }

        function openReportInNewTab() {
            var action = document.getElementById('action').value;
            var period = document.getElementById('period').value;
            var date = document.getElementById('date').value;
            var startHour = document.getElementById('start-hour').value;
            var startMinute = document.getElementById('start-minute').value;
            var endHour = document.getElementById('end-hour').value;
            var endMinute = document.getElementById('end-minute').value;
            var startDate = document.getElementById('start-date').value;
            var endDate = document.getElementById('end-date').value;
            var month = document.getElementById('month').value;
            var year = document.getElementById('year').value;
            var sn = document.getElementById('sn').value;
            var url = "generate_report.php?action=" + action + "&period=" + period;

            if (period === 'daily' || period === 'individual') {
                url += "&date=" + date;
                url += "&start_hour=" + startHour + "&start_minute=" + startMinute;
                url += "&end_hour=" + endHour + "&end_minute=" + endMinute;
            } else if (period === 'weekly') {
                url += "&start_date=" + startDate + "&end_date=" + endDate;
            } else if (period === 'monthly') {
                url += "&month=" + month;
            } else if (period === 'annual') {
                url += "&year=" + year;
            }
            if (period === 'individual') {
                url += "&sn=" + sn;
            }
            window.open(url, '_blank');
        }
    </script>
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
    <div class="container">
        <h4>Generate Report</h4>
        <form>
            <div class="form-group">
                <label for="action">Select Report Type:</label>
                <select class="form-control" id="action" name="action">
                    <option value="check-in">Computers Checked In</option>
                    <option value="check-out">Computers Checked Out</option>
                </select>
            </div>
            <div class="form-group">
                <label for="period">Select Period:</label>
                <select class="form-control" id="period" name="period" onchange="updateFormFields()">
                    <option value="#">Select Period</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="annual">Annual</option>
                    <option value="individual">Individual</option>
                </select>
            </div>
            <div class="form-group" id="date-fields" style="display:none;">
                <label for="date">Select Date:</label>
                <input type="date" class="form-control" id="date" name="date">
                <br>
                <label for="start-hour">Start Time:</label>
                <select class="form-control" id="start-hour" name="start-hour">
                    <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
                <select class="form-control" id="start-minute" name="start-minute">
                    <?php for ($i = 0; $i < 60; $i += 5): ?>
                        <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
                <br>
                <label for="end-hour">End Time:</label>
                <select class="form-control" id="end-hour" name="end-hour">
                    <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
                <select class="form-control" id="end-minute" name="end-minute">
                    <?php for ($i = 0; $i < 60; $i += 5): ?>
                        <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" id="week-fields" style="display:none;">
                <label for="start-date">Start Date:</label>
                <input type="date" class="form-control" id="start-date" name="start-date">
                <br>
                <label for="end-date">End Date:</label>
                <input type="date" class="form-control" id="end-date" name="end-date">
            </div>
            <div class="form-group" id="month-field" style="display:none;">
                <label for="month">Select Month:</label>
                <input type="month" class="form-control" id="month" name="month">
            </div>
            <div class="form-group" id="year-field" style="display:none;">
                <label for="year">Select Year:</label>
                <input type="number" class="form-control" id="year" name="year" min="1900" max="2100" step="1">
            </div>
            <div class="form-group" id="sn-field" style="display:none;">
                <label for="sn">Enter Serial Number:</label>
                <input type="text" class="form-control" id="sn" name="sn">
            </div>
            <button type="button" class="btn btn-primary" onclick="openReportInNewTab()">Generate Report</button>
        </form>
    </div>
</section>
</body>
</html>