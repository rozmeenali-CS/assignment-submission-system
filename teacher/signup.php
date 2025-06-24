<?php
include('../config.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $teacher_id = trim($_POST['teacher_id']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? '';
    $sections = $_POST['sections'] ?? [];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $policy = isset($_POST['policy']);
    $role = "teacher";

    if (empty($name)) $errors[] = "Full Name is required.";
    if (empty($teacher_id)) $errors[] = "Teacher ID is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($department)) $errors[] = "Department is required.";
    if (empty($phone)) $errors[] = "Phone Number is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($sections)) $errors[] = "Please select at least one section.";
    if (empty($password)) $errors[] = "Password is required.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (!$policy) $errors[] = "You must agree to the Privacy Policy.";

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // ✅ Clean section values like "Section A" → "A"
$cleaned_sections = array_map(function($s) {
    return trim(str_replace('Section ', '', $s));
}, $sections);
$sections_str = implode(',', $cleaned_sections); // Now stores "A,B,C"
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (name, teacher_id, email, department, phone, gender, sections, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssss", $name, $teacher_id, $email, $department, $phone, $gender, $sections_str, $hashedPassword, $role);

        if ($stmt->execute()) {
            $success = "Signup successful. You can now login.";
        } else {
            $errors[] = "Error saving data.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Teacher Signup</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .signup-box {
      background: white;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 600px;
      animation: fadeIn 1s ease;
      overflow-y: auto;
      max-height: 90vh;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-30px);}
      to {opacity: 1; transform: translateY(0);}
    }

    h3 {
      text-align: center;
      color: #2c5364;
      margin-bottom: 25px;
    }

    .form-floating input,
    .form-select {
      border-radius: 10px;
      border: 1px solid #ccc;
      transition: all 0.3s ease;
    }

    .form-floating input:focus,
    .form-select:focus {
      border-color: #2c5364;
      box-shadow: 0 0 5px rgba(44,83,100,0.5);
    }

    .btn-signup {
      width: 100%;
      background: #2c5364;
      color: white;
      font-weight: bold;
      border-radius: 10px;
      transition: 0.3s ease;
    }

    .btn-signup:hover {
      background: #1b3a4b;
      transform: scale(1.02);
    }

    .alert {
      font-size: 0.95rem;
    }

    label.form-label {
      font-weight: 500;
      margin-top: 10px;
    }
	.form-check-inline {
  margin-right: 15px;
  margin-bottom: 8px;
}

.w-45 {
  width: 48%;
}

a.btn:hover {
  transform: scale(1.03);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

  </style>
</head>
<body>
  <div class="signup-box">
    <h3><i class="ri-user-add-line"></i> Teacher Signup</h3>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach($errors as $err) echo "<div>$err</div>"; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-3">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" name="name" id="name" placeholder="Full Name" required>
        <label for="name"><i class="ri-user-line"></i> Full Name</label>
      </div>

      <div class="form-floating mb-3">
        <input type="text" class="form-control" name="teacher_id" id="teacher_id" placeholder="Teacher ID" required>
        <label for="teacher_id"><i class="ri-id-card-line"></i> Teacher ID</label>
      </div>

      <div class="form-floating mb-3">
        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
        <label for="email"><i class="ri-mail-line"></i> Email</label>
      </div>

      <div class="form-floating mb-3">
        <input type="text" class="form-control" name="phone" id="phone" placeholder="Phone" required>
        <label for="phone"><i class="ri-phone-line"></i> Phone Number</label>
      </div>

      <div class="mb-3">
        <label for="gender" class="form-label"><i class="ri-user-3-line"></i> Gender</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" value="Male" id="male">
          <label class="form-check-label" for="male">Male</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" value="Female" id="female">
          <label class="form-check-label" for="female">Female</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" value="Other" id="other">
          <label class="form-check-label" for="other">Other</label>
        </div>
      </div>

      <div class="mb-3">
        <label for="department" class="form-label"><i class="ri-building-line"></i> Department</label>
        <select name="department" id="department" class="form-select" required>
          <option value="">-- Select Department --</option>
          <option>Information Technology</option>
          <option>BTech</option>
          <option>Bachelors in Computer Science</option>
          <option>Financial Accounting</option>
          <option>Business Administration</option>
          <option>Software Engineering</option>
        </select>
      </div>

      <div class="mb-3">
  <label class="form-label"><i class="ri-group-line"></i> Select Sections (tick one or more)</label>
  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="sections[]" value="Section A" id="sectionA">
    <label class="form-check-label" for="sectionA">Section A</label>
  </div>
  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="sections[]" value="Section B" id="sectionB">
    <label class="form-check-label" for="sectionB">Section B</label>
  </div>
  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="sections[]" value="Section C" id="sectionC">
    <label class="form-check-label" for="sectionC">Section C</label>
  </div>
  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="sections[]" value="Section D" id="sectionD">
    <label class="form-check-label" for="sectionD">Section D</label>
  </div>
</div>


      <div class="form-floating mb-3">
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
        <label for="password"><i class="ri-lock-password-line"></i> Password</label>
      </div>

      <div class="form-floating mb-3">
        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        <label for="confirm_password"><i class="ri-lock-line"></i> Confirm Password</label>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="policy" id="policy" required>
        <label class="form-check-label" for="policy">I agree to the <a href="#">Privacy Policy</a></label>
      </div>

      <button type="submit" class="btn btn-signup mt-2">Sign Up</button>
    </form>
	<div class="d-flex justify-content-between mt-4">
  <a href="../index.php" class="btn btn-outline-primary w-45" style="border-radius: 10px; font-weight: 500;">
    <i class="ri-home-4-line"></i> Home
  </a>
  <a href="login.php" class="btn btn-outline-success w-45" style="border-radius: 10px; font-weight: 500;">
    <i class="ri-login-circle-line"></i> Teacher Login
  </a>
</div>

  </div>
</body>
</html>
