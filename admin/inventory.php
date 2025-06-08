<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'] ?? '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $unit = $_POST['unit'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO inventory (item_name, quantity, unit) VALUES (?, ?, ?)");
    $stmt->execute([$item_name, $quantity, $unit]);
    header("Location: inventory.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: inventory.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM inventory ORDER BY id DESC");
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c786c;
            --secondary-color: #6b8e23;
            --accent-color: #ff7e5f;
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
            border-radius: 15px;
            border: none;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
            border-bottom: none;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-success {
            background-color: var(--secondary-color);
            border: none;
        }
    </style>
</head>
<body>
<div class="container dashboard-container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-boxes me-2"></i>Inventory Management</span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light">Back to Dashboard</a>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3 mb-4">
                <div class="col-md-5">
                    <input type="text" name="item_name" class="form-control" placeholder="Item name" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="unit" class="form-control" placeholder="Unit (e.g. box, pcs)" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_item" class="btn btn-success w-100">Add Item</button>
                </div>
            </form>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td>
                                <a href="edit_inventory.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="inventory.php?delete=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>