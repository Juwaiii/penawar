<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pdo = require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// ✅ Get doctor_id from doctors table
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$user_id]);
$doctor_row = $stmt->fetch();
$doctor_id = $doctor_row ? $doctor_row['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_record']) && $doctor_id) {
    $appointment_id = !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null;
    $patient_id = !empty($_POST['patient_id']) ? intval($_POST['patient_id']) : null;

    // Auto-fetch patient_id if not provided and appointment_id is given
    if (!$patient_id && $appointment_id) {
        $stmt = $pdo->prepare("SELECT patient_id FROM appointments WHERE id = ? AND doctor_id = ?");
        $stmt->execute([$appointment_id, $doctor_id]);
        $patient = $stmt->fetch();
        if ($patient) {
            $patient_id = $patient['patient_id'];
        }
    }

    if ($patient_id) {
        $diagnosis = $_POST['diagnosis'];
        $treatment = $_POST['treatment'];
        $notes = $_POST['notes'];

        $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, treatment, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $appointment_id, $diagnosis, $treatment, $notes]);
        $message = "Record added successfully!";
    } else {
        $message = "Error: Patient ID is required (either selected or resolved from appointment).";
    }
}

$stmt = $pdo->prepare("SELECT mr.*, CONCAT(u.first_name, ' ', u.last_name) AS patient_name FROM medical_records mr JOIN patients p ON mr.patient_id = p.id JOIN users u ON p.user_id = u.id WHERE mr.doctor_id = ? ORDER BY mr.record_date DESC");
$stmt->execute([$doctor_id]);
$records_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch patients for optional dropdown
$patients_stmt = $pdo->query("SELECT p.id, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.id ORDER BY u.first_name");
$patients = $patients_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Records - Klinik Penawar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .welcome-box, .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }
        textarea.form-control {
            height: 100px;
        }
    </style>
</head>
<body>
<div class="container dashboard-container">
    <div class="welcome-box p-4 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="fas fa-notes-medical me-2"></i>Medical Records</h3>
            <p class="text-muted">View and update patient medical records</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-medical me-2"></i>Patient Records
            <button class="btn btn-sm btn-light float-end" data-bs-toggle="modal" data-bs-target="#createModal">➕ Insert</button>
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (count($records_list) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Diagnosis</th>
                                <th>Treatment</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records_list as $record): ?>
                                <tr>
                                    <td><?php echo $record['id']; ?></td>
                                    <td><?php echo $record['patient_name']; ?></td>
                                    <td><?php echo $record['record_date']; ?></td>
                                    <td><textarea class="form-control" readonly><?php echo htmlspecialchars($record['diagnosis']); ?></textarea></td>
                                    <td><textarea class="form-control" readonly><?php echo htmlspecialchars($record['treatment']); ?></textarea></td>
                                    <td><textarea class="form-control" readonly><?php echo htmlspecialchars($record['notes']); ?></textarea></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <i class="fas fa-file-medical fa-2x mb-2 text-muted"></i>
                    <p class="text-muted">No medical records found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">New Medical Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Appointment ID (optional)</label>
                        <input type="number" name="appointment_id" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Patient (optional)</label>
                        <select name="patient_id" class="form-select">
                            <option value="" selected disabled>-- Optional: select manually --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Treatment</label>
                        <textarea name="treatment" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="create_record" class="btn btn-primary">Save Record</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
