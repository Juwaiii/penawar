<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'patient'])) {
    header('Location: ../index.php');
    exit();
}

// Handle bill creation by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $description = $_POST['description'];
    $total = floatval($_POST['total']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("INSERT INTO bills (patient_id, doctor_id, description, total, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$patient_id, $doctor_id, $description, $total, $status]);
    header("Location: bills_summary.php");
    exit();
}

// Get bills for patient or all (admin)
if ($_SESSION['role'] === 'patient') {
    $stmt = $pdo->prepare("
        SELECT b.id, b.description, b.total, b.status,
               CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               DATE_FORMAT(b.created_at, '%M %d, %Y %h:%i %p') AS formatted_date
        FROM bills b
        JOIN doctors d ON b.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE b.patient_id = (
            SELECT id FROM patients WHERE user_id = ?
        )
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->query("
        SELECT b.id, b.description, b.total, b.status,
               CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
               CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
               DATE_FORMAT(b.created_at, '%M %d, %Y %h:%i %p') AS formatted_date
        FROM bills b
        JOIN doctors d ON b.doctor_id = d.id
        JOIN users du ON d.user_id = du.id
        JOIN patients p ON b.patient_id = p.id
        JOIN users pu ON p.user_id = pu.id
        ORDER BY b.created_at DESC
    ");
}

$bills = $stmt->fetchAll();

// Get list of patients and doctors for form
if ($_SESSION['role'] === 'admin') {
    $patients = $pdo->query("SELECT p.id, CONCAT(u.first_name, ' ', u.last_name) AS name FROM patients p JOIN users u ON p.user_id = u.id")->fetchAll();
    $doctors = $pdo->query("SELECT d.id, CONCAT(u.first_name, ' ', u.last_name) AS name FROM doctors d JOIN users u ON d.user_id = u.id")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill Summary - Klinik Penawar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: 'Poppins', sans-serif;
            background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-pastel-sky-background_23-2148902771.jpg');
            background-size: cover;
            background-attachment: fixed;
        }
        .container {
            margin-top: 40px;
        }
        .card-header {
            background-color: #2c786c;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-file-invoice-dollar me-2"></i>Bill Summary</span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light">Back to Dashboard</a>
        </div>
        <div class="card-body">
            <?php if (count($bills) > 0): ?>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <th>Patient</th>
                            <?php endif; ?>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Total (RM)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td><?= htmlspecialchars($bill['patient_name']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($bill['doctor_name']) ?></td>
                                <td><?= $bill['formatted_date'] ?></td>
                                <td><?= nl2br(htmlspecialchars($bill['description'])) ?></td>
                                <td><strong>RM <?= number_format($bill['total'], 2) ?></strong></td>
                                <td><span class="badge bg-<?= $bill['status'] === 'Paid' ? 'success' : 'warning' ?>"><?= htmlspecialchars($bill['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">No bills found.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="card shadow">
        <div class="card-header">
            <i class="fas fa-plus-circle me-2"></i>Generate New Bill
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="patient_id" class="form-label">Select Patient</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="doctor_id" class="form-label">Select Doctor</label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="total" class="form-label">Total (RM)</label>
                    <input type="number" step="0.01" name="total" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Payment Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Unpaid" selected>Unpaid</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Generate Bill</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
