<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../index.php');
    exit();
}

// ✅ Cancel appointment via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled', cancelled_by = 'doctor' WHERE id = ?");
    echo $stmt->execute([$_POST['id']]) ? "Cancelled" : "Failed";
    exit();
}

// ✅ Confirm or complete via GET
if (isset($_GET['action']) && in_array($_GET['action'], ['confirm', 'complete']) && isset($_GET['id'])) {
    $status = $_GET['action'] === 'confirm' ? 'Confirmed' : 'Completed';
    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $_GET['id']]);
    header("Location: dashboard.php");
    exit();
}

// Get doctor info
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();

if (!$doctor) {
    die("Doctor not found.");
}

$today = date('Y-m-d');

// Fetch today's confirmed appointments
$stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name 
                       FROM appointments a 
                       JOIN patients p ON a.patient_id = p.id 
                       JOIN users u ON p.user_id = u.id 
                       WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.status = 'Confirmed'
                       ORDER BY a.appointment_time");
$stmt->execute([$doctor['id'], $today]);
$todays_appointments = $stmt->fetchAll();

// Fetch pending appointments (exclude cancelled)
$stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name 
                       FROM appointments a 
                       JOIN patients p ON a.patient_id = p.id 
                       JOIN users u ON p.user_id = u.id 
                       WHERE a.doctor_id = ? AND a.status = 'Pending'
                       ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute([$doctor['id']]);
$pending_appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - Klinik Penawar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c786c;
            --secondary-color: #6b8e23;
            --accent-color: #ff7e5f;
        }
        body {
            background-color: #f0f8ff;
            font-family: 'Poppins', sans-serif;
            background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-pastel-sky-background_23-2148902771.jpg');
            background-size: cover;
            background-attachment: fixed;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .welcome-box {
            background: white;
            border-left: 5px solid var(--primary-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .stat-box {
            border-radius: 15px;
            color: white;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 30px 20px;
            margin-bottom: 20px;
        }
        .quick-actions .btn {
            padding: 14px;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 10px;
            margin-bottom: 10px;
            text-align: left;
        }
        .card {
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .card-header {
            font-weight: 600;
            color: white;
            padding: 15px 20px;
        }
        .list-group-item {
            padding: 20px;
            border: none;
            border-bottom: 1px solid #e9ecef;
        }
        .list-group-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
<div class="container dashboard-container">
    <div class="welcome-box d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-user-md me-2"></i>Welcome, Dr. <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
            <p class="text-muted mb-0">You are logged in as <strong>Doctor</strong>.</p>
        </div>
        <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
    </div>

    <div class="row text-white">
        <div class="col-md-6">
            <div class="stat-box bg-primary">
                <?= count($todays_appointments) ?><br>Today's Appointments
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-box bg-warning text-dark">
                <?= count($pending_appointments) ?><br>Pending Appointments
            </div>
        </div>
    </div>

    <div class="card shadow quick-actions">
        <div class="card-header bg-dark">
            <i class="fas fa-bolt me-2"></i>Quick Actions
        </div>
        <div class="card-body d-grid gap-2">
            <a href="appointments.php?filter=pending" class="btn btn-warning text-dark"><i class="fas fa-clock me-2"></i>Pending Appointments</a>
            <a href="medical_records.php" class="btn btn-success"><i class="fas fa-file-medical-alt me-2"></i>Medical Records</a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary"><i class="fas fa-calendar-check me-2"></i>Today's Confirmed Appointments</div>
        <div class="card-body p-0">
            <?php if ($todays_appointments): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($todays_appointments as $a): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></strong><br>
                            <?= date('g:i A', strtotime($a['appointment_time'])) ?> - <?= htmlspecialchars($a['reason']) ?>
                            <div class="text-end mt-2">
                                <a href="dashboard.php?action=complete&id=<?= $a['id'] ?>" class="btn btn-sm btn-success">Mark as Completed</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted p-3">No confirmed appointments for today.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-warning text-dark"><i class="fas fa-clock me-2"></i>Pending Appointments</div>
        <div class="card-body p-0">
            <?php if ($pending_appointments): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pending_appointments as $a): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></strong><br>
                                <?= date('F j, Y', strtotime($a['appointment_date'])) ?> at <?= date('g:i A', strtotime($a['appointment_time'])) ?>
                                - <?= htmlspecialchars($a['reason']) ?>
                            </div>
                            <div class="text-end mt-2">
                                <a href="dashboard.php?action=confirm&id=<?= $a['id'] ?>" class="btn btn-sm btn-success">Confirm</a>
                                <button class="btn btn-sm btn-danger cancel-btn" data-id="<?= $a['id'] ?>">Cancel</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted p-3">No pending appointments.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.cancel-btn').forEach(button => {
    button.addEventListener('click', function () {
        const id = this.dataset.id;
        if (!confirm("Are you sure you want to cancel this appointment?")) return;

        fetch("dashboard.php", {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=cancel&id=${id}`
        })
        .then(res => res.text())
        .then(response => {
            if (response.trim() === "Cancelled") {
                alert("Appointment cancelled successfully.");
                location.reload();
            } else {
                alert("Failed to cancel appointment.");
            }
        })
        .catch(err => {
            alert("Error cancelling appointment.");
            console.error(err);
        });
    });
});
</script>
</body>
</html>
