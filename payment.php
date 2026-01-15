<?php
/* ============================
   SESSION + DB (FIRST)
============================ */
session_start();
require 'db.php';

/* ============================
   LOGIN CHECK
============================ */
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];
$booking_id  = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$msg = "";
$errors = [];

/* ============================
   FETCH BOOKING
============================ */
$sql = "SELECT b.*, p.name AS package_name, p.price AS package_price, p.location
        FROM bookings b
        JOIN packages p ON b.package_id = p.id
        WHERE b.id = $booking_id AND b.customer_id = $customer_id";

$res = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($res);

if (!$booking) {
    die("Invalid booking.");
}

/* ============================
   HANDLE POST
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_mode = $_POST['payment_mode'] ?? 'MANUAL';

    /* ---- ONLINE PAYMENT ---- */
    if ($payment_mode === 'AUTO') {
        header("Location: pay_online.php?booking_id=" . $booking_id);
        exit;
    }

    /* ---- MANUAL PAYMENT ---- */
    $upi_ref = trim($_POST['upi_ref'] ?? '');

    if ($upi_ref === '') {
        $errors[] = "Please enter UPI reference ID.";
    }

    if (empty($errors)) {
        $amount = (float)$booking['total_amount'];
        $txn    = mysqli_real_escape_string($conn, $upi_ref);

        mysqli_query($conn, "
            INSERT INTO payments (booking_id, amount, method, status, transaction_id)
            VALUES ($booking_id, $amount, 'UPI', 'Pending', '$txn')
        ");

        $msg = "Payment submitted successfully. Admin will verify and approve.";
    }
}

/* ============================
   FETCH QR SETTINGS
============================ */
$settings = null;
$setRes = mysqli_query($conn, "SELECT * FROM payment_settings LIMIT 1");
if ($setRes && mysqli_num_rows($setRes) > 0) {
    $settings = mysqli_fetch_assoc($setRes);
}

/* ============================
   HTML STARTS
============================ */
include 'header.php';
?>

<div class="container mt-4">
<div class="card">
<div class="card-body">

<h3>Payment</h3>

<ul>
    <li><b>Package:</b> <?= htmlspecialchars($booking['package_name']) ?></li>
    <li><b>Location:</b> <?= htmlspecialchars($booking['location']) ?></li>
    <li><b>Amount:</b> ₹<?= htmlspecialchars($booking['total_amount']) ?></li>
</ul>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <a href="booking_history.php" class="btn btn-primary">My Bookings</a>

<?php else: ?>

<form method="post">

<div class="mb-3">
    <label><b>Select Payment Method</b></label><br>

    <input type="radio" id="pay_manual" name="payment_mode" value="MANUAL" checked>
    <label for="pay_manual">Manual UPI (QR)</label><br>

    <input type="radio" id="pay_auto" name="payment_mode" value="AUTO">
    <label for="pay_auto">Online Payment (UPI / Card)</label>
</div>

<hr>

<!-- MANUAL PAYMENT SECTION -->
<div id="manualPaymentSection">
    <h5>Manual UPI Payment</h5>

    <?php if ($settings && $settings['qr_image']): ?>
        <img src="<?= htmlspecialchars($settings['qr_image']) ?>"
             class="img-fluid mb-2" width="200">
    <?php endif; ?>

    <input type="text" name="upi_ref" class="form-control mb-3"
           placeholder="Enter UPI Reference ID">
</div>

<!-- BUTTONS -->
<button type="submit" id="manualBtn" class="btn btn-success w-100">
    Submit Manual Payment
</button>

<button type="submit" id="onlineBtn" class="btn btn-primary w-100" style="display:none;">
    Pay Online
</button>

</form>

<?php endif; ?>

</div>
</div>
</div>

<!-- TOGGLE SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const manualRadio = document.getElementById("pay_manual");
    const autoRadio   = document.getElementById("pay_auto");
    const manualBox   = document.getElementById("manualPaymentSection");
    const manualBtn   = document.getElementById("manualBtn");
    const onlineBtn   = document.getElementById("onlineBtn");

    function toggleUI() {
        if (autoRadio.checked) {
            manualBox.style.display = "none";
            manualBtn.style.display = "none";
            onlineBtn.style.display = "block";
        } else {
            manualBox.style.display = "block";
            manualBtn.style.display = "block";
            onlineBtn.style.display = "none";
        }
    }

    manualRadio.addEventListener("change", toggleUI);
    autoRadio.addEventListener("change", toggleUI);

    toggleUI(); // initial load
});
</script>

<?php include 'footer.php'; ?>
