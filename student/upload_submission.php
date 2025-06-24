<?php
session_start();
include('../config.php');

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access");
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $teacher_id = $_POST['teacher_id'];
    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp3', 'mp4'];

    $file = $_FILES['submission_file'];

    if ($file['error'] == 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $filename = "submission_" . time() . "_" . rand(1000,9999) . "." . $ext;
            $dest = "../uploads/" . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, teacher_id, file_path, submitted_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiis", $assignment_id, $student_id, $teacher_id, $filename);
                if ($stmt->execute()) {
                    header("Location: student_dashboard.php?success=submitted");
                    exit();
                } else {
                    echo "DB Error: " . $conn->error;
                }
            } else {
                echo "Upload failed.";
            }
        } else {
            echo "Invalid file type.";
        }
    } else {
        echo "File upload error.";
    }
}
?>
