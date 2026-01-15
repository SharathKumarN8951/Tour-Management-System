<?php
require '../db.php';
require 'admin_header.php';

// Fetch counts
$customers_count = 0;
$packages_count = 0;
$bookings_count = 0;

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM customers");
if ($row = mysqli_fetch_assoc($res)) $customers_count = (int)$row['c'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM packages");
if ($row = mysqli_fetch_assoc($res)) $packages_count = (int)$row['c'];

$res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings");
if ($row = mysqli_fetch_assoc($res)) $bookings_count = (int)$row['c'];
?>
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card card-shadow">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">Customers</h5>
                <h2 class="fw-bold"><?php echo $customers_count; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-shadow">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">Packages</h5>
                <h2 class="fw-bold"><?php echo $packages_count; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-shadow">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">Bookings</h5>
                <h2 class="fw-bold"><?php echo $bookings_count; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class="card card-shadow mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Admin Quick Links</h5>
        <div class="d-flex flex-wrap gap-3">
            <a href="packages.php" class="quick-btn">📦 Manage Packages</a>
            <a href="bookings.php" class="quick-btn">📋 Manage Bookings</a>
            <a href="customers.php" class="quick-btn">👥 View Customers</a>
        </div>
    </div>
</div>

<?php
// Fetch bookings by status for chart
$status_data = [
    'Pending' => 0,
    'Confirmed' => 0,
    'Paid' => 0,
    'Cancelled' => 0
];
$res_status = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM bookings GROUP BY status");
if ($res_status) {
    while ($row = mysqli_fetch_assoc($res_status)) {
        $st = $row['status'];
        if (isset($status_data[$st])) {
            $status_data[$st] = (int)$row['c'];
        }
    }
}
?>

<!-- Bookings Status Bar Chart -->
<div class="card card-shadow mb-5">
    <div class="card-body">
        <h5 class="card-title mb-3">Bookings by Status</h5>
        <canvas id="statusChart" height="120"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statusChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',  // BAR CHART
    data: {
        labels: ['Pending', 'Confirmed', 'Paid', 'Cancelled'],
        datasets: [{
            label: 'Total Bookings',
            data: [
                <?php echo $status_data['Pending']; ?>,
                <?php echo $status_data['Confirmed']; ?>,
                <?php echo $status_data['Paid']; ?>,
                <?php echo $status_data['Cancelled']; ?>
            ],
            backgroundColor: [
                '#fbbf24', // Pending
                '#3b82f6', // Confirmed
                '#22c55e', // Paid
                '#ef4444'  // Cancelled
            ],
            borderColor: '#ffffff',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#cbd5e1' }
            },
            x: {
                ticks: { color: '#cbd5e1' }
            }
        },
        plugins: {
            legend: {
                labels: { color: '#cbd5e1' }
            }
        }
    }
});
</script>

<?php require 'admin_footer.php'; ?>
