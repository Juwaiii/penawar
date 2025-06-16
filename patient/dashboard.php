<?php 
session_start();
include '../db.php';
require '../vendor/autoload.php'; // For Dompdf

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../index.php');
    exit();
}

// Get patient info
$stmt = $pdo->prepare("SELECT p.* FROM patients p JOIN users u ON p.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

// ✅ Handle cancel request
if (isset($_GET['cancel'])) {
    $cancelId = intval($_GET['cancel']);
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND patient_id = ?");
    $stmt->execute([$cancelId, $patient['id']]);
    header("Location: dashboard.php");
    exit();
}

// ✅ Get upcoming appointments
$stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name, d.specialization 
                      FROM appointments a 
                      JOIN doctors d ON a.doctor_id = d.id 
                      JOIN users u ON d.user_id = u.id 
                      WHERE a.patient_id = ? 
                        AND a.status IN ('Confirmed', 'Pending')
                        AND a.appointment_date >= CURDATE() 
                      ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute([$patient['id']]);
$appointments = $stmt->fetchAll();

// ✅ Filter for appointment history
$filter = $_GET['history_filter'] ?? 'all';
$filterQuery = "";

if ($filter === 'completed') {
    $filterQuery = "AND a.status = 'Completed'";
} elseif ($filter === 'cancelled') {
    $filterQuery = "AND a.status = 'Cancelled'";
}

// ✅ Show all past or completed appointments (even if date >= today)
$query = "SELECT a.*, u.first_name, u.last_name, d.specialization 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          JOIN users u ON d.user_id = u.id 
          WHERE a.patient_id = ? 
            AND a.status IN ('Completed', 'Cancelled')
            $filterQuery
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$patient['id']]);
$pastAppointments = $stmt->fetchAll();

// ✅ Handle export to PDF
if (isset($_GET['export_pdf'])) {
    $dompdf = new Dompdf();
    ob_start();
    echo "<h2>Appointment History</h2><table border='1' width='100%' cellpadding='5'><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th></tr>";
    foreach ($pastAppointments as $app) {
        echo "<tr><td>" . $app['appointment_date'] . "</td><td>" . $app['appointment_time'] . "</td><td>Dr. " . htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) . "</td><td>" . $app['status'] . "</td></tr>";
    }
    echo "</table>";
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("appointment_history.pdf", ["Attachment" => 1]);
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard - Klinik Penawar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .appointment-card {
            border-left: 4px solid var(--accent-color);
        }

        .doctor-name {
            color: var(--primary-color);
            font-weight: 600;
        }

        .specialization-badge {
            background-color: var(--secondary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .status-confirmed {
            background-color: #28a745;
            color: white;
        }

        .status-pending {
            background-color: #ffc107;
            color: #343a40;
        }

        .action-btn {
            padding: 12px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s;
            text-align: left;
            padding-left: 20px;
        }

        .action-btn i {
            margin-right: 10px;
        }

        .no-appointments {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <div class="welcome-box shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="header"><i class="fas fa-user-injured me-2"></i> Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</h2>
                <p class="lead text-muted">You are logged in to <strong>Klinik Penawar</strong> as a patient.</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointments -->
        <div class="col-lg-7">
            <div class="card shadow appointment-card">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2"></i> Upcoming Appointments
                </div>
                <div class="card-body p-0">
                    <?php if (count($appointments) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($appointments as $appointment): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="doctor-name mb-2">
                                                <i class="fas fa-user-md me-2"></i>Dr. <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) ?>
                                            </h5>
                                            <span class="specialization-badge"><?= htmlspecialchars($appointment['specialization']) ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="status-badge <?= $appointment['status'] === 'Confirmed' ? 'status-confirmed' : 'status-pending' ?>">
                                                <?= $appointment['status'] ?>
                                            </span><br>
                                            <small class="text-muted">Appointment ID: <?= $appointment['id'] ?></small><br>
                                            <?php if ($appointment['status'] === 'Pending'): ?>
                                                <a href="?cancel=<?= $appointment['id'] ?>" class="btn btn-sm btn-danger mt-2" onclick="return confirm('Cancel this appointment?');">
                                                    <i class="fas fa-times"></i> Cancel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <p><i class="far fa-calendar-alt me-2"></i> <strong>Date:</strong> <?= date('F j, Y', strtotime($appointment['appointment_date'])) ?></p>
                                            <p><i class="far fa-clock me-2"></i> <strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_time'])) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Reason:</strong></p>
                                            <p class="text-muted"><?= htmlspecialchars($appointment['reason']) ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="no-appointments">
                            <i class="far fa-calendar-times fa-3x mb-3" style="color: #adb5bd;"></i>
                            <p>No upcoming appointments scheduled.</p>
                            <a href="book_appointment.php" class="btn btn-primary mt-2">Book an Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-5">
            <div class="card shadow">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-3">
                        <a href="book_appointment.php" class="btn action-btn btn-primary">
                            <i class="fas fa-calendar-plus"></i> Book New Appointment
                        </a>
                        <a href="medical_records.php" class="btn action-btn btn-secondary">
                            <i class="fas fa-file-medical"></i> View Medical Records
                        </a>
                        <a href="prescriptions.php" class="btn action-btn btn-info text-white">
                            <i class="fas fa-prescription-bottle-alt"></i> View Prescriptions
                        </a>
                        <a href="bill.php" class="btn action-btn btn-warning">
                            <i class="fas fa-file-invoice-dollar"></i> View Bills & Payments
                        </a>
                        <a href="profile.php" class="btn action-btn" style="background-color: #6c757d; color: white;">
                            <i class="fas fa-user-cog"></i> Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Appointment History -->
<div class="card shadow mt-4 appointment-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history me-2"></i> Appointment History</span>
        <div>
            <form method="GET" class="d-inline">
                <select name="history_filter" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                    <option value="all" <?= ($filter === 'all') ? 'selected' : '' ?>>All</option>
                    <option value="completed" <?= ($filter === 'completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($filter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </form>
            <a href="?export_pdf=1<?= $filter !== 'all' ? '&history_filter=' . $filter : '' ?>" class="btn btn-sm btn-outline-danger ms-2">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (count($pastAppointments) > 0): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($pastAppointments as $appointment): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="doctor-name mb-2">
                                    <i class="fas fa-user-md me-2"></i>Dr. <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) ?>
                                </h5>
                                <span class="specialization-badge"><?= htmlspecialchars($appointment['specialization']) ?></span>
                            </div>
                            <div class="text-end">
                                <span class="status-badge <?= $appointment['status'] === 'Completed' ? 'status-confirmed' : 'status-pending' ?>">
                                    <?= $appointment['status'] ?>
                                </span><br>
                                <small class="text-muted">Appointment ID: <?= $appointment['id'] ?></small>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <p><i class="far fa-calendar-alt me-2"></i> <strong>Date:</strong> <?= date('F j, Y', strtotime($appointment['appointment_date'])) ?></p>
                                <p><i class="far fa-clock me-2"></i> <strong>Time:</strong> <?= date('g:i A', strtotime($appointment['appointment_time'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reason:</strong></p>
                                <p class="text-muted"><?= htmlspecialchars($appointment['reason']) ?></p>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="no-appointments">
                <i class="far fa-calendar-times fa-3x mb-3" style="color: #adb5bd;"></i>
                <p>No past appointments found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
