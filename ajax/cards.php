<?php
session_start();
include('../config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['sections'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$sections = explode(',', $_SESSION['sections']);
$total_students = 0;
$total_assignments = 0;
$total_submissions = 0;

// Count students in teacher's sections
foreach ($sections as $section) {
    $section = trim($section);
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role='student' AND sections LIKE ?");
    $like_section = "%$section%";
    $stmt->bind_param("s", $like_section);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $total_students += $count;
    $stmt->close();
}

// Count teacher's total assignments
$teacher_id = $_SESSION['teacher_id'];
$stmt = $conn->prepare("SELECT COUNT(*) FROM assignments WHERE teacher_id = ?");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$stmt->bind_result($total_assignments);
$stmt->fetch();
$stmt->close();

// Count total submissions
$stmt = $conn->prepare("SELECT COUNT(*) FROM submissions WHERE teacher_id = ?");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$stmt->bind_result($total_submissions);
$stmt->fetch();
$stmt->close();

echo json_encode([
    'total_students' => $total_students,
    'total_assignments' => $total_assignments,
    'total_submissions' => $total_submissions
]);
