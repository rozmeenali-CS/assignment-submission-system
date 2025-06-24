<?php
session_start();
include('../config.php');

if (!isset($_SESSION['teacher_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$assignment_id = $_GET['id'];

// Delete from database and server
$result = $conn->query("SELECT file_path FROM assignments WHERE id = $assignment_id AND teacher_id = $teacher_id");
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (!empty($row['file_path']) && file_exists("../uploads/" . $row['file_path'])) {
        unlink("../uploads/" . $row['file_path']);
    }
    $conn->query("DELETE FROM assignments WHERE id = $assignment_id AND teacher_id = $teacher_id");
}

header("Location: add_assignment.php?deleted=1");
exit();
?>
