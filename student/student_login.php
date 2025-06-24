<?php
session_start();
include('../config.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $department = $_POST['department'];
    $section = $_POST['section'];

    if (!$email || !$password || !$department || !$section) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND department = ? AND sections = ? AND role = 'student'");
        $stmt->bind_param("sss", $email, $department, $section);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['name'];
            $_SESSION['student_section'] = $user['sections'];
            $_SESSION['student_department'] = $user['department'];
            header("Location: student_dashboard.php");
            exit();
        } else {
            $error = "Invalid login credentials or mismatched department/section.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #56ccf2, #2f80ed);
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box {
            max-width: 500px;
            margin: 70px auto;
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            animation: fadeIn 1s ease-in-out;
        }
        h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        .btn-primary {
            background-color: #2f80ed;
            border: none;
        }
        .btn-primary:hover {
            background-color: #256cd1;
        }
        .go-home {
            margin-bottom: 20px;
            display: inline-block;
            color: #2f80ed;
            text-decoration: none;
        }
        .go-home:hover {
            text-decoration: underline;
        }
        .fade-bottom {
            text-align: center;
            margin-top: 25px;
        }
        .fade-bottom a {
            text-decoration: none;
            color: #256cd1;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="login-box">
    <a href="../index.php" class="go-home">&larr; Go to Home</a>
    <h3>Student Login</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= $_POST['email'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
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

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="fade-bottom">
        Don't have an account? <a href="student_signup.php">Register here</a>
    </div>
</div>

</body>
</html>
