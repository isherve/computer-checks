<!-- codes reserved for login -->
<?php

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (!isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
} 


// Check if the user is logged in
// if (!isset($_SESSION['id'])) {
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
<!-- codes reserved for insert -->

<?php
// if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Include the database connection
require_once 'connection.php';

if (isset($_POST['submit'])) {
    $sn = $_POST['sn'];
    $model = $_POST['model'];
    $type = $_POST['type'];
    $owno = $_POST['owno'];
    $owname = $_POST['owname'];

    try {
        // Check if computer with the same serial number already exists
        $sql_check = "SELECT COUNT(*) FROM computer_info WHERE sn = :sn";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':sn', $sn);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            $Error = "Error: Computer's S/N already exists.";
        } else {
            // Insert computer record into the database
            $stmt = $pdo->prepare("INSERT INTO computer_info (sn, model,type, owno, owname) VALUES (?,?, ?, ?, ?)");
            $stmt->execute([$sn, $model,$type, $owno, $owname]);

            // Redirect or display a success message
            // header('Location: Institutions.php'); // Redirect to a page showing the list of computers
            $message = "Computer was recorded successfully!" ;
            $lastInsertedSn = $sn; // Store the last inserted serial number
        }
    } catch (PDOException $e) {
        die('Error occurred: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Record New Computers</title>
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
    section {
            margin-top: 4vh;
            display: flex;
            justify-content: space-between;
            padding: 20px;
            font-size: 16px;
            font-weight: bold;
            max-width: 1000px;
            margin-left: 10px;
        }
        .error {
            color: red;
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
 <div class="container mt-5">
  <div class="row">
    <div class="col-md-8 offset-md-2">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title text-center">Record New Laptop</h5>
        </div>
        <div class="card-body">
        <?php if (isset($message)) : ?>
          <div class="alert alert-success text-center" role="alert">
            <?php echo $message; ?>
            <a href="test-qr.php?name=<?php echo urlencode($owname); ?>" target="_blank" class="btn btn-primary ml-3">Generate QR Code</a>
          </div>
        <?php elseif (isset($Error)) : ?>
          <div class="alert alert-danger text-center" role="alert">
            <?php echo $Error; ?>
          </div>
        <?php endif; ?>
          <!-- Laptop Record Form -->
          <form class="form-container" action="" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="serialNumber">Serial Number:</label>
            <input type="text" class="form-control" id="sn" name="sn" placeholder="Enter Serial Number" required>
            <span id="snError" class="error"></span>
        </div>
        <div class="form-group">
            <label for="model">Model:</label>
            <select class="form-control" id="model" name="model" required>
                <option value="|#">Select Model</option>
                <option value="LENOVO">LENOVO</option>
                <option value="HP">HP</option>
                <option value="DELL">DELL</option>
                <option value="MAC BOOK">MAC BOOK</option>
                <option value="SAMSUNG">SAMSUNG</option>
                <option value="LG">LG</option>
            </select>
        </div>
        <div class="form-group">
            <label for="type">Owner Type:</label>
            <select class="form-control" id="type" name="type" onchange="updateValidation()" required>
                <option value="#">Select owner's Type</option>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="registrationNumber">Owner's Identification:</label>
            <input type="text" class="form-control" id="registrationNumber" name="owno" placeholder="Enter Identification" required>
            <span id="regError" class="error"></span>
        </div>
        <div class="form-group">
            <label for="ownerName">Owner's Name:</label>
            <input type="text" class="form-control" id="ownerName" name="owname" placeholder="Enter Owner's Name" required>
            <span id="nameError" class="error"></span>
        </div>
        
        <button type="submit" name="submit" class="btn btn-primary btn-block">Save</button>
    </form>


          <!-- End Laptop Record Form -->
        </div>
      </div>
    </div>
  </div>
</div>
</section>
</div>

        <!--Script for validations-->

        <script>
        document.getElementById("sn").addEventListener("blur", function() {
            validateSerialNumber();
        });

        document.getElementById("ownerName").addEventListener("blur", function() {
            validateOwnerName();
        });

        document.getElementById("registrationNumber").addEventListener("blur", function() {
            validateRegistrationNumber();
        });

        function updateValidation() {
            var type = document.getElementById("type").value;
            var registrationNumber = document.getElementById("registrationNumber");
            
            if (type === "student") {
                registrationNumber.placeholder = "Enter Registration Number (e.g., 12RP3456)";
                registrationNumber.pattern = "^\\d{2}RP\\d+$";
            } else if (type === "staff") {
              registrationNumber.placeholder = "Enter NID (16 digits, starting with 1)";
              registrationNumber.pattern = "^1\\d{15}$";
            } else if (type === "other") {
                registrationNumber.placeholder = "Enter NID (16 digits, starting with 1)";
                registrationNumber.pattern = "^1\\d{15}$";
            } else {
                registrationNumber.placeholder = "Enter Identification";
                registrationNumber.pattern = "";
            }
        }

        function validateSerialNumber() {
            var sn = document.getElementById("sn").value;
            var snPattern = /^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/;
            if (!snPattern.test(sn)) {
                alert("Serial Number must contain both letters and numbers.");
                document.getElementById("snError").textContent = "Serial Number must contain both letters and numbers.";
                return false;
            } else {
                document.getElementById("snError").textContent = "";
                return true;
            }
        }

        function validateOwnerName() {
            var ownerName = document.getElementById("ownerName").value;
            var namePattern = /^[a-zA-Z\s]+$/;
            if (!namePattern.test(ownerName)) {
                alert("Owner's Name can only contain letters and spaces.");
                document.getElementById("nameError").textContent = "Owner's Name can only contain letters and spaces.";
                return false;
            } else {
                document.getElementById("nameError").textContent = "";
                return true;
            }
        }

        function validateRegistrationNumber() {
            var type = document.getElementById("type").value;
            var regNumber = document.getElementById("registrationNumber").value;
            var regPattern;
            var isValid = true;

            if (type === "student") {
                regPattern = /^\d{2}RP\d+$/; // Pattern: 2 digits, RP, followed by digits
                if (!regPattern.test(regNumber)) {
                    alert("Registration Number must be in the format: 2 digits, RP, followed by digits.");
                    document.getElementById("regError").textContent = "Registration Number must be in the format: 2 digits, RP, followed by digits.";
                    isValid = false;
                }
            } else if (type === "staff") {
                regPattern = /^1\d{15}$/; // Pattern: 1 followed by 15 digits
                if (!regPattern.test(regNumber)) {
                    alert("NID must be 16 digits long, start with 1, and not be a single repeated digit.");
                    document.getElementById("regError").textContent = "NID must be 16 digits long, start with 1, and not be a single repeated digit.";
                    isValid = false;
                }
            } else if (type === "other") {
                regPattern = /^1\d{15}$/; // Pattern: 1 followed by 15 digits
                if (!regPattern.test(regNumber)) {
                    alert("NID must be 16 digits long, start with 1, and not be a single repeated digit.");
                    document.getElementById("regError").textContent = "NID must be 16 digits long, start with 1, and not be a single repeated digit.";
                    isValid = false;
                }
                if (/^(.)\1+$/.test(regNumber)) { // Check for single repeated digit
                    alert("NID cannot be a single repeated digit.");
                    document.getElementById("regError").textContent = "NID cannot be a single repeated digit.";
                    isValid = false;
                }
            }

            if (isValid) {
                document.getElementById("regError").textContent = "";
            }
            return isValid;
        }

        function validateForm() {
            var isSnValid = validateSerialNumber();
            var isNameValid = validateOwnerName();
            var isRegValid = validateRegistrationNumber();

            return isSnValid && isNameValid && isRegValid;
        }
    </script>



      <!--Sripts for refreshing a form after successfully submision-->

      <script>
        function validateForm() {
            // Add any form validation logic here if needed
            return true;
        }

        function submitForm(event) {
            event.preventDefault(); // Prevent default form submission
            
            // Validate form
            if (!validateForm()) {
                return false;
            }
            
            // Create a FormData object
            const formData = new FormData(document.querySelector('.form-container'));
            
            // Send form data using Fetch API
            fetch('', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
              .then(result => {
                  // Optionally, handle the response
                  console.log(result);
                  
                  // Refresh the page after successful submission
                  window.location.reload();
              }).catch(error => {
                  console.error('Error:', error);
              });
        }
    </script>


<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
