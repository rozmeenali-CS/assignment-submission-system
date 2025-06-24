<?php
session_start();
include('../config.php');

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if (!isset($_GET['id'])) {
    echo "Invalid request!";
    exit();
}

$assignment_id = $_GET['id'];

// Fetch assignment to edit
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $assignment_id, $teacher_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    echo "Assignment not found or unauthorized!";
    exit();
}

// Fetch teacher's sections
$user = $conn->query("SELECT sections FROM users WHERE id = $teacher_id")->fetch_assoc();
$sections = explode(",", $user['sections']);
$selected_sections = explode(",", $assignment['sections']);
$department = $assignment['department'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $updated_sections = isset($_POST['sections']) ? implode(",", $_POST['sections']) : '';
    $file_path = $assignment['file_path'];

    // File update
    if (!empty($_FILES['assignment_file']['name'])) {
        $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'mp3', 'mp4'];
        $file_name = basename($_FILES['assignment_file']['name']);
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_extensions)) {
            $new_file = time() . "_" . $file_name;
            $target = "../uploads/" . $new_file;

            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
                // Delete old file
                if (!empty($file_path) && file_exists("../uploads/" . $file_path)) {
                    unlink("../uploads/" . $file_path);
                }
                $file_path = $new_file;
            }
        }
    }

    // Update in database
    $stmt = $conn->prepare("UPDATE assignments SET name=?, description=?, file_path=?, sections=? WHERE id=? AND teacher_id=?");
    $stmt->bind_param("ssssii", $title, $description, $file_path, $updated_sections, $assignment_id, $teacher_id);
    $stmt->execute();

    header("Location: add_assignment.php?updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3>Edit Assignment</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Assignment Title</label>
      <input type="text" name="title" class="form-control" value="<?php echo $assignment['name']; ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3" required><?php echo $assignment['description']; ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Current File</label><br>
      <?php if (!empty($assignment['file_path'])): ?>
        <a href="../uploads/<?php echo $assignment['file_path']; ?>" target="_blank">View Current File</a>
      <?php else: ?>
        <span class="text-muted">No file</span>
      <?php endif; ?>
    </div>
    <div class="mb-3">
      <label class="form-label">Upload New File (optional)</label>
      <input type="file" name="assignment_file" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Select Sections</label>
      <?php foreach ($sections as $sec): ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="sections[]" value="<?php echo trim($sec); ?>" <?php echo in_array(trim($sec), $selected_sections) ? 'checked' : ''; ?>>
          <label class="form-check-label">Section <?php echo trim($sec); ?></label>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary">Update Assignment</button>
    <a href="add_assignment.php" class="btn btn-secondary">Back</a>
  </form>
</div>
</body>
</html>
