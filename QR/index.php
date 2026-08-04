<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR-BASED COMPUTER TRACKING SOLUTION</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    body {
        background-color: #f8f9fa;
        font-family: poppins;
        background-image:url('img/computer-tracking.jpg');
        opacity: 0.6;
        background-size:cover;
        
    }
    .container {
        max-width: 720px;
        margin: 80px auto;
        text-align: center;
        padding: 0 16px;
    }
    h4 {
        color: #ffffff;
        margin-bottom: 22px;
        font-weight: 400;
        font-size: 1rem;
        letter-spacing: 0.3px;
        text-shadow: 0 2px 8px rgba(0,0,0,0.55);
    }
    h4 .brand-name {
        display: block;
        margin-top: 6px;
        font-size: clamp(0.95rem, 2.4vw, 1.35rem);
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        line-height: 1.35;
        max-width: 28em;
        margin-left: auto;
        margin-right: auto;
    }
    .btn {
        background-color: #007bff;
        color: #fff;
        border-radius: 25px;
        padding: 8px 18px;
        font-size: 15px;
        margin-top: 14px;
    }
    .btn:hover {
        background-color: #0056b3;
    }
    .btn-success { background-color: #28a745; margin-left: 8px; }
    .btn-success:hover { background-color: #1e7e34; }
    .hero-img {
        width: 100%;
        max-width: 600px;
        margin-top: 20px;
    }
    .footer {
        position: fixed;
        bottom: 16px;
        width: 100%;
        text-align: center;
        color: #666;
        font-size: 0.8rem;
        padding: 0 12px;
    }
    .footer p {
        color: white;
        font-size: 0.78rem;
        line-height: 1.3;
        margin: 0;
    }
    p{
      color:white;
    }
   
</style>
</head>
<body>

<div class="container">
    <h4>Welcome to the<br><span class="brand-name">QR-BASED COMPUTER TRACKING SOLUTION</span></h4>
    <!-- <p>Check computers easily using QR codes</p> -->
    <a href="login.php" class="btn btn-lg">Staff Login (Admin / Gate Officer)</a>
    <a href="gate-check.php" class="btn btn-lg btn-success ml-2">Gate Check Portal</a>
    <p class="mt-4" style="font-size:0.9rem;max-width:34em;margin-left:auto;margin-right:auto;">
      Portals: <strong>Admin</strong> (users + devices + logs),
      <strong>Gate Officer</strong> (register devices, QR, logs),
      <strong>Gate Check</strong> (authenticate &amp; check-in/out).
    </p>
</div>

<div class="footer">
    <p>&copy; ulk Gisenyi QR-BASED COMPUTER TRACKING SOLUTION. All rights reserved.</p>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
