<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "assignment_system";

$conn = new mysqli($host, $user, $pass, $db);

// Admin credentials
$admin_email = "admin@example.com";
$admin_password = "admin123";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
