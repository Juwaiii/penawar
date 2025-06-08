<?php 
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../index.php');
    exit();
}

// Get patient info
$stmt = $pdo->prepare("SELECT p.* FROM profile p JOIN users u ON p.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $fullName = htmlspecialchars(trim($_POST['fullName']));
    $icNumber = htmlspecialchars(trim($_POST['icNumber']));
    $dateOfBirth = $_POST['dateOfBirth'];
    $gender = $_POST['gender'];
    $bloodType = $_POST['bloodType'];
    $maritalStatus = $_POST['maritalStatus'];
    $phoneNumber = htmlspecialchars(trim($_POST['phoneNumber']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $state = htmlspecialchars(trim($_POST['state']));
    $postalCode = htmlspecialchars(trim($_POST['postalCode']));
    $allergies = htmlspecialchars(trim($_POST['allergies']));
    $currentMedications = htmlspecialchars(trim($_POST['currentMedications']));
    $medicalHistory = htmlspecialchars(trim($_POST['medicalHistory']));
    $familyHistory = htmlspecialchars(trim($_POST['familyHistory']));
    $emergencyName = htmlspecialchars(trim($_POST['emergencyName']));
    $emergencyRelationship = htmlspecialchars(trim($_POST['emergencyRelationship']));
    $emergencyPhone = htmlspecialchars(trim($_POST['emergencyPhone']));
    $emergencyEmail = filter_var(trim($_POST['emergencyEmail']), FILTER_SANITIZE_EMAIL);

    // Update patient record
    $stmt = $pdo->prepare("UPDATE profile SET 
        full_name = ?, 
        ic_number = ?, 
        date_of_birth = ?, 
        gender = ?, 
        blood_type = ?, 
        marital_status = ?, 
        phone_number = ?, 
        email = ?, 
        address = ?, 
        city = ?, 
        state = ?, 
        postal_code = ?, 
        allergies = ?, 
        current_medications = ?, 
        medical_history = ?, 
        family_history = ?, 
        emergency_contact_name = ?, 
        emergency_contact_relationship = ?, 
        emergency_contact_phone = ?, 
        emergency_contact_email = ?
        WHERE id = ?");
    
    $success = $stmt->execute([
        $fullName, $icNumber, $dateOfBirth, $gender, $bloodType, $maritalStatus,
        $phoneNumber, $email, $address, $city, $state, $postalCode,
        $allergies, $currentMedications, $medicalHistory, $familyHistory,
        $emergencyName, $emergencyRelationship, $emergencyPhone, $emergencyEmail,
        $patient['id']
    ]);

    if ($success) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile - Klinik Penawar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c786c;
            --secondary-color: #6b8e23;
            --accent-color: #ff7e5f;
            --light-bg: #f0f8ff;
        }

        body {
            background-color: var(--light-bg);
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

        .profile-header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .profile-card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }

        .profile-card-body {
            padding: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .required-field::after {
            content: " *";
            color: var(--accent-color);
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(44, 120, 108, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #235347;
            border-color: #235347;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        .alert {
            border-radius: 10px;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <!-- Header Section -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-user-edit me-2"></i> Update Your Profile</h2>
                <p class="lead text-muted">Keep your information up to date for better healthcare services.</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="dashboard.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
                <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Profile Update Form -->
    <form method="POST" action="profile.php">
        <!-- Personal Information Section -->
        <div class="profile-card mb-4">
            <div class="profile-card-header">
                <i class="fas fa-user me-2"></i> Personal Information
            </div>
            <div class="profile-card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fullName" class="form-label required-field">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" 
                               value="<?= htmlspecialchars($patient['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="icNumber" class="form-label required-field">IC/Passport Number</label>
                        <input type="text" class="form-control" id="icNumber" name="icNumber" 
                               value="<?= htmlspecialchars($patient['ic_number'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dateOfBirth" class="form-label required-field">Date of Birth</label>
                        <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" 
                               value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="gender" class="form-label required-field">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($patient['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($patient['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($patient['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="bloodType" class="form-label">Blood Type</label>
                        <select class="form-select" id="bloodType" name="bloodType">
                            <option value="">Select Blood Type</option>
                            <option value="A+" <?= ($patient['blood_type'] ?? '') == 'A+' ? 'selected' : '' ?>>A+</option>
                            <option value="A-" <?= ($patient['blood_type'] ?? '') == 'A-' ? 'selected' : '' ?>>A-</option>
                            <option value="B+" <?= ($patient['blood_type'] ?? '') == 'B+' ? 'selected' : '' ?>>B+</option>
                            <option value="B-" <?= ($patient['blood_type'] ?? '') == 'B-' ? 'selected' : '' ?>>B-</option>
                            <option value="AB+" <?= ($patient['blood_type'] ?? '') == 'AB+' ? 'selected' : '' ?>>AB+</option>
                            <option value="AB-" <?= ($patient['blood_type'] ?? '') == 'AB-' ? 'selected' : '' ?>>AB-</option>
                            <option value="O+" <?= ($patient['blood_type'] ?? '') == 'O+' ? 'selected' : '' ?>>O+</option>
                            <option value="O-" <?= ($patient['blood_type'] ?? '') == 'O-' ? 'selected' : '' ?>>O-</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="maritalStatus" class="form-label">Marital Status</label>
                        <select class="form-select" id="maritalStatus" name="maritalStatus">
                            <option value="">Select Marital Status</option>
                            <option value="single" <?= ($patient['marital_status'] ?? '') == 'single' ? 'selected' : '' ?>>Single</option>
                            <option value="married" <?= ($patient['marital_status'] ?? '') == 'married' ? 'selected' : '' ?>>Married</option>
                            <option value="divorced" <?= ($patient['marital_status'] ?? '') == 'divorced' ? 'selected' : '' ?>>Divorced</option>
                            <option value="widowed" <?= ($patient['marital_status'] ?? '') == 'widowed' ? 'selected' : '' ?>>Widowed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="profile-card mb-4">
            <div class="profile-card-header">
                <i class="fas fa-address-book me-2"></i> Contact Information
            </div>
            <div class="profile-card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phoneNumber" class="form-label required-field">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" 
                               value="<?= htmlspecialchars($patient['phone_number'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($patient['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" 
                               value="<?= htmlspecialchars($patient['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state" 
                               value="<?= htmlspecialchars($patient['state'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="postalCode" class="form-label">Postal Code</label>
                        <input type="text" class="form-control" id="postalCode" name="postalCode" 
                               value="<?= htmlspecialchars($patient['postal_code'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information Section -->
        <div class="profile-card mb-4">
            <div class="profile-card-header">
                <i class="fas fa-heartbeat me-2"></i> Medical Information
            </div>
            <div class="profile-card-body">
                <div class="mb-3">
                    <label for="allergies" class="form-label">Known Allergies</label>
                    <textarea class="form-control" id="allergies" name="allergies" rows="2"><?= htmlspecialchars($patient['allergies'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="currentMedications" class="form-label">Current Medications</label>
                    <textarea class="form-control" id="currentMedications" name="currentMedications" rows="2"><?= htmlspecialchars($patient['current_medications'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="medicalHistory" class="form-label">Medical History</label>
                    <textarea class="form-control" id="medicalHistory" name="medicalHistory" rows="3"><?= htmlspecialchars($patient['medical_history'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="familyHistory" class="form-label">Family Medical History</label>
                    <textarea class="form-control" id="familyHistory" name="familyHistory" rows="2"><?= htmlspecialchars($patient['family_history'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Emergency Contact Section -->
        <div class="profile-card mb-4">
            <div class="profile-card-header">
                <i class="fas fa-exclamation-triangle me-2"></i> Emergency Contact
            </div>
            <div class="profile-card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="emergencyName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="emergencyName" name="emergencyName" 
                               value="<?= htmlspecialchars($patient['emergency_contact_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="emergencyRelationship" class="form-label">Relationship</label>
                        <input type="text" class="form-control" id="emergencyRelationship" name="emergencyRelationship" 
                               value="<?= htmlspecialchars($patient['emergency_contact_relationship'] ?? '') ?>" placeholder="Spouse, parent, etc.">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="emergencyPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="emergencyPhone" name="emergencyPhone" 
                               value="<?= htmlspecialchars($patient['emergency_contact_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="emergencyEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="emergencyEmail" name="emergencyEmail" 
                               value="<?= htmlspecialchars($patient['emergency_contact_email'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-end gap-3 mb-5">
            <button type="reset" class="btn btn-secondary"><i class="fas fa-undo me-2"></i> Reset</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Changes</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = [
            { id: 'fullName', name: 'Full Name' },
            { id: 'icNumber', name: 'IC/Passport Number' },
            { id: 'dateOfBirth', name: 'Date of Birth' },
            { id: 'gender', name: 'Gender' },
            { id: 'phoneNumber', name: 'Phone Number' }
        ];
        
        let isValid = true;
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element.value.trim()) {
                isValid = false;
                element.classList.add('is-invalid');
                // Add error message
                if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = `Please provide your ${field.name}`;
                    element.parentNode.appendChild(errorDiv);
                }
            } else {
                element.classList.remove('is-invalid');
                const errorDiv = element.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                    errorDiv.remove();
                }
            }
        });
        
        // Validate IC number format (Malaysian IC example)
        const icNumber = document.getElementById('icNumber').value;
        if (icNumber && !/^\d{12}$/.test(icNumber)) {
            isValid = false;
            const element = document.getElementById('icNumber');
            element.classList.add('is-invalid');
            if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Please enter a valid 12-digit IC number';
                element.parentNode.appendChild(errorDiv);
            }
        }
        
        // Validate phone number format
        const phoneNumber = document.getElementById('phoneNumber').value;
        if (phoneNumber && !/^[0-9]{10,15}$/.test(phoneNumber)) {
            isValid = false;
            const element = document.getElementById('phoneNumber');
            element.classList.add('is-invalid');
            if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Please enter a valid phone number (10-15 digits)';
                element.parentNode.appendChild(errorDiv);
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
</body>
</html>