<?php
// admin/export_bookings.php
require '../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_export.csv');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['Booking ID', 'Customer Name', 'Customer Email', 'Package', 'Location', 'Seats', 'Total Amount', 'Status', 'Booking Date', 'Created At']);

// Data rows
$sql = "SELECT b.*, c.name AS customer_name, c.email AS customer_email,
               p.name AS package_name, p.location AS package_location
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN packages p ON b.package_id = p.id
        ORDER BY b.created_at DESC";
$res = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    fputcsv($output, [
        $row['id'],
        $row['customer_name'],
        $row['customer_email'],
        $row['package_name'],
        $row['package_location'],
        $row['seats'],
        $row['total_amount'],
        $row['status'],
        $row['booking_date'],
        $row['created_at']
    ]);
}
fclose($output);
exit;
