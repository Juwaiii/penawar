<?php
include 'db.php';
session_start();

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$user = null;

if (!$token) {
    $error = "Invalid reset link.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user || strtotime($user['token_expiry']) < time()) {
        $error = "Invalid or expired token.";
        $user = null;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8 || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password)) {
            $error = "Password must be at least 8 characters, include a number and a symbol.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
            $update->execute([$hashed, $user['id']]);
            $success = "Password updated successfully. <a href='index.php'>Login here</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f8ff;
      font-family: 'Poppins', sans-serif;
    }
    .container {
      max-width: 500px;
      margin: 100px auto;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
    .btn-primary {
      background-color: #2c786c;
      border-color: #2c786c;
    }
    .btn-primary:hover {
      background-color: #004445;
      border-color: #004445;
    }
    .input-group-text {
      cursor: pointer;
    }
  </style>
</head>
<body>
<div class="container">
  <h3 class="text-center mb-4">Reset Your Password</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($user): ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="input-group">
          <input type="password" class="form-control" name="password" id="password" required
                 pattern="(?=.*\d)(?=.*[\W_]).{8,}"
                 title="At least 8 characters, including a number and a symbol">
          <span class="input-group-text" onclick="togglePassword('password', this)">👁️</span>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
          <span class="input-group-text" onclick="togglePassword('confirm_password', this)">👁️</span>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Update Password</button>
    </form>
  <?php endif; ?>
</div>

<script>
  function togglePassword(id, el) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
      input.type = 'text';
      el.textContent = '🙈';
    } else {
      input.type = 'password';
      el.textContent = '👁️';
    }
  }
</script>
</body>
</html>
