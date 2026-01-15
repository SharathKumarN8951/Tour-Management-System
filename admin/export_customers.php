<?php
// admin/export_customers.php
require '../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customers_export.csv');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['Customer ID', 'Name', 'Email', 'Phone', 'City', 'State', 'Country', 'Pincode', 'Created At']);

// Data rows
$sql = "SELECT * FROM customers ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $city = $row['city'] ?? '';
    $state = $row['state'] ?? '';
    $country = $row['country'] ?? '';
    $pincode = $row['pincode'] ?? '';
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $city,
        $state,
        $country,
        $pincode,
        $row['created_at']
    ]);
}
fclose($output);
exit;
