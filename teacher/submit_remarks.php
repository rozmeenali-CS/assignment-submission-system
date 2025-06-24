<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'], $_POST['remarks'])) {
    $id = $_POST['submission_id'];
    $remarks = trim($_POST['remarks']);

    $stmt = $conn->prepare("UPDATE submissions SET feedback = ? WHERE id = ?");
    $stmt->bind_param("si", $remarks, $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
