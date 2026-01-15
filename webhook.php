<?php
require 'db.php';

$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

if ($data['event'] === 'payment.captured') {

    $payment_id = $data['payload']['payment']['entity']['id'];
    $amount = $data['payload']['payment']['entity']['amount'] / 100;

    // Insert payment
    mysqli_query($conn, "
        INSERT INTO payments (booking_id, amount, method, status, transaction_id)
        VALUES (0, $amount, 'RAZORPAY', 'Success', '$payment_id')
    ");
}
