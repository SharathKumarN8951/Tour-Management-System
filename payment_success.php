<?php
session_start();
require 'db.php';

if (!isset($_SESSION['customer_id'])) {
    die("Login required");
}

$booking_id = (int)($_GET['booking_id'] ?? 0);
$payment_id = $_GET['payment_id'] ?? '';

if ($booking_id <= 0 || $payment_id == '') {
    die("Invalid payment data");
}

/* UPDATE PAYMENT */
mysqli_query($conn, "
    UPDATE payments
    SET status='Success', transaction_id='$payment_id'
    WHERE booking_id=$booking_id AND method='RAZORPAY'
");

/* UPDATE BOOKING */
mysqli_query($conn, "
    UPDATE bookings
    SET status='Paid'
    WHERE id=$booking_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
</head>
<body style="text-align:center; margin-top:50px;">
    <h2>✅ Payment Successful</h2>
    <p>Your booking is confirmed.</p>
    <a href="booking_history.php">Go to My Bookings</a>
</body>
</html>
