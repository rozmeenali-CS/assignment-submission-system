<?php
session_start();
include('../config.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$department = trim($student['department']);
$section = trim($student['sections']); // e.g., "D"

// Fetch assignments matching student’s department and section
$query = $conn->prepare("SELECT * FROM assignments WHERE department = ? AND FIND_IN_SET(?, sections)");
$query->bind_param("ss", $department, $section);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Assignments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h4 class="mb-3">📄 Available Assignments</h4>

    <?php if ($result->num_rows == 0): ?>
      <div class="alert alert-warning">No assignments available for your section yet.</div>
    <?php endif; ?>

    <?php while ($assignment = $result->fetch_assoc()): ?>
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <h5><?php echo htmlspecialchars($assignment['name']); ?></h5>
          <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
          
          <?php
            // Check if already submitted
            $sub = $conn->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $sub->bind_param("ii", $assignment['id'], $student_id);
            $sub->execute();
            $already = $sub->get_result()->fetch_assoc();
          ?>
          
          <?php if ($already): ?>
            <div class="alert alert-success">
              ✅ Submitted: <a href="../uploads/<?php echo $already['file_path']; ?>" target="_blank">View File</a><br>
              📅 Date: <?php echo date('d M Y', strtotime($already['submitted_at'])); ?><br>
              📝 Feedback: <strong><?php echo $already['feedback'] ?? 'No feedback yet'; ?></strong>
            </div>
          <?php else: ?>
            <form method="POST" action="upload_submission.php" enctype="multipart/form-data">
              <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
              <input type="hidden" name="teacher_id" value="<?php echo $assignment['teacher_id']; ?>">
              
              <div class="mb-2">
                <label class="form-label">Upload Your Assignment</label>
                <input type="file" name="submission_file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.mp3,.mp4">
              </div>

              <button type="submit" class="btn btn-primary">📤 Submit Assignment</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</body>
</html>
