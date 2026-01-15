<?php
// booking_history.php

require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ LOGIN CHECK BEFORE ANY HTML OUTPUT
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];

// Fetch bookings for this customer
$sql = "SELECT b.*, p.name AS package_name, p.location 
        FROM bookings b
        JOIN packages p ON b.package_id = p.id
        WHERE b.customer_id = $customer_id
        ORDER BY b.created_at DESC";
$res = mysqli_query($conn, $sql);

// Now we can safely output HTML
include 'header.php';
?>

<h3 class="mb-3">My Booking History</h3>

<style>
    .timeline-box {
        font-size: 0.8rem;
        line-height: 1.3;
        margin-top: 4px;
        padding-left: 8px;
        border-left: 2px solid #ccc;
    }
    .timeline-dot {
        width: 6px;
        height: 6px;
        background-color: #0d6efd;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
</style>

<div class="card card-shadow">
    <div class="card-body">
        <?php if (!$res || mysqli_num_rows($res) === 0): ?>
            <p class="mb-0">You have not booked any packages yet. 
                <a href="packages.php">Browse packages</a>.
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Location</th>
                            <th>Seats</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Booking Date</th>
                            <th>Created At</th>
                            <th>Action & Timeline</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($b = mysqli_fetch_assoc($res)): ?>
                        <?php
                        $bid = (int)$b['id'];

                        // Latest payment info
                        $payRes = mysqli_query($conn, "
                            SELECT status, transaction_id, created_at 
                            FROM payments 
                            WHERE booking_id = $bid 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        ");
                        $payStatus = null;
                        $payTxn    = null;
                        $payTime   = null;
                        if ($payRes && mysqli_num_rows($payRes) > 0) {
                            $prow = mysqli_fetch_assoc($payRes);
                            $payStatus = $prow['status'];
                            $payTxn    = $prow['transaction_id'];
                            $payTime   = $prow['created_at'];
                        }

                        $isPaid      = ($b['status'] === 'Paid' || $payStatus === 'Confirmed');
                        $underReview = ($payStatus === 'Pending' && !$isPaid);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['package_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['location']); ?></td>
                            <td><?php echo htmlspecialchars($b['seats']); ?></td>
                            <td>₹<?php echo htmlspecialchars($b['total_amount']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($b['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($b['created_at']); ?></td>
                            <td>
                                <!-- ACTION BUTTONS -->
                                <?php if ($isPaid): ?>
                                    <span class="text-success d-block mb-1">Paid</span>
                                    <a href="ticket.php?booking_id=<?php echo $b['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        Download Ticket (PDF)
                                    </a>
                                <?php elseif ($underReview): ?>
                                    <span class="text-warning d-block mb-1">Payment Under Review</span>
                                <?php else: ?>
                                    <a href="payment.php?booking_id=<?php echo $b['id']; ?>" 
                                       class="btn btn-sm btn-success mb-1">
                                        Pay via UPI
                                    </a>
                                <?php endif; ?>

                                <!-- PAYMENT STATUS TIMELINE -->
                                <div class="timeline-box text-muted mt-1">
                                    <div>
                                        <span class="timeline-dot"></span>
                                        Booking created: <?php echo htmlspecialchars($b['created_at']); ?>
                                    </div>

                                    <?php if ($payTime): ?>
                                        <div>
                                            <span class="timeline-dot"></span>
                                            Payment submitted: <?php echo htmlspecialchars($payTime); ?>
                                            (Status: <?php echo htmlspecialchars($payStatus); ?>)
                                        </div>
                                        <?php if ($payTxn): ?>
                                            <div style="margin-left: 12px;">
                                                Ref ID: <code><?php echo htmlspecialchars($payTxn); ?></code>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div>
                                            <span class="timeline-dot"></span>
                                            No payment submitted yet.
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <span class="timeline-dot"></span>
                                        Current booking status: <?php echo htmlspecialchars($b['status']); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
