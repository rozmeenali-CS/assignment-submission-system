<?php
session_start();
include('../config.php');

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch all assignments for dropdown and listing
$assignments_stmt = $conn->prepare("SELECT * FROM assignments WHERE teacher_id = ? ORDER BY created_at DESC");
$assignments_stmt->bind_param("i", $teacher_id);
$assignments_stmt->execute();
$result = $assignments_stmt->get_result();
$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

// Fetch all sections for filter dropdown
$section_list = [];
$sec_stmt = $conn->prepare("SELECT DISTINCT sections FROM assignments WHERE teacher_id = ?");
$sec_stmt->bind_param("i", $teacher_id);
$sec_stmt->execute();
$sec_result = $sec_stmt->get_result();
while ($row = $sec_result->fetch_assoc()) {
    $parts = explode(",", $row['sections']);
    foreach ($parts as $part) {
        $section_list[trim($part)] = true;
    }
}
$section_list = array_keys($section_list);

// Get filters
$filter_section = $_GET['section'] ?? '';
$filter_assignment = $_GET['assignment'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fa; font-family: 'Segoe UI', sans-serif; padding: 30px; }
        .assignment-card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .saved-msg { font-size: 13px; color: green; display: none; }
    </style>
</head>
<body>
<div class="container">
    <h3 class="mb-4">Submitted Assignments</h3>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Filter by Section</label>
            <select name="section" class="form-select">
                <option value="">All Sections</option>
                <?php foreach($section_list as $sec): ?>
                    <option value="<?= $sec ?>" <?= ($filter_section == $sec ? 'selected' : '') ?>><?= $sec ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Filter by Assignment</label>
            <select name="assignment" class="form-select">
                <option value="">All Assignments</option>
                <?php foreach($assignments as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($filter_assignment == $a['id'] ? 'selected' : '') ?>><?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <?php foreach($assignments as $assignment):
        if ($filter_assignment && $filter_assignment != $assignment['id']) continue;
        $assignment_sections = explode(",", $assignment['sections']);
        if ($filter_section && !in_array($filter_section, $assignment_sections)) continue;
    ?>
        <div class="assignment-card">
            <h5><?= htmlspecialchars($assignment['name']) ?></h5>
            <p><strong>Sections:</strong> <?= htmlspecialchars($assignment['sections']) ?></p>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>File</th>
                        <th>Submitted</th>
                        <th>Marks (0-25)</th>
                        <th>Feedback</th>
                        <th>Save</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $aid = $assignment['id'];
                $sub_stmt = $conn->prepare("
                    SELECT s.*, u.name as student_name 
                    FROM submissions s 
                    JOIN users u ON u.id = s.student_id 
                    WHERE s.assignment_id = ?
                ");
                $sub_stmt->bind_param("i", $aid);
                $sub_stmt->execute();
                $subs = $sub_stmt->get_result();

                $hasRows = false;
                while($row = $subs->fetch_assoc()):
                    if ($filter_section && !in_array($filter_section, explode(",", $assignment['sections']))) continue;
                    $hasRows = true;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><a href="../uploads/<?= $row['file_path'] ?>" target="_blank">View</a></td>
                        <td><?= date("d M Y", strtotime($row['submitted_at'])) ?></td>
                        <td><input type="number" min="0" max="25" class="form-control marks-input" value="<?= $row['marks'] ?>" data-id="<?= $row['id'] ?>"></td>
                        <td><textarea class="form-control feedback-input" rows="2" data-id="<?= $row['id'] ?>"><?= $row['feedback'] ?></textarea></td>
                        <td>
                            <button class="btn btn-success btn-sm save-btn" data-id="<?= $row['id'] ?>">Save</button>
                            <div class="saved-msg" id="msg-<?= $row['id'] ?>">Saved ✓</div>
                        </td>
                    </tr>
                <?php endwhile; if (!$hasRows): ?>
                    <tr><td colspan="6" class="text-muted">No submissions found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.save-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const marksInput = document.querySelector(`.marks-input[data-id='${id}']`);
        const feedbackInput = document.querySelector(`.feedback-input[data-id='${id}']`);
        const marks = marksInput.value;
        const feedback = feedbackInput.value;

        if (marks < 0 || marks > 25) {
            alert("Marks should be between 0 and 25.");
            return;
        }

        fetch('save_feedback.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&marks=${encodeURIComponent(marks)}&feedback=${encodeURIComponent(feedback)}`
        }).then(res => res.text()).then(data => {
            if (data === "success") {
                const msg = document.getElementById(`msg-${id}`);
                msg.style.display = "block";
                setTimeout(() => msg.style.display = "none", 1500);
            } else {
                alert("Error saving feedback.");
            }
        });
    });
});
</script>
</body>
</html>
