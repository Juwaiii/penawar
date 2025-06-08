<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Penawar Appointment System - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .register-header {
            color: #2c786c;
            text-align: center;
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
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, first_name, last_name) 
                                           VALUES (?, ?, 'patient', ?, ?, ?)");
                    $stmt->execute([$username, $hashed_password, $email, $first_name, $last_name]);
                    $user_id = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO patients (user_id, dob, gender, phone, address, blood_group) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
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
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control" name="confirm_password" required>
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
                    <option value="NOT SURE">O-</option>
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
</script>
</body>
</html>
