<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../index.php');
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill_id']) && isset($_FILES['payment_pdf'])) {
    $bill_id = $_POST['bill_id'];
    $file = $_FILES['payment_pdf'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'pdf') {
            $uploadDir = '../uploads/payments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Create directory if not exists
            }

            $filename = 'payment_' . $bill_id . '_' . time() . '.pdf';
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE bills SET status = 'Paid' WHERE id = ? AND patient_id = (SELECT id FROM patients WHERE user_id = ?)");
                $stmt->execute([$bill_id, $_SESSION['user_id']]);
            } else {
                echo "<div class='alert alert-danger'>Failed to upload the file.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Only PDF files are allowed.</div>";
        }
    }
}

// Get bills for this patient
$stmt = $pdo->prepare("SELECT b.id, b.description, b.total, b.status, DATE_FORMAT(b.created_at, '%M %d, %Y %h:%i %p') AS formatted_date, CONCAT(u.first_name, ' ', u.last_name) AS doctor_name FROM bills b JOIN doctors d ON b.doctor_id = d.id JOIN users u ON d.user_id = u.id WHERE b.patient_id = (SELECT id FROM patients WHERE user_id = ?) ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bills = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bills - Klinik Penawar</title>
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
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-file-invoice-dollar me-2"></i>Your Bills</span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light">Back to Dashboard</a>
        </div>
        <div class="card-body">
            <?php if (count($bills) > 0): ?>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Total (RM)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?= htmlspecialchars($bill['doctor_name']) ?></td>
                                <td><?= $bill['formatted_date'] ?></td>
                                <td><?= nl2br(htmlspecialchars($bill['description'])) ?></td>
                                <td><strong>RM <?= number_format($bill['total'], 2) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= $bill['status'] === 'Paid' ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars($bill['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($bill['status'] !== 'Paid'): ?>
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                                            <div class="mb-2">
                                                <input type="file" name="payment_pdf" accept="application/pdf" required class="form-control form-control-sm">
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Upload Receipt</button>
                                        </form>
                                    <?php else: ?>
                                        <em>N/A</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">No bills found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
