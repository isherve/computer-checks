<script>
    // Disable the back button
    window.history.forward();

    // Redirect to the login page if the user tries to go back
    window.onload = function () {
        if (window.history.state && window.history.state.forward === false) {
            window.location.replace("index.php");
        }
        window.history.pushState({ forward: true }, "", "");
    }
</script>
<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to the login page
header('Location: index.php');
exit();
?>



