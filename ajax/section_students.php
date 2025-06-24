<?php
include('../config.php');
session_start();

if (!isset($_SESSION['teacher'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$teacher = $_SESSION['teacher'];
$sections = explode(',', $teacher['sections']);
$department = $teacher['department'];

$results = [];

foreach ($sections as $section) {
    $stmt = $conn->prepare("SELECT name, student_id FROM users WHERE role='student' AND department=? AND section=?");
    $stmt->bind_param("ss", $department, $section);
    $stmt->execute();
    $res = $stmt->get_result();

    $students = [];
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
    $results[$section] = $students;
}

header('Content-Type: application/json');
echo json_encode($results);
