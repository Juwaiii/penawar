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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .login-header {
            color: #2c786c;
            text-align: center;
            margin-bottom: 25px;
        }
        .role-selector {
            margin-bottom: 20px;
        }
        .role-option {
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #ddd;
        }
        .role-option:hover {
            background-color: #f0f0f0;
        }
        .role-option.active {
            background-color: #2c786c;
            color: white;
            border-color: #2c786c;
        }
        .role-option input {
            display: none;
        }
        .btn-primary {
            background-color: #2c786c;
            border-color: #2c786c;
        }
        .btn-primary:hover {
            background-color: #004445;
            border-color: #004445;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="login-header mb-4">
                <i class="fas fa-clinic-medical"></i><br>
                Klinik Penawar Appointment System
            </h2>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

                    // Redirect to appropriate dashboard
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
                    <label class="form-label">I am logging in as:</label>
                    <div class="role-selector">
                        <div class="role-option">
                            <input type="radio" name="role" value="patient" id="role-patient" checked>
                            <label for="role-patient">
                                <i class="fas fa-user"></i> Patient
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" value="doctor" id="role-doctor">
                            <label for="role-doctor">
                                <i class="fas fa-user-md"></i> Doctor
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" value="admin" id="role-admin">
                            <label for="role-admin">
                                <i class="fas fa-user-shield"></i> Admin
                            </label>
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

            <div class="mt-3 text-center">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>

    <script>
        // Activate role option based on selected radio input
        document.addEventListener('DOMContentLoaded', function () {
            const roleInputs = document.querySelectorAll('input[name="role"]');
            roleInputs.forEach(input => {
                if (input.checked) {
                    input.closest('.role-option').classList.add('active');
                }

                input.addEventListener('change', () => {
                    document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                    input.closest('.role-option').classList.add('active');
                });

                // Allow clicking anywhere on the role-option to trigger input
                input.closest('.role-option').addEventListener('click', () => {
                    input.checked = true;
                    input.dispatchEvent(new Event('change'));
                });
            });
        });
    </script>
</body>
</html>
