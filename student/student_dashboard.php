<?php
session_start();
include('../config.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $student_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();

$name = $user['name'];
$department = $user['department'];
$section = $user['sections'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    body {
      background: #f4f7fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #34495e;
      color: white;
      padding: 25px;
    }
    .sidebar h4 {
      margin-bottom: 20px;
      color: #ecf0f1;
    }
    .sidebar p {
      font-size: 14px;
      margin: 5px 0;
    }
    .sidebar a {
      display: block;
      color: #bdc3c7;
      text-decoration: none;
      margin: 10px 0;
      padding: 6px 10px;
      border-radius: 6px;
      transition: 0.3s;
    }
    .sidebar a:hover {
      background-color: #2c3e50;
      color: #fff;
    }
    .topbar {
      background: #fff;
      padding: 15px;
      border-bottom: 1px solid #ddd;
    }
    .main-content {
      padding: 20px;
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 sidebar">
      <h4><i class="ri-dashboard-line"></i> Student Dashboard</h4>
      <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
      <p><strong>Department:</strong> <?= htmlspecialchars($department) ?></p>
      <p><strong>Section:</strong> <?= htmlspecialchars($section) ?></p>

      <hr>
      <a href="#" onclick="loadContent('student_view_assignments.php')"><i class="ri-book-open-line"></i> View Assignments</a>
      <a href="#" onclick="loadContent('view_feedback.php')"><i class="ri-star-line"></i> View Feedback & Remarks</a>
      <a href="logout.php"><i class="ri-logout-box-line"></i> Logout</a>
    </div>

    <!-- Main Area -->
    <div class="col-md-9">
      <div class="topbar">
        <h5>Welcome, <?= htmlspecialchars($name) ?>!</h5>
      </div>
      <div class="main-content" id="main-content">
        <h5>Select an option from the menu to get started.</h5>
      </div>
    </div>
  </div>
</div>

<script>
function loadContent(page) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", page, true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      document.getElementById("main-content").innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}
</script>
</body>
</html>
