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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
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
      max-width: 100%;
      margin: 0 auto;
    }
     /* Custom Styling for Header */
    .header {
      background-color: #007bff;
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
            margin-top: 10vh;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            font-size: 16px;
            font-weight: bold;
            max-width: 600px;
            margin-left: 180px;
        }
        input[type="text"],[type="password"]{
            width:300px;
        }
  </style>
</head>
<body>
 <header>
 <img src="img/QR-logo.JPG" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
        <h1>User | Dashboard</h1>
        <h5>Computer Checks</h5>
  
    </header>

<!-- Sidebar -->
<div class="sidebar">
  
    
    <nav class="sidebar-sticky mt-5">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="user-dashboard.php">
         <i class="fa fa-home" aria-hidden="true"></i>
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="view-laptops.php">
          <i class="fa fa-eye" aria-hidden="true"></i>
          View Laptops
        </a>
      </li>
      <li class="nav-item">
        
        <a class="nav-link" href="record-computers.php">
        <i class="fa fa-plus-circle" aria-hidden="true"></i>
         Record New Laptop 
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

 


<!-- Main Content -->
<div class="main-content">
<section>
  
<div class="record-summary">
  <p>Welcome,<span style="color: #3498db;font-family: poppins;font-weight: bold;"> <?php echo $email; ?>!</span></p>
            <h5 style="font-family: poppins;color:green;">Change your Password</h5>

    <form class="form-container" action="update-account.php" method="post">

    <div class="form-group"> 
    <label for="NID">NID:</label> <br>
    <input type="text" id="NID" name="nid" placeholder="Enter your National ID" required>
    </div>

    <div class="form-group">
    <label for="currentPassword">Current Password:</label><br>
    <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter the current password" required>
    </div>

    <div class="form-group">
    <label for="newPassword">New Password:</label><br>
    <input type="password" id="newPassword" name="newPassword" placeholder="Set a new password" required>
    </div>

    <div class="form-group">
    <label for="confirmPassword">Confirm New Password:</label><br>
    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Conifrm a new password" required><br><br>
    
    <button type="submit" name="submit" class="btn btn-success">Save Changes</button>
    </div>
</form>

  </div>
  </section>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
