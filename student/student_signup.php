<?php
session_start();
include('../config.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $student_id = trim($_POST['student_id']);
    $department = $_POST['department'];
    $section = $_POST['section'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gender = $_POST['gender'] ?? '';
    $agree = $_POST['agree'] ?? '';

    if (!$name || !$email || !$student_id || !$department || !$section || !$password || !$confirm_password || !$gender || !$agree) {
        $errors[] = "All fields including privacy agreement are required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
        $stmt->bind_param("ss", $email, $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email or Student ID already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $role = 'student';
            $stmt = $conn->prepare("INSERT INTO users (name, email, student_id, department, sections, password, gender, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssssss", $name, $email, $student_id, $department, $section, $hash, $gender, $role);
            if ($stmt->execute()) {
                $success = "Signup successful! <a href='student_login.php'>Click here to login</a>.";
            } else {
                $errors[] = "Signup failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            font-family: 'Segoe UI', sans-serif;
        }
        .signup-box {
            max-width: 650px;
            margin: 60px auto;
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            animation: fadeIn 1s ease-in-out;
        }
        h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background-color: #2980b9;
            border: none;
        }
        .btn-primary:hover {
            background-color: #21618c;
        }
        .form-check-label a {
            color: #2980b9;
        }
        .go-home {
            margin-bottom: 20px;
            display: inline-block;
            color: #2980b9;
            text-decoration: none;
        }
        .go-home:hover {
            text-decoration: underline;
        }
        .fade-bottom {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
        }
        .fade-bottom a {
            text-decoration: none;
            color: #21618c;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="signup-box">
    <a href="../index.php" class="go-home">&larr; Go to Home</a>

    <h3>Student Signup</h3>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>• $e</div>"; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required value="<?= $_POST['name'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= $_POST['email'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-control" required value="<?= $_POST['student_id'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department" class="form-select" required>
                <option value="">-- Select Department --</option>
                <?php
                $departments = [
                    "Information Technology",
                    "BTech",
                    "Bachelors in Computer Science",
                    "Financial Accounting",
                    "Business Administration",
                    "Software Engineering"
                ];
                foreach ($departments as $dep) {
                    $selected = ($_POST['department'] ?? '') == $dep ? 'selected' : '';
                    echo "<option value='$dep' $selected>$dep</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Section</label>
            <select name="section" class="form-select" required>
                <option value="">-- Select Section --</option>
                <?php
$sections = ['A', 'B', 'C', 'D'];
foreach ($sections as $sec) {
    $selected = ($_POST['section'] ?? '') == $sec ? 'selected' : '';
    echo "<option value='$sec' $selected>Section $sec</option>";
}
?>

            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Gender</label><br>
            <div class="form-check form-check-inline">
                <input type="radio" name="gender" value="Male" class="form-check-input" required <?= ($_POST['gender'] ?? '') == 'Male' ? 'checked' : '' ?>>
                <label class="form-check-label">Male</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" name="gender" value="Female" class="form-check-input" required <?= ($_POST['gender'] ?? '') == 'Female' ? 'checked' : '' ?>>
                <label class="form-check-label">Female</label>
            </div>
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" name="agree" class="form-check-input" required>
            <label class="form-check-label">I agree to the <a href="#">privacy policy</a></label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <div class="fade-bottom">
        Already have an account? <a href="student_login.php">Login here</a>
    </div>
</div>

</body>
</html>
