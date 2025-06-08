<?php
include 'db.php';
session_start();

// ✅ Auto-redirect based on role
if (isset($_SESSION['username'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            exit();
        case 'doctor':
            header('Location: doctor/dashboard.php');
            exit();
        case 'patient':
            header('Location: patient/dashboard.php');
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Penawar Appointment System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: 'Poppins', sans-serif;
            background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-pastel-sky-background_23-2148902771.jpg');
            background-size: cover;
            background-attachment: fixed;
        }
        .login-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            color: #2c786c;
            margin-bottom: 25px;
        }
        .btn-primary {
            background-color: #2c786c;
            border-color: #2c786c;
        }
        .btn-primary:hover {
            background-color: #004445;
            border-color: #004445;
        }
        .role-option {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }
        .role-option:hover {
            background-color: #f1f1f1;
        }
        .role-option.active {
            background-color: #2c786c;
            color: white;
            border-color: #2c786c;
        }
        .role-option input {
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <h2 class="login-header">
            <i class="fas fa-clinic-medical"></i><br>
            Klinik Penawar Appointment System
        </h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
            $stmt->execute([$username, $role]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'doctor':
                        header('Location: doctor/dashboard.php');
                        break;
                    case 'patient':
                        header('Location: patient/dashboard.php');
                        break;
                }
                exit();
            } else {
                echo '<div class="alert alert-danger">Invalid username or password for the selected role.</div>';
            }
        }
        ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Login as:</label>
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" name="role" value="patient" id="role-patient" checked>
                        <label for="role-patient"><i class="fas fa-user me-1"></i>Patient</label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="role" value="doctor" id="role-doctor">
                        <label for="role-doctor"><i class="fas fa-user-md me-1"></i>Doctor</label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="role" value="admin" id="role-admin">
                        <label for="role-admin"><i class="fas fa-user-shield me-1"></i>Admin</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="text-center mt-3">
            <small>Don't have an account? <a href="register.php">Register</a></small>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelectorAll('.role-option');
        options.forEach(opt => {
            const input = opt.querySelector('input');
            if (input.checked) opt.classList.add('active');
            opt.addEventListener('click', () => {
                document.querySelectorAll('.role-option').forEach(o => o.classList.remove('active'));
                input.checked = true;
                opt.classList.add('active');
            });
        });
    });
</script>
</body>
</html>
