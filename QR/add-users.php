<!-- codes for checking login -->
<?php

session_start();
if (!isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
} 

// Include the database connection
include 'connection.php';

// Fetch username based on user Type
$user_type = $_SESSION['user_type'];
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT names FROM users WHERE user_type = ? AND email = ?");
$stmt->execute([$user_type,$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Display the email
$email = $user['names'];
?>



<!-- codes reserved for insert -->

<?php
session_start();

// Include the database connection
include 'connection.php';

if (isset($_POST['submit'])) {
    $user_type = $_POST['user_type'];
    $nid = $_POST['nid'];
    $names = $_POST['names'];
    $email = $_POST['email'];
    $password = $_POST['nid']; 
    
    // Check if user with the same ID already exists
    $sql_check = "SELECT COUNT(*) FROM users WHERE nid = :nid";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':nid', $nid);
    $stmt_check->execute();
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        $Error = "Error: User's ID already exists.";
    } else {
        // Hash the password
        $password = password_hash($_POST['nid'], PASSWORD_DEFAULT);

        try {
            // Insert user record into the database
            $stmt = $pdo->prepare("INSERT INTO users (user_type, nid, names, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_type, $nid, $names, $email, $password]);

            // Redirect or display a success message

            $message = "User was added successfully!";
        } catch (PDOException $e) {
            die('Error occurred: ' . $e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Users</title>
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
  </style>
</head>
<body>
 <header>
 <img src="img/QR-logo.JPG" alt="Logo" class="logo img-fluid col-md-4 mt-0 image-container float-left">
        <h1>Admin | Dashboard</h1>
        <h5>Computer Checks</h5>
  
    </header>

<!-- Sidebar -->
<div class="sidebar">
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
 <div class="container mt-5">
  <div class="row">
    <div class="col-md-8 offset-md-2">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title text-center">Add New User</h5>
        </div>
        <div class="card-body">
        <?php if (isset($message) || isset($Error)) : ?>
    <p class="<?php echo isset($message) ? 'alert alert-success' : 'alert alert-danger'; ?>" style="font-family: poppins; text-align: center;">
        <?php echo isset($message) ? $message : $Error; ?>
    </p>
    <?php endif; ?>

          <!-- User Add Form -->
          <form class="form-container" action="" method="POST">
            <div class="form-group">
              <label  for="usertype">UserType:</label>
              <select class="form-control" name="user_type" id="user_type">
                <option class="form-control" value="Admin">Admin</option>
                <option class="form-control" value="Guest">Get_Officer</option>
              </select>
              
            </div>
            <div class="form-group">
              <label for="nid">NID:</label>
              <input type="text" class="form-control" id="nid" name="nid" placeholder="Enter National ID" required>
            </div>
            
            <div class="form-group">
              <label for="userName">User's Name:</label>
              <input type="text" class="form-control" id="userName" name="names" placeholder="Enter User's Name" required>
            </div>

             <div class="form-group">
              <label for="email">User's Email:</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Enter User's Email" required>
            </div>

             <div class="form-group">
                <input type="hidden" class="form-control" id="password" name="password" placeholder="Enter Password"  required>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary btn-block">Save</button>
          </form>
          <!-- End User Add Form -->
        </div>
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
