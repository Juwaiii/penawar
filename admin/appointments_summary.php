<?php
session_start();
include '../db.php';

// Restrict to admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle reassignment form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['doctor_id'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET doctor_id = ?, status = 'Pending', cancelled_by = NULL WHERE id = ?");
    $stmt->execute([$_POST['doctor_id'], $_POST['appointment_id']]);
    header('Location: appointments_summary.php');
    exit();
}

// Fetch appointments
$stmt = $pdo->prepare("
    SELECT a.*, 
           u1.first_name AS patient_first, u1.last_name AS patient_last,
           u2.first_name AS doctor_first, u2.last_name AS doctor_last,
           d.specialization AS cancelled_doctor_specialization
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u1 ON p.user_id = u1.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u2 ON d.user_id = u2.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Summary - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f0f8ff; font-family: 'Poppins', sans-serif; }
        .container { margin-top: 30px; }
        .card { border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .card-header { background-color: #2c786c; color: white; font-weight: bold; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

    <div class="card">
        <div class="card-header"><i class="fas fa-calendar-check me-2"></i> All Appointments</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($appointments): ?>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['patient_first'] . ' ' . $a['patient_last']) ?></td>
                                <td>Dr. <?= htmlspecialchars($a['doctor_first'] . ' ' . $a['doctor_last']) ?></td>
                                <td><?= date('F j, Y', strtotime($a['appointment_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($a['reason']) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($a['status']) {
                                        'Confirmed' => 'success',
                                        'Pending' => 'warning',
                                        'Cancelled' => 'danger',
                                        default => 'secondary'
                                    } ?>">
                                        <?= $a['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($a['status'] === 'Cancelled' && $a['cancelled_by'] === 'doctor'): ?>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#reassignModal<?= $a['id'] ?>">
                                            Reassign
                                        </button>

                                        <!-- Reassign Modal -->
                                        <div class="modal fade" id="reassignModal<?= $a['id'] ?>" tabindex="-1"
                                             aria-labelledby="reassignModalLabel<?= $a['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form class="modal-content" method="POST">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Reassign Doctor</h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                                                        <p><strong>Date:</strong> <?= $a['appointment_date'] ?> |
                                                           <strong>Time:</strong> <?= date('g:i A', strtotime($a['appointment_time'])) ?></p>

                                                        <div class="mb-3">
                                                            <label class="form-label">Available Doctors</label>
                                                            <select name="doctor_id" class="form-select" required>
                                                                <option value="">-- Select Doctor --</option>
                                                                <?php
                                                                $availableStmt = $pdo->prepare("
                                                                    SELECT d.id, u.first_name, u.last_name
                                                                    FROM doctors d
                                                                    JOIN users u ON d.user_id = u.id
                                                                    WHERE d.id NOT IN (
                                                                        SELECT doctor_id FROM appointments 
                                                                        WHERE appointment_date = ? 
                                                                          AND appointment_time = ?
                                                                          AND status IN ('Confirmed', 'Pending')
                                                                    )
                                                                    AND d.id != ?
                                                                    AND d.specialization = ?
                                                                ");
                                                                $availableStmt->execute([
                                                                    $a['appointment_date'],
                                                                    $a['appointment_time'],
                                                                    $a['doctor_id'],
                                                                    $a['cancelled_doctor_specialization']
                                                                ]);
                                                                $availableDoctors = $availableStmt->fetchAll();

                                                                foreach ($availableDoctors as $doc):
                                                                ?>
                                                                    <option value="<?= $doc['id'] ?>">
                                                                        Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Reassign</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No appointments found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
