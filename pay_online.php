<?php
session_start();
require 'db.php';

if (!isset($_SESSION['customer_id'])) {
    die("Please login again");
}

$customer_id = (int)$_SESSION['customer_id'];
$booking_id  = (int)($_GET['booking_id'] ?? 0);

/* --------------------------
   FETCH BOOKING
--------------------------- */
$res = mysqli_query($conn, "
    SELECT * FROM bookings 
    WHERE id = $booking_id AND customer_id = $customer_id
");
$booking = mysqli_fetch_assoc($res);

if (!$booking) {
    die("Invalid booking");
}

$amount = (int)($booking['total_amount'] * 100); // paise

/* --------------------------
   RAZORPAY TEST KEYS
--------------------------- */
$key_id     = "";    ### { add your api key}
$key_secret = "";

/* --------------------------
   CREATE ORDER
--------------------------- */
$data = [
    "amount"   => $amount,
    "currency" => "INR",
    "receipt"  => "BOOKING_" . $booking_id
];

$ch = curl_init("https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);

if ($response === false) {
    die("cURL Error: " . curl_error($ch));
}

$order = json_decode($response, true);

if (!isset($order['id'])) {
    echo "<pre>";
    print_r($order);
    exit;
}

$order_id = $order['id'];

/* --------------------------
   SAVE PAYMENT (PENDING)
--------------------------- */
mysqli_query($conn, "
    INSERT INTO payments (booking_id, amount, method, status, transaction_id)
    VALUES ($booking_id, {$booking['total_amount']}, 'RAZORPAY', 'Pending', '$order_id')
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Online Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>

<script>
var options = {
    "key": "<?= $key_id ?>",
    "amount": "<?= $amount ?>",
    "currency": "INR",
    "name": "Tour Management",
    "description": "Booking Payment",
    "order_id": "<?= $order_id ?>",

    "handler": function (response) {
        // ✅ REDIRECT AFTER SUCCESS
        window.location.href =
            "payment_success.php?booking_id=<?= $booking_id ?>" +
            "&payment_id=" + response.razorpay_payment_id;
    },

    "modal": {
        "ondismiss": function () {
            alert("Payment cancelled");
        }
    }
};

new Razorpay(options).open();
</script>

</body>
</html>

