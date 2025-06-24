<?php
session_start();
include('../config.php');

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher info
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $teacher_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();

$sections = array_map('trim', explode(",", $user['sections']));
$department = $user['department'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $selected_sections = '';
    if (isset($_POST['sections'])) {
        $selected_sections = implode(",", array_map('trim', $_POST['sections']));
    }

    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'mp3', 'mp4'];
    $file_path = '';

    if (!empty($_FILES['assignment_file']['name'])) {
        $file_name = basename($_FILES['assignment_file']['name']);
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_extensions)) {
            $new_filename = time() . "_" . $file_name;
            $target = "../uploads/" . $new_filename;
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
                $file_path = $new_filename;
            } else {
                $error = "❌ File upload failed.";
            }
        } else {
            $error = "❌ Invalid file type.";
        }
    }

    if (!isset($error)) {
        $stmt = $conn->prepare("INSERT INTO assignments (teacher_id, name, description, file_path, department, sections, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssss", $teacher_id, $title, $description, $file_path, $department, $selected_sections);
        $stmt->execute();
        $success = "✅ Assignment posted successfully.";
    }
}

// Fetch all assignments by this teacher
$assignment_list = $conn->query("SELECT * FROM assignments WHERE teacher_id = $teacher_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Assignment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f7fa;
      padding: 30px;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      max-width: 850px;
    }
    .form-section, .assignment-table {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }
    h4 {
      color: #2c3e50;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="form-section">
    <h4 class="mb-3">📌 Add New Assignment</h4>

    <div class="mb-3">
      <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Assignment Title</label>
        <input type="text" name="title" class="form-control" required placeholder="Enter assignment title">
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required placeholder="Enter assignment description"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Upload File</label>
        <input type="file" name="assignment_file" class="form-control">
        <small class="form-text text-muted">Allowed: pdf, doc, xls, ppt, png, jpg, mp3, mp4</small>
      </div>

      <div class="mb-3">
        <label class="form-label">Select Sections</label>
        <?php foreach ($sections as $sec): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="sections[]" value="<?php echo $sec; ?>" id="sec_<?php echo $sec; ?>">
            <label class="form-check-label" for="sec_<?php echo $sec; ?>">Section <?php echo $sec; ?></label>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn btn-primary">➕ Add Assignment</button>
    </form>
  </div>

  <div class="assignment-table">
    <h4 class="mb-3">📄 Your Assignments</h4>
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Title</th>
          <th>Description</th>
          <th>Sections</th>
          <th>File</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($assignment_list->num_rows == 0): ?>
          <tr><td colspan="5" class="text-center text-muted">No assignments added yet.</td></tr>
        <?php else: ?>
          <?php while($row = $assignment_list->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['description']); ?></td>
              <td><?php echo htmlspecialchars($row['sections']); ?></td>
              <td>
                <?php if ($row['file_path']): ?>
                  <a href="../uploads/<?php echo $row['file_path']; ?>" target="_blank">View File</a>
                <?php else: ?>
                  <span class="text-muted">No file</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="edit_assignment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this assignment?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- JS for delete modal -->
<script>
  let assignmentToDelete = null;
  function confirmDelete(id) {
    assignmentToDelete = id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
  }

  document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (assignmentToDelete) {
      window.location.href = 'delete_assignment.php?id=' + assignmentToDelete;
    }
  });
</script>

<!-- Bootstrap JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
