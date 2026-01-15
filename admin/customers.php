<?php
require '../db.php';
require 'admin_header.php';

$sql = "SELECT * FROM customers ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Customers</h3>
    <a href="export_customers.php" class="btn btn-sm btn-outline-primary">Export Customers (CSV)</a>
</div>
<div class="card card-shadow">
    <div class="card-body">
        <?php if (mysqli_num_rows($res) === 0): ?>
            <p class="mb-0">No customers found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($c = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><?php echo htmlspecialchars($c['email']); ?></td>
                            <td><?php echo htmlspecialchars($c['phone']); ?></td>
                            <td>
                                <?php
                                $parts = array_filter([
                                    $c['city'] ?? '',
                                    $c['state'] ?? '',
                                    $c['country'] ?? ''
                                ]);
                                echo htmlspecialchars(implode(', ', $parts));
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require 'admin_footer.php'; ?>
