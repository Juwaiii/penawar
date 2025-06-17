<?php
require_once '../vendor/autoload.php';
require_once '../db.php';

use Dompdf\Dompdf;

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch inventory items
$stmt = $pdo->query("SELECT * FROM inventory ORDER BY id DESC");
$items = $stmt->fetchAll();

// HTML for the PDF
$html = '
    <h2 style="text-align:center;">Inventory List</h2>
    <table border="1" cellspacing="0" cellpadding="6" width="100%">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th>ID</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>';
foreach ($items as $item) {
    $html .= "<tr>
                <td>{$item['id']}</td>
                <td>" . htmlspecialchars($item['item_name']) . "</td>
                <td>{$item['quantity']}</td>
                <td>" . htmlspecialchars($item['unit']) . "</td>
              </tr>";
}
$html .= '</tbody></table>';

// Generate and stream PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("inventory_list.pdf", ["Attachment" => false]); // open in browser
exit();
