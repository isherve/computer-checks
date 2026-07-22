<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirm Account</title>
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
      margin-top: 20.8vh;
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
        <h2>Account Updating</h2>
        <form action="acc-info.php" method="POST" class="form-container">
          <div class="form-group">
              <input type="email" class="form-control" id="email" name="email" placeholder="Enter your registered email" required>
        </div>
        <button type="submit" name="submit" class="btn btn-success btn-block">Send<i class="fa-solid fa-paper-plane"></i></button>

        </form>
        
      </div>
      <a href="index.php"><i class="fa-solid fa-backward-step"></i>Back</a>
    </div>
  </div>
</div>
<footer>&COPY;rights reserved to iprc kigali</footer>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
