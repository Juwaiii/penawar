<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Klinik Penawar Appointment System - Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    :root {
      --primary-color: #2c786c;
      --accent-color: #004445;
    }
    body {
      background-color: #f0f8ff;
      font-family: 'Poppins', sans-serif;
      background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-pastel-sky-background_23-2148902771.jpg');
      background-size: cover;
      background-attachment: fixed;
    }
    .register-container {
      max-width: 600px;
      margin: 50px auto;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .register-header {
      color: var(--primary-color);
      text-align: center;
      margin-bottom: 30px;
    }
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    .btn-primary:hover {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
    }
  </style>
</head>
<body>
<div class="container">
  <div class="register-container">
    <h2 class="register-header"><i class="fas fa-user-plus"></i><br>Patient Registration</h2>

    <?php
    session_start();
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $username = trim($_POST['username'] ?? '');
      $password = $_POST['password'] ?? '';
      $confirm_password = $_POST['confirm_password'] ?? '';
      $email = trim($_POST['email'] ?? '');
      $first_name = trim($_POST['first_name'] ?? '');
      $last_name = trim($_POST['last_name'] ?? '');
      $dob = $_POST['dob'] ?? '';
      $gender = $_POST['gender'] ?? '';
      $phone = trim($_POST['phone'] ?? '');
      $address = trim($_POST['address'] ?? '');
      $blood_group = $_POST['blood_group'] ?? '';

      if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
      } elseif (strlen($password) < 8 || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must be at least 8 characters long and include at least one number and one symbol.";
      }

      $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
      $stmt->execute([$username]);
      if ($stmt->fetch()) {
        $errors[] = "Username already taken.";
      }

      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $errors[] = "Email already registered.";
      }

      if (empty($errors)) {
        try {
          $pdo->beginTransaction();
          $hashed_password = password_hash($password, PASSWORD_DEFAULT);
          $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, first_name, last_name) VALUES (?, ?, 'patient', ?, ?, ?)");
          $stmt->execute([$username, $hashed_password, $email, $first_name, $last_name]);
          $user_id = $pdo->lastInsertId();

          $stmt = $pdo->prepare("INSERT INTO patients (user_id, dob, gender, phone, address, blood_group) VALUES (?, ?, ?, ?, ?, ?)");
          $stmt->execute([$user_id, $dob, $gender, $phone, $address, $blood_group]);

          $pdo->commit();
          $_SESSION['success_message'] = "Registration successful! Please login.";
          header("Location: index.php");
          exit();

        } catch (PDOException $e) {
          $pdo->rollBack();
          $errors[] = "Registration failed: " . $e->getMessage();
        }
      }

      if (!empty($errors)) {
        echo '<div class="alert alert-danger">';
        foreach ($errors as $error) {
          echo htmlspecialchars($error) . '<br>';
        }
        echo '</div>';
      }
    }
    ?>

    <form method="POST">
      <h5 class="text-muted mb-3">Account Information</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Username *</label>
          <input type="text" class="form-control" name="username" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email *</label>
          <input type="email" class="form-control" name="email" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Password *</label>
          <div class="input-group">
            <input type="password" class="form-control" name="password" id="password" required
                   pattern="(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}"
                   title="Password must be at least 8 characters long and include at least one number and one symbol.">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Confirm Password *</label>
          <div class="input-group">
            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </div>

      <h5 class="text-muted mb-3 mt-4">Personal Information</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">First Name *</label>
          <input type="text" class="form-control" name="first_name" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Last Name *</label>
          <input type="text" class="form-control" name="last_name" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Date of Birth *</label>
          <input type="date" class="form-control" name="dob" id="dob" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Gender *</label>
          <select class="form-select" name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Phone Number *</label>
        <input type="tel" class="form-control" name="phone" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Address *</label>
        <textarea class="form-control" name="address" rows="2" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Blood Group</label>
        <select class="form-select" name="blood_group">
          <option value="">Select Blood Group</option>
          <option value="A+">A+</option>
          <option value="A-">A-</option>
          <option value="B+">B+</option>
          <option value="B-">B-</option>
          <option value="AB+">AB+</option>
          <option value="AB-">AB-</option>
          <option value="O+">O+</option>
          <option value="O-">O-</option>
          <option value="NOT SURE">NOT SURE</option>
        </select>
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Register</button>
        <a href="index.php" class="btn btn-secondary">Back to Login</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('dob').max = new Date(new Date().setFullYear(new Date().getFullYear() - 18)).toISOString().split('T')[0];

  function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  }
</script>
</body>
</html>
