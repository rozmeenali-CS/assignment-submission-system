<?php
session_start();

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to home or login page
header("Location: ../index.php"); // Change this to your home or login page
exit();
?>
