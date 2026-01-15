
<?php
require '../db.php';
require 'admin_header.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $id = (int) $_POST['booking_id'];
    $status = $_POST['status'];
    $allowed = ['Pending','Confirmed','Paid','Cancelled'];
    if ($id > 0 && in_array($status, $allowed, true)) {
        $status_esc = mysqli_real_escape_string($conn, $status);
        mysqli_query($conn, "UPDATE bookings SET status='$status_esc' WHERE id = $id");
    }
    header("Location: bookings.php");
    exit;
}

$sql = "SELECT b.*, c.name AS customer_name, c.email AS customer_email,
               p.name AS package_name, p.location AS package_location
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN packages p ON b.package_id = p.id
        ORDER BY b.created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Manage Bookings</h3>
    <a href="export_bookings.php" class="btn btn-sm btn-outline-primary">Export Bookings (CSV)</a>
</div>
<div class="card card-shadow">
    <div class="card-body">
        <?php if (mysqli_num_rows($res) === 0): ?>
            <p class="mb-0">No bookings found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Status</th>
                            <th>Booking Date</th>
                            <th>Created At</th>
                            <th>Change Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($b = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($b['customer_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($b['customer_email']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($b['package_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($b['package_location']); ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($b['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($b['created_at']); ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php
                                        $statuses = ['Pending','Confirmed','Paid','Cancelled'];
                                        foreach ($statuses as $s):
                                        ?>
                                            <option value="<?php echo $s; ?>" <?php if ($s === $b['status']) echo 'selected'; ?>>
                                                <?php echo $s; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require 'admin_footer.php'; ?>
