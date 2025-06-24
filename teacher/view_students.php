<?php
session_start();
include('../config.php');

if (!isset($_SESSION['teacher_id'])) {
    echo "Unauthorized access!";
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if (!isset($_POST['section'])) {
    echo "Section not selected.";
    exit();
}

$section = $_POST['section'];

// Fetch students in this section
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'student' AND FIND_IN_SET(?, sections)");
$stmt->bind_param("s", $section);
$stmt->execute();
$students = $stmt->get_result();

if ($students->num_rows === 0) {
    echo "<div class='alert alert-warning'>No students found in $section.</div>";
    exit();
}
?>

<h5>Students of Section <?php echo htmlspecialchars($section); ?></h5>

<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Assignment</th>
        <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($student = $students->fetch_assoc()): ?>
        <?php
        $student_id = $student['id'];

        $submission_query = $conn->prepare("
          SELECT s.*, a.name AS assignment_name 
          FROM submissions s 
          JOIN assignments a ON a.id = s.assignment_id 
          WHERE s.student_id = ? AND a.teacher_id = ? 
          ORDER BY s.id DESC LIMIT 1
        ");
        $submission_query->bind_param("ii", $student_id, $teacher_id);
        $submission_query->execute();
        $submission = $submission_query->get_result()->fetch_assoc();
        ?>
        <tr>
          <td><?php echo $student['student_id']; ?></td>
          <td><?php echo $student['name']; ?></td>
          <td><?php echo $student['email']; ?></td>
          <td>
            <?php if ($submission): ?>
              <a href="../uploads/<?php echo $submission['file_path']; ?>" target="_blank">
                <?php echo htmlspecialchars($submission['assignment_name']); ?>
              </a>
            <?php else: ?>
              <span class="text-muted">No Submission</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($submission): ?>
              <div class="input-group">
                <input type="hidden" class="submission-id" value="<?php echo $submission['id']; ?>">
                <input type="text" class="form-control remark-input" value="<?php echo $submission['feedback'] ?? ''; ?>" placeholder="Enter remarks">
                <button class="btn btn-success save-remark-btn">Save</button>
              </div>
              <div class="text-success small mt-1 saved-msg" style="display:none;">Saved ✔</div>
            <?php else: ?>
              <span class="text-muted">N/A</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($submission): ?>
              <small class="text-success">Submitted on <?php echo date('d M Y', strtotime($submission['submitted_at'])); ?></small>
            <?php else: ?>
              <small class="text-muted">--</small>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  document.querySelectorAll(".save-remark-btn").forEach(button => {
    button.addEventListener("click", function () {
      const group = this.closest(".input-group");
      const submissionId = group.querySelector(".submission-id").value;
      const remarks = group.querySelector(".remark-input").value;
      const msg = group.parentElement.querySelector(".saved-msg");

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "submit_remarks.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        if (xhr.status == 200 && xhr.responseText.trim() === "success") {
          msg.style.display = "block";
          setTimeout(() => msg.style.display = "none", 2000);
        } else {
          alert("Failed to save remarks. Try again.");
        }
      };
      xhr.send("submission_id=" + encodeURIComponent(submissionId) + "&remarks=" + encodeURIComponent(remarks));
    });
  });
</script>
