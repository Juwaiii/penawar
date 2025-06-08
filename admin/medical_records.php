<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch all medical records with joined doctor and patient details
$stmt = $pdo->prepare("
    SELECT 
        mr.*, 
        d.specialization,
        doc_user.first_name AS doctor_first, doc_user.last_name AS doctor_last,
        pat_user.first_name AS patient_first, pat_user.last_name AS patient_last
    FROM medical_records mr
    JOIN doctors d ON mr.doctor_id = d.id
    JOIN users doc_user ON d.user_id = doc_user.id
    JOIN patients p ON mr.patient_id = p.id
    JOIN users pat_user ON p.user_id = pat_user.id
    ORDER BY mr.record_date DESC
");
$stmt->execute();
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Medical Records - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c786c;
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
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
<div class="container dashboard-container">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-notes-medical me-2"></i>All Medical Records</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
        <div class="card-body">
            <?php if (count($records) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Specialization</th>
                                <th>Diagnosis</th>
                                <th>Treatment</th>
                                <th>Notes</th>
                                <th>Record Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $rec): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rec['patient_first'] . ' ' . $rec['patient_last']) ?></td>
                                    <td><?= htmlspecialchars($rec['doctor_first'] . ' ' . $rec['doctor_last']) ?></td>
                                    <td><?= htmlspecialchars($rec['specialization']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($rec['diagnosis'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($rec['treatment'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($rec['notes'])) ?></td>
                                    <td><?= date('F j, Y', strtotime($rec['record_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No medical records available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
