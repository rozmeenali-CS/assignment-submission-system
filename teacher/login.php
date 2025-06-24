<?php
session_start();
include('../config.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $department = trim($_POST['department']);

    if (empty($email) || empty($password) || empty($department)) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND department = ? AND role = 'teacher' LIMIT 1");
        $stmt->bind_param("ss", $email, $department);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['teacher_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['department'] = $user['department'];
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Invalid email, password, or department.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(to right, #283E51, #485563);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-box {
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h3 {
      text-align: center;
      color: #485563;
      margin-bottom: 25px;
    }

    .btn-login {
      background-color: #485563;
      color: #fff;
      width: 100%;
      border-radius: 10px;
      font-weight: 600;
    }

    .btn-login:hover {
      background-color: #2c3e50;
    }

    .alert {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h3>Teacher Login</h3>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach($errors as $err) echo "<div>$err</div>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-floating mb-3">
        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
        <label for="email">Email</label>
      </div>

      <div class="form-floating mb-3">
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
        <label for="password">Password</label>
      </div>

      <div class="mb-3">
        <label for="department" class="form-label">Select Department</label>
        <select name="department" class="form-select" id="department" required>
          <option value="">-- Choose Department --</option>
          <option>Information Technology</option>
          <option>BTech</option>
          <option>Bachelors in Computer Science</option>
          <option>Financial Accounting</option>
          <option>Business Administration</option>
          <option>Software Engineering</option>
        </select>
      </div>

      <button type="submit" class="btn btn-login">Login</button>
    </form>

    <div class="mt-4 text-center">
      <a href="../index.php" class="text-decoration-none">← Back to Home</a>
    </div>
  </div>
</body>
</html>
