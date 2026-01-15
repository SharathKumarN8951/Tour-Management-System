<?php
require 'db.php';
include 'header.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];

$sql_name = "SELECT name FROM customers WHERE id = $customer_id";
$res_name = mysqli_query($conn, $sql_name);
$row_name = mysqli_fetch_assoc($res_name);
$customer_name = $row_name['name'] ?? 'Customer';

$sql_upcoming = "SELECT b.*, p.name AS package_name, p.location 
                 FROM bookings b 
                 JOIN packages p ON b.package_id = p.id
                 WHERE b.customer_id = $customer_id
                 ORDER BY b.created_at DESC
                 LIMIT 5";
$res_upcoming = mysqli_query($conn, $sql_upcoming);
?>

<style>
/* ===== Dashboard headings ===== */
.dashboard-heading {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: .4rem;
}
.dashboard-subtext {
    color: var(--text-muted);
    max-width: 640px;
}

/* ===== Status pills (light & dark theme friendly) ===== */
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.15rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

/* Base (light theme) colors */
.status-paid {
    background: rgba(22,163,74,0.15);
    color: #15803d;
}
.status-pending {
    background: rgba(234,179,8,0.15);
    color: #b45309;
}
.status-confirmed {
    background: rgba(59,130,246,0.15);
    color: #1d4ed8;
}
.status-cancelled {
    background: rgba(248,113,113,0.16);
    color: #b91c1c;
}
.status-default {
    background: rgba(148,163,184,0.18);
    color: #374151;
}

/* 🔥 DARK THEME: high contrast for status pills */
[data-theme="dark"] .card-body .status-pill {
    color: #f9fafb !important;  /* bright text */
}
[data-theme="dark"] .status-paid {
    background-color: #166534;
}
[data-theme="dark"] .status-pending {
    background-color: #854d0e;
}
[data-theme="dark"] .status-confirmed {
    background-color: #1d4ed8;
}
[data-theme="dark"] .status-cancelled {
    background-color: #b91c1c;
}
[data-theme="dark"] .status-default {
    background-color: #4b5563;
}

/* Quick actions */
.quick-actions a {
    text-decoration: none;
    display: block;
    margin-bottom: .25rem;
}

/* ===== Table visibility fixes ===== */
.dashboard-table thead th {
    font-weight: 600;
}

/* Dark theme: override Bootstrap table background + text */
[data-theme="dark"] .dashboard-table > :not(caption) > * > * {
    background-color: transparent !important;   /* remove white cells */
    color: #e5e7eb !important;                 /* light grey text */
    border-color: rgba(31,41,55,0.9) !important;
}

/* Dark theme: header text a bit brighter */
[data-theme="dark"] .dashboard-table thead th {
    color: #f9fafb !important;
}

/* Dark theme: "Recent Bookings" header text */
[data-theme="dark"] .card-header {
    color: #f9fafb !important;
}
</style>

<div class="row mb-4">
    <div class="col-md-8">
        <h3 class="dashboard-heading">Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h3>
        <p class="dashboard-subtext">
            This is your customer dashboard. From here you can manage your profile,
            view bookings, and explore packages.
        </p>

        <div class="card card-shadow mt-3">
            <div class="card-header border-0" style="background: transparent;">
                <strong>Recent Bookings</strong>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($res_upcoming) === 0): ?>
                    <p class="mb-0">No bookings found. <a href="packages.php">Browse packages</a>.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 dashboard-table">
                            <thead>
                                <tr>
                                    <th>Package</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($b = mysqli_fetch_assoc($res_upcoming)): ?>
                                    <?php
                                        $status = strtolower(trim($b['status']));
                                        $status_class = 'status-default';
                                        if ($status === 'paid') {
                                            $status_class = 'status-paid';
                                        } elseif ($status === 'pending') {
                                            $status_class = 'status-pending';
                                        } elseif ($status === 'confirmed') {
                                            $status_class = 'status-confirmed';
                                        } elseif ($status === 'cancelled') {
                                            $status_class = 'status-cancelled';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b['package_name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['location']); ?></td>
                                        <td>
                                            <span class="status-pill <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($b['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($b['created_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-shadow">
            <div class="card-body quick-actions">
                <h5 class="fw-bold mb-3">Quick Actions</h5>
                <a href="profile.php">Edit Profile</a>
                <a href="packages.php">Browse Packages</a>
                <a href="booking_history.php">View Booking History</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
