<?php
session_start();
include('../config.php');

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $teacher_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();

// ✅ Extract name and department
$name = $user['name'];
$department = $user['department'];

$sections = explode(",", $user['sections']);
$section_list = "'" . implode("','", $sections) . "'";

$total_students_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student' AND FIND_IN_SET(users.sections, '$user[sections]')");
$total_students = $total_students_result->fetch_assoc()['total'];

$total_assignments_result = $conn->query("SELECT COUNT(*) as total FROM assignments WHERE teacher_id = '$teacher_id'");
$total_assignments = $total_assignments_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f7fa;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #2c3e50;
      padding: 20px;
      color: white;
    }
    .sidebar h5 {
      color: #ecf0f1;
    }
    .sidebar a {
      color: #bdc3c7;
      display: block;
      margin: 10px 0;
      text-decoration: none;
    }
    .sidebar a:hover {
      color: #ecf0f1;
    }
    .card {
      transition: 0.3s;
    }
    .card:hover {
      transform: scale(1.02);
    }
    .topbar {
      background-color: #ffffff;
      padding: 15px;
      border-bottom: 1px solid #ddd;
    }
    .topbar .right {
      float: right;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 sidebar">
        <h5><i class="ri-dashboard-line"></i> Dashboard</h5>
        <p><strong>Name:</strong> <?php echo $name; ?></p>
        <p><strong>Department:</strong> <?php echo $department; ?></p>
       <p><strong>Sections:</strong>
    <?php
    foreach ($sections as $s) {
        echo "Section " . htmlspecialchars(trim($s)) . " ";
    }
    ?>
</p>


        <hr>
        <h6><i class="ri-group-line"></i> My Sections</h6>
        <?php foreach ($sections as $section): ?>
          <a href="#" onclick="loadStudents('<?php echo trim($section); ?>')"><?php echo trim($section); ?> Students</a>
        <?php endforeach; ?>

        <hr>
        <a href="profile.php"><i class="ri-user-settings-line"></i> Profile Settings</a>
        <a href="add_assignment.php"><i class="ri-upload-cloud-line"></i> Add Assignment</a>
		<a href="view_submissions.php"><i class="ri-file-eye-line"></i> View Submissions</a>

        <a href="logout.php"><i class="ri-logout-box-line"></i> Logout</a>
      </div>

      <!-- Main Content -->
      <div class="col-md-9">
        <div class="topbar">
          <h4>Welcome, <?php echo $name; ?>!</h4>
        </div>

        <div class="container mt-4">
          <div class="row" id="live-cards">
            <!-- Cards will be loaded via AJAX -->
          </div>

          <div id="main-content" class="mt-4">
            <h5>Select a section to view students.</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Load student list
    function loadStudents(section) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "view_students.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        if (xhr.status == 200) {
          document.getElementById("main-content").innerHTML = xhr.responseText;
        }
      };
      xhr.send("section=" + encodeURIComponent(section));
    }

    // Load live cards
    function loadCards() {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "ajax/cards.php", true);
      xhr.onload = function () {
        if (xhr.status == 200) {
          document.getElementById("live-cards").innerHTML = xhr.responseText;
        }
      };
      xhr.send();
    }

    window.onload = function () {
      loadCards();
    };
  </script>
</body>
</html>
