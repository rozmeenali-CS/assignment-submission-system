<?php
include('../config.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $marks = $_POST['marks'];
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("UPDATE submissions SET marks = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("isi", $marks, $feedback, $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "fail";
    }
}
?>
