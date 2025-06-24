<?php
include('../config.php');

$section = $_GET['section'] ?? '';

if (!$section) {
    echo "<div class='alert alert-warning'>No section selected.</div>";
    exit();
}

$query = $conn->prepare("SELECT * FROM students WHERE section = ?");
$query->bind_param("s", $section);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    echo "<h4 class='mt-4'>Students in $section</h4><table class='table table-striped'>
    <thead><tr><th>Name</th><th>Student ID</th><th>Assignment</th><th>Remarks</th><th>Action</th></tr></thead><tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
        <td>{$row['name']}</td>
        <td>{$row['student_id']}</td>
        <td><a href='../uploads/{$row['assignment']}' target='_blank'>View</a></td>
        <td>{$row['remarks']}</td>
        <td><button class='btn btn-sm btn-success' onclick=\"giveRemark('{$row['id']}')\">Give Remark</button></td>
        </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<div class='alert alert-info'>No students found in $section.</div>";
}
?>
<script>
function giveRemark(studentId) {
  let remark = prompt("Enter your remarks or marks:");
  if (remark) {
    fetch("submit_remark.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `student_id=${studentId}&remark=${remark}`
    }).then(res => res.text()).then(msg => {
      alert(msg);
      location.reload(); // Refresh students list
    });
  }
}
</script>
