<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Assignment System Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(to right, #1f4037, #99f2c8);
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: scale(0.95);}
      to {opacity: 1; transform: scale(1);}
    }

    .main-box {
      background: white;
      border-radius: 20px;
      padding: 40px 30px;
      width: 95%;
      max-width: 700px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      text-align: center;
      animation: slideIn 1s ease;
    }

    @keyframes slideIn {
      from {transform: translateY(-50px);}
      to {transform: translateY(0);}
    }

    h2 {
      font-weight: 700;
      color: #1f4037;
      margin-bottom: 20px;
    }

    .btn-group-custom {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
      margin-top: 30px;
    }

    .btn-custom {
      background: #1f4037;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 12px;
      font-weight: 500;
      transition: 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-custom:hover {
      background: #14532d;
      transform: scale(1.05);
    }

    @media(max-width: 600px) {
      .btn-custom {
        width: 100%;
        justify-content: center;
      }
    }

    .role-section {
      margin-top: 30px;
    }

    .role-title {
      font-weight: 600;
      margin-bottom: 10px;
      color: #14532d;
      font-size: 1.2rem;
    }
  </style>
</head>
<body>
  <div class="main-box">
    <h2><i class="ri-book-3-line"></i> Assignment Submission System</h2>

    <div class="role-section">
      <div class="role-title"><i class="ri-admin-line"></i> Admin</div>
      <div class="btn-group-custom">
        <a href="admin/login.php" class="btn-custom"><i class="ri-login-box-line"></i> Admin Login</a>
      </div>
    </div>

    <div class="role-section">
      <div class="role-title"><i class="ri-user-line"></i> Teacher</div>
      <div class="btn-group-custom">
        <a href="teacher/signup.php" class="btn-custom"><i class="ri-user-add-line"></i> Teacher Signup</a>
        <a href="teacher/login.php" class="btn-custom"><i class="ri-login-box-line"></i> Teacher Login</a>
      </div>
    </div>

    <div class="role-section">
      <div class="role-title"><i class="ri-user-smile-line"></i> Student</div>
      <div class="btn-group-custom">
        <a href="student/student_signup.php" class="btn-custom"><i class="ri-user-add-line"></i> Student Signup</a>
        <a href="student/student_login.php" class="btn-custom"><i class="ri-login-box-line"></i> Student Login</a>
      </div>
    </div>
  </div>
</body>
</html>
