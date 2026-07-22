<?php
//including libraries for generating qr code

//include 'tcpdf/tcpdf_barcodes_2d.php';
require_once 'connection.php';


// Function to fetch information from database based on serial number
function getInfoFromDatabase($serialNumber,$model,$ownernumber,$ownername) {
    global $pdo;

    // Query database to fetch information based on serial number
    $stmt = $pdo->prepare("SELECT sn,model,owno,owname FROM computer_info WHERE sn = ?");
    $stmt->execute([$serialNumber]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row;
}

// Function to generate QR code
/*function generateQRCode($text, $file_name) {
    // QR code configuration
    $ecc = 'L'; // Error correction level: L, M, Q, H
    $pixel_size = 10; // Size of each "pixel" in the QR code
    $margin = 4; // Margin around the QR code
    $obj = new TCPDF2DBarcode($text,'QRCODE,H');\
    return $obj->getBarcodePNG(6,6, [0,0,0]);
}
*/
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch the serial number from the form submission
    $serialNumber = $_POST['sn'];
    echo $serialNumber;

    // Fetch information from database based on serial number
    $computerInfo = getInfoFromDatabase($serialNumber,$model,$ownernumber,$ownername);
    

    // Check if computer information is found
    if ($computerInfo) {
        // Generate QR code data (you can customize this based on your database structure)
        $qrCodeData = json_encode($computerInfo);

        // Generate QR code image file name
        //$qrCodeFileName = 'qrcodes/' . $serialNumber . '.png';

        // Generate QR code
       // generateQRCode($qrCodeData, $qrCodeFileName);

        // Output success message with QR code image
        echo "QR code generated successfully. <br>";
        echo "Scan the QR code:<br>";
       // echo $serialNumber;
        //echo "<img src='$qrCodeFileName' alt='QR Code'><br>";
        echo '<img src="tcpdf/examples/barcodes/getMyBarcode.php?details='.$serialNumber,$model,$ownernumber,$ownername.'" height="20" width="20" />';
    } else {
        echo "Computer not found in the database.";
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body onload="window.print();">
    <!-- <h2>Generate QR Code</h2>
    <form method="post" action="test-qr.php"> -->
    <?php
        // Include the database connection file
        require_once 'connection.php';

        // Fetch a serial number from the database
        $stmt = $pdo->prepare("SELECT sn,model,owno,owname FROM computer_info WHERE sn=?");
        $stmt->execute([$_GET['sn']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $serialNumber = $row['sn'] ;
        $model = $row['model'] ;
        $ownernumber = $row['owno'] ;
        $ownername = $row['owname'] ;
        
      ?>
    <img src="tcpdf/examples/barcodes/getMyBarcode.php?details=Serial Number: <?=$serialNumber?> &nbsp; Model: <?=$model?> &nbsp;&nbsp; Owner Number: <?=$ownernumber?> &nbsp;&nbsp; Owner Name: <?=$ownername?>  " height="55" width="55" />
</body>
</html>