<?php 
include '../db.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header('Location: ../index.php');
    exit();
}

// Get doctor details
$stmt = $pdo->prepare("SELECT d.* FROM doctors d JOIN users u ON d.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();

// Handle appointment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $appointment_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Verify the appointment belongs to this doctor
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$appointment_id, $doctor['id']]);
    $appointment = $stmt->fetch();
    
    if ($appointment) {
        switch ($action) {
            case 'confirm':
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'Confirmed' WHERE id = ?");
                $stmt->execute([$appointment_id]);
                $_SESSION['success_message'] = "Appointment confirmed successfully.";
                break;
            case 'cancel':
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
                $stmt->execute([$appointment_id]);
                $_SESSION['success_message'] = "Appointment cancelled successfully.";
                break;
            case 'complete':
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
                $stmt->execute([$appointment_id]);
                $_SESSION['success_message'] = "Appointment marked as completed.";
                break;
        }
    }
    
    header('Location: dashboard.php');
    exit();
}

// Get all appointments
$stmt = $pdo->prepare("SELECT a.*, p.first_name, p.last_name 
                      FROM appointments a 
                      JOIN patients p ON a.patient_id = p.id 
                      WHERE a.doctor_id = ?
                      ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->execute([$doctor['id']]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/doctor_navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>All Appointments</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo $appointment['first_name'] . ' ' . $appointment['last_name']; ?></td>
                            <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                            <td><?php echo $appointment['reason']; ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        switch ($appointment['status']) {
                                            case 'Pending': echo 'bg-warning'; break;
                                            case 'Confirmed': echo 'bg-success'; break;
                                            case 'Cancelled': echo 'bg-danger'; break;
                                            case 'Completed': echo 'bg-info'; break;
                                        }
                                    ?>">
                                    <?php echo $appointment['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($appointment['status'] == 'Pending'): ?>
                                    <a href="?action=confirm&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-success">Confirm</a>
                                    <a href="?action=cancel&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                                <?php elseif ($appointment['status'] == 'Confirmed'): ?>
                                    <a href="?action=complete&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">Complete</a>
                                    <a href="prescribe.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info">Prescribe</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>