<?php

// Error reporting settings
error_reporting(0); // Disable all error reporting
ini_set('display_errors', '0'); // Do not display errors
ini_set('log_errors', '1'); // Log errors to the error log
// ini_set('error_log', '/path/to/your/logs/php-error.log'); // Path to your error log file


//------------------LOGIN PHP CODES---------------
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
        } elseif ($user['user_type'] === 'Guest') {
            header('Location: user-dashboard.php');
        } 
        exit();
    } else {
        // Authentication failed, show an error
        $error = "Invalid user_type, username or password";
    }
}
 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
      <link rel="stylesheet" href="icons/css/all.css">
    <style>
        body {
            font-family: poppins;
            background-color: #f4f4f4;
            background-image:url('img/computer-tracking.jpg');
            opacity: 0.6;
            background-size:cover;
        }

        header {
            /* background-color: #3498db; */
            color: #fff;
            text-align: center;
            padding: 20px;
        }

        section {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 380px;
            text-align: center;
            margin-left:280px;
        }

        h2 {
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }
        select, option{
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 6px;
            margin-bottom: 20px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            color: #fff;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }
        .span{
           background-color: none;
            color: #3498db;
            padding: 8px 12px;
            font-family: cursive; 
        }
        a{
            text-decoration: none;
        }
        .span:hover{
            color: black;
        }
    </style>
</head>

<body>
    
    <header>
        <h5>Computer Checks</h5>
    </header>

    <section>
        <div class="container">
            <h5>Login</h5>
            <form action="#" method="POST">
                <label for="user_type">Who are you?</label>
                <select id="user_type" name="user_type" required>
                    <option value="Admin">Admin</option>
                    <option value="Guest">Gate_Officer</option>
                </select>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Put the email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Put the password" required>

                <button type="submit">Login</button>
                
                <?php if (isset($error)) : ?>
                    <p style="color: red;font-family: sans-serif;"><?php echo $error; ?></p>
                <?php endif; ?>

            </form>
        </div>
    </section>
<!-- <footer>&COPY;rights reserved to irpc kigali</footer> -->
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
