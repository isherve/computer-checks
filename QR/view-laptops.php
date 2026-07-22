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
  <title>View Computers Recorded</title>
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
    .record-summary{
      
      background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            flex-grow: 1;
            margin-left: 0px;
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
  <div>  <p>Welcome,<span style="color: #3498db;font-family: poppins;font-weight: bold; text-align: center;"> <?php echo $email; ?>!</span></p> 
            <h2 style="font-family: poppins;color:green;">Laptops</h2>



<!--OPEN SEARCH-->
<div class="form-group">
    <form action="" method="POST" class="form-inline mb-3">
        <input type="text" class="form-control mr-2" name="search" placeholder="Search by Owner's Name or Serial Number" style="min-width:280px;" required>
        <button type="submit" name="submit" class="btn btn-sm btn-info">Search</button>
    </form>
    <!--PHP CODES-->
    <?php

require_once 'connection.php';

if (isset($_POST['submit'])) {
    $search = trim($_POST['search']);
    try {
        // Search by owner's name or serial number
        $stmt = $pdo->prepare(
            "SELECT * FROM computer_info
             WHERE owname LIKE :search OR sn LIKE :search
             ORDER BY id DESC"
        );
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "<h5 style='color:green;'>Laptop(s) Found:</h5>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $sn = htmlspecialchars($row['sn']);
                $model = htmlspecialchars($row['model']);
                $owno = htmlspecialchars($row['owno']);
                $owname = htmlspecialchars($row['owname']);
                $nameParam = urlencode($row['owname']);
                echo "<div class='mb-3 p-2 border rounded'>";
                echo "<p class='mb-1'><strong>Serial Number:</strong> $sn</p>";
                echo "<p class='mb-1'><strong>Model:</strong> $model</p>";
                echo "<p class='mb-1'><strong>Owner's Number:</strong> $owno</p>";
                echo "<p class='mb-1'><strong>Owner's Name:</strong> $owname</p>";
                echo "<a href='test-qr.php?name=" . $nameParam . "' class='open-in-new-tab'>
                        <button type='button' class='btn btn-primary btn-sm'>Generate QR Code</button>
                      </a>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-danger'>No records found for that name or serial number.</p>";
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
    global $pdo;
    try {    
        // Order by id in descending order to get the latest inserted first
        $stm = $pdo->prepare("SELECT computer_info.sn, computer_info.model, computer_info.owno, computer_info.owname FROM computer_info ORDER BY id DESC");
        $stm->execute();
        $results = $stm->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <table class="table table-striped table-bordered table-hover table-responsive">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Serial Number</th>
                    <th>Model</th>
                    <th>Owner's Number</th>
                    <th>Owner's Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
        <?php

        $i = 1;
        foreach ($results as $row) {
            $nameParam = urlencode($row['owname']);
            echo "<tr>";
            echo "<td><b>$i.&nbsp;</b></td>";
            echo "<td>" . htmlspecialchars($row['sn']) . "</td>";
            echo "<td>" . htmlspecialchars($row['model']) . "</td>";
            echo "<td>" . htmlspecialchars($row['owno']) . "</td>";
            echo "<td>" . htmlspecialchars($row['owname']) . "</td>";
            echo "<td>
                    <a href='test-qr.php?name=" . $nameParam . "' class='open-in-new-tab' target='_blank'>
                        <button type='button' class='btn btn-primary btn-sm'>Generate QR Code</button>
                    </a>
                  </td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody>";
        ?>
        </table>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var links = document.querySelectorAll(".open-in-new-tab");
        links.forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();
                var url = this.href;
                window.open(url, '_blank');
            });
        });
    });
</script>



<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
