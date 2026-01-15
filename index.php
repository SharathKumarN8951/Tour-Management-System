<?php
require_once 'db.php';
session_start();

// Load customer details if logged in
$current_customer = null;
if (isset($_SESSION['customer_id'])) {
    $cid = (int)$_SESSION['customer_id'];
    $resCust = mysqli_query($conn, "SELECT name, profile_image FROM customers WHERE id = $cid");
    if ($resCust && mysqli_num_rows($resCust) > 0) {
        $current_customer = mysqli_fetch_assoc($resCust);
    }
}

// Stats
$package_count  = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM packages"))['c'];
$customer_count = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM customers"))['c'];
$booking_count  = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings"))['c'];

include 'header.php';
?>

<!-- ========================= PAGE-SPECIFIC STYLES ========================= -->
<style>
/* Fade-in page */
.page-fade {
    animation: fadein 0.7s ease forwards;
}
@keyframes fadein {
    from { opacity: 0; transform: translateY(15px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Hero banner shine animation */
.hero-banner {
    position: relative;
    border-radius: 20px;
    padding: 32px 28px;
    margin-bottom: 24px;
    color: #fff;
    overflow: hidden;
    background: linear-gradient(120deg, #4158d0, #c850c0, #ffcc70);
    background-size: 260% 260%;
    animation: gradientMove 10s ease infinite;
}
@keyframes gradientMove {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.hero-banner::before {
    content: "";
    position: absolute;
    top: -100%;
    left: -100%;
    width: 300%;
    height: 300%;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 60%);
    animation: glowLoop 6s linear infinite;
}
@keyframes glowLoop {
    0%   { transform: translate(-50%, -50%); }
    50%  { transform: translate(20%, 35%);   }
    100% { transform: translate(-50%, -50%); }
}

/* Slide-up animation for inner hero text */
.slide-up {
    animation: slideUp 0.8s ease both;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(25px); }
    to   { opacity: 1; transform: translateY(0);     }
}

/* Card hover zoom (uses card styles from header too) */
.card.card-shadow {
    transition: transform .32s ease, box-shadow .32s ease;
}

/* Ripple animated buttons */
.btn-animated {
    position: relative;
    overflow: hidden;
    transition: 0.3s;
}
.btn-animated:active::after {
    content: "";
    position: absolute;
    width: 140px;
    height: 140px;
    background: rgba(255,255,255,.4);
    border-radius: 50%;
    animation: ripple .5s linear;
}
@keyframes ripple {
    from { transform: scale(0);   opacity: 1; }
    to   { transform: scale(1.8); opacity: 0; }
}

/* Stats card animation */
.stat-card {
    border-radius: 16px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    opacity: 0;
    animation: fadeUp 1s ease forwards;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(25px); }
    to   { opacity: 1; transform: translateY(0);     }
}
.stat-card:nth-child(1) { animation-delay: .2s; }
.stat-card:nth-child(2) { animation-delay: .4s; }
.stat-card:nth-child(3) { animation-delay: .6s; }

.stat-value {
    font-size: 1.3rem;
    font-weight: 700;
}

/* Profile avatar */
.hero-avatar {
    width: 60px; height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,.8);
}
.hero-avatar img {
    width: 100%; height: 100%; object-fit: cover;
}

@media (max-width: 575.98px) {
    .hero-banner {
        padding: 20px 16px;
        text-align: center;
    }
    .hero-banner .btn {
        width: 100%;
        margin-bottom: 8px;
    }
}

/* ================== DARK THEME FIX FOR SYSTEM OVERVIEW ================== */
/* This is the main fix you needed */
[data-theme="dark"] .stat-card.bg-light {
    background-color: #f9fafb !important;        /* keep strips light */
    border: 1px solid rgba(148,163,184,0.4);
}

[data-theme="dark"] .stat-card.bg-light small,
[data-theme="dark"] .stat-card.bg-light .stat-value,
[data-theme="dark"] .stat-card.bg-light span {
    color: #0f172a !important;                   /* strong dark text */
    opacity: 1 !important;
}

/* Optional: make label slightly softer, value bolder */
[data-theme="dark"] .stat-card.bg-light small {
    opacity: 0.85 !important;
}
[data-theme="dark"] .stat-card.bg-light .stat-value {
    font-weight: 700;
}
</style>

<!-- ========================= PAGE CONTENT ========================= -->
<div class="page-fade">
    <div class="hero-banner">
        <div class="row align-items-center slide-up">
            <div class="col-sm-8">
                <h2 class="fw-bold mb-2">Plan your trip, explore the world 🌍</h2>
                <p class="mb-3">UPI Payments • Live Maps • Instant Ticket Download • Tours for Everyone</p>
                <a href="packages.php" class="btn btn-light btn-animated btn-sm">Browse Packages</a>
                <a href="booking_history.php" class="btn btn-outline-light btn-animated btn-sm">My Bookings</a>
            </div>

            <!-- DESKTOP PROFILE -->
            <div class="col-sm-4 d-none d-sm-flex justify-content-end">
                <?php if ($current_customer): ?>
                    <div class="text-end">
                        <div class="mb-2 d-flex align-items-center justify-content-end">
                            <div class="hero-avatar me-2">
                                <?php if (!empty($current_customer['profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($current_customer['profile_image']); ?>" alt="Profile">
                                <?php else: ?>
                                    <span class="fs-4 text-white">
                                        <?php echo strtoupper(substr($current_customer['name'],0,1)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <small>Hello,</small><br>
                                <strong><?php echo htmlspecialchars($current_customer['name']); ?></strong>
                            </div>
                        </div>
                        <a href="profile.php" class="btn btn-sm btn-outline-light btn-animated">View Profile</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- MOBILE PROFILE BUTTON -->
        <?php if ($current_customer): ?>
            <div class="d-sm-none mt-3 text-center">
                <a href="profile.php" class="btn btn-light btn-animated btn-sm w-100">
                    👤 View Profile
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- MAIN CARD -->
    <div class="card card-shadow mb-4">
        <div class="card-body">
            <h3 class="fw-bold text-primary mb-2">All-in-One Tour Booking Platform</h3>
            <p>Book tours, make payments, manage your trips, and download instant PDF tickets.</p>
            <ul>
                <li>Customer registration & login</li>
                <li>Tour packages with live locations</li>
                <li>UPI/QR screenshot payment + admin approval</li>
                <li>Booking history + ticket invoice PDF</li>
                <li>Profile photo & account management</li>
            </ul>
            <a href="packages.php" class="btn btn-primary btn-animated px-4">Start Booking</a>
        </div>
    </div>

    <!-- RIGHT SIDE: QUICK LINKS + STATS -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-shadow mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-2">🔗 Quick Links</h5>
                    <a href="register.php">➕ Create Account</a><br>
                    <a href="login.php">🔐 Login</a><br>
                    <a href="forgot_password.php">🗝 Reset Password</a><br>
                    <a href="booking_history.php">📜 My Bookings</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-shadow">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">📊 System Overview</h5>

                    <div class="stat-card bg-light mb-2">
                        <span style="font-size:1.4rem;">🧳</span>
                        <div>
                            <small>Packages</small>
                            <div class="stat-value" data-count="<?php echo $package_count; ?>">0</div>
                        </div>
                    </div>

                    <div class="stat-card bg-light mb-2">
                        <span style="font-size:1.4rem;">👥</span>
                        <div>
                            <small>Customers</small>
                            <div class="stat-value" data-count="<?php echo $customer_count; ?>">0</div>
                        </div>
                    </div>

                    <div class="stat-card bg-light">
                        <span style="font-size:1.4rem;">🎟</span>
                        <div>
                            <small>Bookings</small>
                            <div class="stat-value" data-count="<?php echo $booking_count; ?>">0</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================= COUNT-UP SCRIPT ========================= -->
<script>
document.querySelectorAll(".stat-value").forEach(counter => {
    const final = +counter.dataset.count;
    let count = 0;
    const step = Math.max(1, Math.ceil(final / 50));
    const speed = 16;

    (function update() {
        count += step;
        counter.textContent = (count > final ? final : count);
        if (count < final) setTimeout(update, speed);
    })();
});
</script>

<?php include 'footer.php'; ?>
