<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- LOGIN CHECK ----
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];
$errors = [];

// ---- DATE LIMITS: TODAY .. NEXT 5 DAYS ----
$today   = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+5 days'));
$travel_value = $today;

// ---- LOAD PACKAGE ----
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($package_id <= 0) {
    header("Location: packages.php");
    exit;
}

$res = mysqli_query($conn, "SELECT * FROM packages WHERE id = $package_id LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) {
    header("Location: packages.php");
    exit;
}
$package = mysqli_fetch_assoc($res);

$name          = $package['name'];
$location      = $package['location'];
$description   = $package['description'];
$price         = (float)$package['price'];
$days          = $package['days'];
$image         = $package['image'] ?? "";
$from_location = $package['from_location'] ?? "";
$to_location   = $package['to_location'] ?? "";
$distance_km   = $package['distance_km'] ?? null;

// ---- BOOKING SUBMIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $seats = isset($_POST['seats']) ? (int)$_POST['seats'] : 0;
    $travel_date = trim($_POST['travel_date'] ?? '');
    $travel_value = $travel_date ?: $today;

    // ✅ SEAT LIMIT VALIDATION (IMPORTANT)
    if ($seats < 1 || $seats > 5) {
        $errors[] = "You can book minimum 1 and maximum 5 seats only.";
    }

    // ✅ DATE VALIDATION
    if ($travel_date === '') {
        $errors[] = "Please select a travel date.";
    } else {
        if ($travel_date < $today || $travel_date > $maxDate) {
            $errors[] = "Booking allowed only from today to the next 5 days.";
        }
    }

    if (empty($errors)) {

        $total_amount = $price * $seats;
        $travel_date_esc = mysqli_real_escape_string($conn, $travel_date);

        $sql = "INSERT INTO bookings 
                (customer_id, package_id, seats, total_amount, status, booking_date, created_at)
                VALUES 
                ($customer_id, $package_id, $seats, $total_amount, 'Pending', '$travel_date_esc', NOW())";

        if (mysqli_query($conn, $sql)) {
            $booking_id = mysqli_insert_id($conn);
            header("Location: payment.php?booking_id=" . $booking_id);
            exit;
        } else {
            $errors[] = "Error booking package: " . mysqli_error($conn);
        }
    }
}

include 'header.php';
?>

<div class="row">

    <!-- LEFT: PACKAGE DETAILS -->
    <div class="col-md-8">
        <div class="card card-shadow mb-4">
            <div class="card-body">
                <h3 class="card-title mb-2"><?php echo htmlspecialchars($name); ?></h3>
                <p class="text-muted">📍 <?php echo htmlspecialchars($location); ?> — <?php echo $days; ?> Days</p>

                <?php if (!empty($image)): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" class="img-fluid rounded mb-3 shadow-soft" />
                <?php endif; ?>

                <p><?php echo nl2br(htmlspecialchars($description)); ?></p>
            </div>
        </div>

        <!-- ROUTE MAP + DISTANCE -->
        <div class="card card-shadow mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">🗺 Route Map & Distance</h5>

                <?php if ($from_location && $to_location): ?>
                    <p>
                        <strong>From:</strong> <?php echo htmlspecialchars($from_location); ?><br>
                        <strong>To:</strong> <?php echo htmlspecialchars($to_location); ?>
                    </p>

                    <div class="map-container mb-3" style="height: 350px;">
                        <iframe
                            width="100%" height="100%"
                            style="border:0;"
                            loading="lazy"
                            allowfullscreen
                            src="https://www.google.com/maps?q=<?php echo urlencode($from_location . ' to ' . $to_location); ?>&output=embed">
                        </iframe>
                    </div>

                    <p>
                        <strong>Estimated Distance:</strong>
                        <?php echo $distance_km ? htmlspecialchars($distance_km) . " km" : "As per route shown on map"; ?>
                    </p>
                <?php else: ?>
                    <p class="text-muted">Route information not available for this package.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT: BOOKING CARD -->
    <div class="col-md-4">
        <div class="card card-shadow">
            <div class="card-body">
                <h5 class="card-title">Book this Trip</h5>

                <p>
                    Price per seat:
                    <strong>₹<?php echo number_format($price, 2); ?></strong>
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Number of Seats (Max 5)</label>
                        <input type="number"
                               name="seats"
                               min="1"
                               max="5"
                               value="1"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Travel Date</label>
                        <input type="date"
                               name="travel_date"
                               id="travel_date"
                               class="form-control"
                               required
                               min="<?php echo $today; ?>"
                               max="<?php echo $maxDate; ?>"
                               value="<?php echo htmlspecialchars($travel_value); ?>"
                               onkeydown="return false">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-ripple">
                        Proceed to Payment
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
