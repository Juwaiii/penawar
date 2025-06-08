<?php 
session_start();
include '../db.php';

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../index.php');
    exit();
}

// Get patient details
$stmt = $pdo->prepare("SELECT p.* FROM patients p JOIN users u ON p.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

// Get all doctors
$stmt = $pdo->query("SELECT d.id, u.first_name, u.last_name, d.specialization FROM doctors d JOIN users u ON d.user_id = u.id");
$doctors = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];

    // Check if the selected time slot is available
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                          WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $error = "The selected time slot is already booked. Please choose another time.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) 
                              VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$patient['id'], $doctor_id, $appointment_date, $appointment_time, $reason]);
        $_SESSION['success_message'] = "Appointment booked successfully! It will be confirmed by the doctor.";
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment - Klinik Penawar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('https://img.freepik.com/free-vector/hand-painted-watercolor-pastel-sky-background_23-2148902771.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            max-width: 600px;
            margin-top: 50px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h2 {
            color: #2c786c;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #2c786c;
            border: none;
        }

        .btn-primary:hover {
            background-color: #235347;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .form-label {
            font-weight: 500;
        }

        .form-select, .form-control {
            border-radius: 10px;
        }

        .alert {
            border-radius: 10px;
        }

        .form-section-title {
            margin-bottom: 15px;
            font-size: 1.25rem;
            font-weight: 600;
            color: #343a40;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2><i class="fas fa-calendar-plus me-2"></i>Book Appointment</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="doctor_id" class="form-label">Select Doctor</label>
                <select class="form-select" id="doctor_id" name="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?= $doctor['id']; ?>">
                            Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> - <?= htmlspecialchars($doctor['specialization']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="appointment_date" class="form-label">Appointment Date</label>
                <input type="date" class="form-control" id="appointment_date" name="appointment_date" min="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="mb-3">
                <label for="appointment_time" class="form-label">Appointment Time</label>
                <input type="time" class="form-control" id="appointment_time" name="appointment_time" min="09:00" max="17:00" required>
                <small class="text-muted">Clinic hours: 9:00 AM to 5:00 PM</small>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason for Appointment</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Book Now</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Set minimum date for appointment (today)
    document.getElementById('appointment_date').min = new Date().toISOString().split('T')[0];
</script>
</body>
</html>
