<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'db.php';
session_start();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);

        $resetLink = "http://localhost/penawar/reset-password.php?token=$token";

        $mail = new PHPMailer(true);
        try {
            // ✅ Gmail SMTP settings using your Gmail and App Password
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'juwaimuhdsin@gmail.com';
            $mail->Password = 'jfefsjqdrnsnsukw'; // App Password without spaces
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('juwaimuhdsin@gmail.com', 'Klinik Penawar');
            $mail->addAddress($email, $user['first_name']);

            $mail->isHTML(true);
            $mail->Subject = 'Klinik Penawar Password Reset';
            $mail->Body = "Hi <b>{$user['first_name']}</b>,<br><br>
                Click the link below to reset your password:<br>
                <a href='$resetLink'>$resetLink</a><br><br>
                This link will expire in 1 hour.<br><br>
                Regards,<br>Klinik Penawar System";

            $mail->send();
            $success = "Reset link sent successfully to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $error = "Email not found in our system.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Klinik Penawar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: 'Poppins', sans-serif;
        }
        .forgot-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #2c786c;
            border-color: #2c786c;
        }
        .btn-primary:hover {
            background-color: #004445;
            border-color: #004445;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="forgot-container">
        <h3 class="text-center mb-4">Forgot Password</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Enter your email</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            <div class="text-center mt-3">
                <a href="index.php">Back to Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
