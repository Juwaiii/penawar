<?php
$host = 'localhost';
$dbname = 'doctor_appointment_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo; // ✅ this line is critical
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
