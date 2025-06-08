<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db.php';

// Restrict to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Count stats
$patients_count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$doctors_count = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$appointments_count = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pending_appointments_count = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();

// Doctor registration logic
$doctorErrors = [];
$doctorSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_doctor'])) {
    $dUsername = trim($_POST['username']);
    $dPassword = $_POST['password'];
    $dEmail = trim($_POST['email']);
    $dFirst = trim($_POST['first_name']);
    $dLast = trim($_POST['last_name']);
    $dSpecialization = trim($_POST['specialization']);
    $dPhone = trim($_POST['phone']);
    $dAddress = trim($_POST['address']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$dUsername]);
    if ($stmt->fetch()) $doctorErrors[] = "Username already exists.";

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$dEmail]);
    if ($stmt->fetch()) $doctorErrors[] = "Email already exists.";

    if (empty($doctorErrors)) {
        try {
            $pdo->beginTransaction();
            $hashed = password_hash($dPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$dUsername, $hashed, 'doctor', $dEmail, $dFirst, $dLast]);
            $uid = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialization, phone, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$uid, $dSpecialization, $dPhone, $dAddress]);

            $pdo->commit();
            $doctorSuccess = "Doctor registered successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $doctorErrors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Klinik Penawar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c786c;
            --secondary-color: #6b8e23;
            --accent-color: #ff7e5f;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        body {
            background-color: #f0f8ff;
            font-family: 'Poppins', sans-serif;
            background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-pastel-sky-background_23-2148902771.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .welcome-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
        }
        .card {
            border-radius: 15px;
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 15px 20px;
            border-bottom: none;
        }
        .card-stat {
            text-align: center;
            padding: 30px 10px;
            font-size: 1.25rem;
        }
        .card-stat h5 {
            font-weight: bold;
        }
        .quick-actions .btn {
            text-align: left;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .quick-actions .btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="container dashboard-container">

    <div class="welcome-box shadow-sm d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-user-shield me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
            <p class="text-muted mb-0">You are logged in to <strong>Klinik Penawar</strong> as an admin.</p>
        </div>
        <div>
            <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body card-stat"><h5><?= $patients_count ?></h5><p>Patients</p></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body card-stat"><h5><?= $doctors_count ?></h5><p>Doctors</p></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body card-stat"><h5><?= $appointments_count ?></h5><p>Appointments</p></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body card-stat"><h5><?= $pending_appointments_count ?></h5><p>Pending</p></div></div></div>
    </div>

    <div class="card quick-actions shadow">
        <div class="card-header"><i class="fas fa-bolt me-2"></i>Quick Actions</div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <!-- ✅ Fixed appointment summary path -->
                <a href="appointments_summary.php" class="btn btn-primary">
                    <i class="fas fa-calendar-alt"></i> Appointments Summary
                </a>
                <a href="medical_records.php" class="btn btn-success">
                    <i class="fas fa-notes-medical"></i> Medical Records
                </a>
               
                <a href="bills_summary.php" class="btn btn-warning">
                    <i class="fas fa-file-invoice-dollar"></i> Bills Summary
                </a>
                <a href="inventory.php" class="btn btn-info">
                    <i class="fas fa-pills"></i> Inventory Management
                </a>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#doctorModal">
            <i class="fas fa-user-md me-1"></i> Register New Doctor
        </button>
    </div>
</div>

<!-- Doctor Registration Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" aria-labelledby="doctorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Register New Doctor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($doctorErrors)): ?>
            <div class="alert alert-danger"><?php foreach ($doctorErrors as $err) echo htmlspecialchars($err) . "<br>"; ?></div>
        <?php elseif ($doctorSuccess): ?>
            <div class="alert alert-success"><?= htmlspecialchars($doctorSuccess) ?></div>
        <?php endif; ?>
        <div class="row">
          <div class="col-md-6 mb-3"><label>Username *</label><input type="text" name="username" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>Password *</label><input type="password" name="password" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>First Name *</label><input type="text" name="first_name" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>Specialization *</label><input type="text" name="specialization" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>Phone *</label><input type="text" name="phone" class="form-control" required></div>
          <div class="col-md-6 mb-3"><label>Address *</label><textarea name="address" class="form-control" rows="2" required></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="register_doctor" class="btn btn-success">Register Doctor</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
