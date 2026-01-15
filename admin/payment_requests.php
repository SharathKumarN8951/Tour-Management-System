<?php
require '../db.php';
require '../email_functions.php';
require 'admin_header.php';

// Handle approve / reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'], $_POST['action'])) {
    $payment_id = (int) $_POST['payment_id'];
    $action = $_POST['action'];
    if ($payment_id > 0) {
        $res = mysqli_query($conn, "SELECT * FROM payments WHERE id = $payment_id");
        if ($res && mysqli_num_rows($res) > 0) {
            $payment = mysqli_fetch_assoc($res);
            $booking_id = (int)$payment['booking_id'];
            if ($action === 'approve') {
                mysqli_query($conn, "UPDATE payments SET status='Confirmed' WHERE id = $payment_id");
                mysqli_query($conn, "UPDATE bookings SET status='Paid' WHERE id = $booking_id");
                // Send email to customer about payment approval
                if (function_exists('send_payment_approved_email')) {
                    send_payment_approved_email($booking_id);
                }
            } elseif ($action === 'reject') {
                mysqli_query($conn, "UPDATE payments SET status='Rejected' WHERE id = $payment_id");
                mysqli_query($conn, "UPDATE bookings SET status='Pending' WHERE id = $booking_id");
            }
        }
    }
    header("Location: payment_requests.php");
    exit;
}

// List pending payments
$sql = "SELECT pay.*, b.booking_date, b.seats, b.total_amount, b.status AS booking_status,
               c.name AS customer_name, c.email AS customer_email,
               p.name AS package_name, p.location AS package_location
        FROM payments pay
        JOIN bookings b ON pay.booking_id = b.id
        JOIN customers c ON b.customer_id = c.id
        JOIN packages p ON b.package_id = p.id
        WHERE pay.status = 'Pending'
        ORDER BY pay.created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<h3 class="mb-3">Pending Payment Requests</h3>
<div class="card card-shadow">
    <div class="card-body">
        <?php if (mysqli_num_rows($res) === 0): ?>
            <p class="mb-0">No pending payment requests.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>UPI Ref ID</th>
                            <th>Booking Date</th>
                            <th>Requested On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['customer_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['customer_email']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['package_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['package_location']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['seats']); ?></td>
                            <td>₹<?php echo htmlspecialchars($row['amount']); ?></td>
                            <td><code><?php echo htmlspecialchars($row['transaction_id']); ?></code></td>
                            <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success"
                                            onclick="return confirm('Mark this payment as Paid?');">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Reject this payment request?');">Reject</button>
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
