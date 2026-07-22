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
  <title>View Users Recorded</title>
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
      margin-left: 0px; /* Same width as sidebar */
      padding: 10px;
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
    .record-summary {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    flex-grow: 1;
    margin-left: 20px;
}

.record-summary table {
    width: 100%;
    border-collapse: collapse;
}

.record-summary th, .record-summary td {
    padding: 10px;
    border: 1px solid #ddd;
}

.record-summary th {
    background-color: #f2f3ffff;
    font-weight: none;
    
}

.table-wrapper-scroll-y {
    max-height: 300px; /* Adjust as needed */
    overflow-y: auto;
}


      section {
            margin-top: 10vh;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            font-size: 15px;
            font-weight: bold;
            max-width: 1000px;
            margin-left: 200px;
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
        
        <a class="nav-link" href="add-users.php">
        <i class="fa fa-plus-circle" aria-hidden="true"></i>
         Add new user 
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
  <div>  <p>Welcome,<span style="color: #3498db;font-family: sans-serif;font-weight: bold; text-align: center;"> <?php echo $email; ?>!</span></p> 
            <h5 style="font-family: poppins;color:green;">Users</h5>
<!--OPEN SEARCH-->
<div class="form-group">
    <form action="" method="POST">
        <input type="text" name="names" placeholder="Input a name">
        <button type="submit" name="submit" class="btn btn-sm btn-info">Search</button>
    </form>
    <!--PHP CODES-->
    <?php

include 'connection.php';

if (isset($_POST['submit'])) {
    $search = $_POST['names'];
    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE names LIKE :search");
        // Bind the parameter
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        // Execute the statement
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Fetch results
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $nid = $row['nid'];
                $names = $row['names'];
                $email = $row['email'];
                // Output all information about the user
                echo "<h5 style='color:green;font-family:poppins;'>User Found:</h5>";
                echo "<p>NID: $nid</p>";
                echo "<p>Name: $names</p>";
                echo "<p>Email: $email</p>";
                // Add an edit link/button
                echo "<td><a href='update-users.php?nid=".$row["nid"]."'><button class='btn btn-info'>Edit</button></a></td> &nbsp;&nbsp;";
                echo "<td><a href='delete-users.php?nid=".$row["nid"]."'><button class='btn btn-danger'>Delete</button></a></td>";
            }
        } else {
            echo "No records found";
        }
    } catch (PDOException $e) {
        echo "Query failed: " . $e->getMessage();
    }
}
?>


</div>
<!--CLOSING SEARCH-->

<?php
function selectStudent(){
include "connection.php";
try {    
    $stm=$pdo->prepare("SELECT users.nid, users.user_type,users.names,users.email FROM users");
    $stm->execute ();
    $results = $stm->fetchAll(PDO::FETCH_ASSOC);
    ?>
   
        <div class="record-summary">
        <table class = "table table-striped table-bordered table-hover table-responsive table-wrapper-scroll-y table">
            <tr>
                <thead>
                    <tr>
                        <th>No</th><th>NID</th><th>Role</th><th>Name</th><th>Email</th><th colspan="2"><center>Action</center></th>
                    </tr>
                </thead>
            </tr>
    <?php
    $i=1;
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td><b>$i.&nbsp;</b></td>";
        echo "<td>" . $row['nid'] . "</td>";
        echo "<td>" . $row['user_type'] . "</td>";
        echo "<td>" . $row['names'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td><a href='update-users.php?nid=".$row["nid"]."'><button class='btn btn-info'>Edit</button></a></td>";
        echo "<td><a href='delete-users.php?nid=".$row["nid"]."'><button class='btn btn-danger'>Delete</button></a></td>";

        echo "</tr>";
        $i=$i+1;
    }
    ?>
            </table>
  </div>
    
    <?php
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
}

selectStudent();

?>

  </div>
  </section>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
