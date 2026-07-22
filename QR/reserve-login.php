<?php
// Start the session
session_start();
//Database Connection
include 'connection.php';
// Function to authenticate user
function authenticateUser($email, $password, $user_type, $pdo) {

    $query = "SELECT * FROM users WHERE email = :email AND user_type = :user_type";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email); 
    $stmt->bindParam(':user_type', $user_type);
    $res= $stmt->execute();

    if ($res) { 
       $rowResults= $stmt->fetch(PDO::FETCH_ASSOC);
        if(password_verify($password, $rowResults['password'])){
            return  $rowResults;
        }else{
            return false;
        }
    }else{
        return false;
    }  
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Authenticate user
    $user = authenticateUser($email, $password, $user_type, $pdo);

   
    if ($user) {
        // User authenticated, set session variables
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['email'] = $user['email'];

        // Redirect to a welcome page or dashboard
          if ($user['user_type'] === 'Admin') {
            header('Location: admin-dashboard.php');
        }  
          elseif ($user['user_type'] === 'Guest'){
            header('Location: user-dashboard.php');
        }
        exit();
    } else {
        // Authentication failed, show an error
        $error = "Invalid user_type, email or password";
    }
}
 
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Computer Checks</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="icons/css/all.css">
  <!-- Custom CSS -->
  <style>
    /* Custom Styling for Header */
    .header {
      background-color: #496657;
      color: #ffffff;
      padding: 20px 0;
      text-align: center;
    }
    footer{
      background-color: #496657;
      color: #ffffff;
      padding: 10px 0;
      text-align: center;
      margin-top: 0vh;
    }
    /* Custom Styling for Content */
    .content {
      padding: 20px;
    }
    h2{
      text-align: center;
    }
    p{
      text-align: justify;
    }
    .yes{
      color: green;
      font-weight: bold;
      font-size: 20px;
    }
    .no{
      color: red;
      font-weight: bold;
      font-size: 20px;
    }
     .logo {
      max-width: 150px; /* Adjust width as needed */
      float: left;
      margin-top: -8px;
    }
     /* Custom Styling */
    .image-container {
      
      border-radius: 50%;
    }
     .form-container {
      margin-left: 25%;
      max-width: 400px;
    }

  </style>
</head>
<body>

<!-- Header -->
<div class="header">
  <h1><img src="img/QR-logo.jpg" alt="Logo" class="logo img-fluid col-md-4  image-container">Computer Checks</h1>
  <p style="text-align:center;">IPRC Kigali</p>
</div>

<!-- Content -->
<div class="content">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h2>Welcome to Computer Checks</h2>
        <p>This system allows you to easily record information about laptops using QR codes. You can record details such as serial number, model, owner's registration number, and owner's name.</p>
        <p>To get started, you need to be sure of being registered by sytem administrator</p>

      </div>
      <div class="col-md-6">
        <h2>Login <i class="fa-solid fa-right-to-bracket"></i></h2>
        <div class="card-body">
         <form action="#" method="POST">
            <div class="form-group">
              <label for="user_type">Who are you?</label>
              <select class="form-control" id="user_type" name="user_type" required>
                <option class="form-control" value="Admin">Admin</option>
                <option class="form-control" value="Guest">Guest</option>
              </select>
              
            </div>
           <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email" required>
            </div>

             <div class="form-group">
              <label for="password">Password:</label>
              <input type="password" class="form-control" name="password" id="password"  placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn btn-success btn-block">Login</button>
            <?php if (isset($error)) : ?>
                    <p style="color: red;font-family: sans-serif;"><?php echo $error; ?></p>
                <?php endif; ?>
         </form>
        </div>
      </div>
      <a href="index.php"><i class="fa-solid fa-backward-step"></i>Back</a>
    </div>
  </div>
</div>
<footer>&COPY;irpc kigali 2024</footer>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
