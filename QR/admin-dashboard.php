<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="icons/css/all.css">
  <!-- Custom CSS -->
  <style>
     body{
      font-family:poppins;
    }
    /* Adjustments for Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 100;
      padding: 48px 0 0; /* Height of navbar */
      box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    }
    .sidebar-sticky {
      position: relative;
      top: 0;
      height: calc(100vh - 48px);
      padding-top: .5rem;
      overflow-x: hidden;
      overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
    }
    /* Adjustments for Main Content */
    .main-content {
      margin-left: 100px; /* Same width as sidebar */
      padding: 20px;
    }
     /* Custom Styling for Form */
    .form-container {
      max-width: 500px;
      margin: 0 auto;
    }
     /* Custom Styling for Header */
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

        /* Custom Styling for Horizontal Menu */
    @media (max-width: 576px) {
      .horizontal-menu {
        display: block;
      }
      .vertical-menu {
        display: none;
      }
    }
    @media (min-width: 577px) {
      .horizontal-menu {
        display: none;
      }
      .vertical-menu {
        display: block;
      }
    }
    i{
      color: black;
    }
    .record-summary{
      
      background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex-grow: 1;
            margin-left: 20px;
    }
      section {
            margin-top: 28vh;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            font-size: 16px;
            font-weight: bold;
            max-width: 500px;
            margin-left: 200px;
        }
        .left-division {
            background-color: #f8f9fa;
            padding: 20px;
            height: 100vh;
            border-right: 1px solid #ddd; /* Add border */
        }
        .right-division {
          margin-top: 10vh;
            padding: 20px;
        }

        .dashboard-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .statistic-counter {
            text-align: center;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .statistic-counter h3 {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .statistic-counter p {
            font-size: 24px;
            margin: 0;
        }
  </style>
</head>
<body>
<header>
<img src="img/QR-logo.JPG" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
  
        <h1>Admin | Dashboard</h1>
         
        <h5>Computer Checks</h5>
  
    </header>

<!-- Sidebar -->
<div class="container-fluid">
<div class="row">
<div class="col-md-2 left-division">
<div class="sidebar">
  
    <!-- <img src="img/QR-logo.JPG" alt="Logo" class="logo img-fluid col-md-4 mt-5 image-container"> -->
    <nav class="sidebar-sticky mt-5">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="admin-dashboard.php">
         <i class="fa fa-home" aria-hidden="true"></i>
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="view-users.php">
          <i class="fa fa-eye" aria-hidden="true"></i>
          View Users
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="add-users.php">
        <i class="fa fa-plus-circle" aria-hidden="true"></i>
         Add new user 
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="report.php">
          <i class="fa fa-book" aria-hidden="true"></i>
          Logs
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <i class="fa fa-sign-out" aria-hidden="true"></i>
          Logout
        </a>
      </li>
    </ul>
  </nav>
</div>
</div>

 


<!-- Main Content -->

<div class="col-md-10 right-division">
            <div class="header">
                <!-- <button class="menu-toggle"><i class="fas fa-bars"></i></button> -->
              <!--   <div class="search-container ">
                    <input type="text" class="search-input" placeholder="Search...">
                    <button class="btn btn-primary search-button"><i class="fas fa-search"></i></button>
                </div> -->
                <!-- <button class="add-button float-right">Add</button> Float to right --> 
            </div>
            <div class="dashboard-content">
            <p>Welcome,<span style="color: #3498db;font-family: poppins;font-weight: bold; text-align: center;"> <?php echo $email; ?>!</span></p>
                <div class="row">
                    <!-- First Horizontal Section -->
                    <div class="col-md-12">
                        <div class="statistic-counter">
                            <h6>Users</h6>
                            <p class="counter">
                            <?php
                                // Include the database connection file
                                require_once 'connection.php';

                                // Query to count the total number of users
                                $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
                                $totalUsers = $stmt->fetchColumn();

                                // Output the total number of users
                                echo $totalUsers;
                            ?>
                            </p>
                        </div>
                    </div>
                    </div>
                    <!-- Third Horizontal Section -->

                </div>
            </div>
        </div>
      </div>
      </div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
