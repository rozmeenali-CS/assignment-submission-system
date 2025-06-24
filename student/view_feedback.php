<?php
session_start();
include('../config.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student's submitted assignments with feedback
$stmt = $conn->prepare("
  SELECT s.*, a.name AS assignment_title
  FROM submissions s
  JOIN assignments a ON a.id = s.assignment_id
  WHERE s.student_id = ?
  ORDER BY s.submitted_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h4 class="mb-3">📚 Feedback & Remarks</h4>

<?php if ($result->num_rows === 0): ?>
  <div class="alert alert-info">You have not submitted any assignments yet.</div>
<?php else: ?>
  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($row['assignment_title']); ?></h5>
        <p><strong>📅 Submitted At:</strong> <?php echo date('d M Y, h:i A', strtotime($row['submitted_at'])); ?></p>
        <p><strong>📎 Your File:</strong> 
          <a href="../uploads/<?php echo $row['file_path']; ?>" target="_blank">View Submission</a>
        </p>
        <p><strong>📝 Teacher's Feedback:</strong> 
          <?php echo $row['feedback'] ? nl2br(htmlspecialchars($row['feedback'])) : '<span class="text-muted">Not given yet</span>'; ?>
        </p>
        <p><strong>🎯 Marks:</strong> 
          <?php echo $row['marks'] !== null ? "<span class='badge bg-success'>{$row['marks']}</span>" : "<span class='text-muted'>Not assigned</span>"; ?>
        </p>
      </div>
    </div>
  <?php endwhile; ?>
<?php endif; ?>
