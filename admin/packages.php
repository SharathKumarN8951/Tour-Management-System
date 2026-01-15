<?php
require '../db.php';
require 'admin_header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM packages WHERE id = $id");
    }
    header("Location: packages.php");
    exit;
}

$res = mysqli_query($conn, "SELECT * FROM packages ORDER BY created_at DESC");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Manage Packages</h3>
    <a href="package_form.php" class="btn btn-primary">Add New Package</a>
</div>
<div class="card card-shadow">
    <div class="card-body">
        <?php if (mysqli_num_rows($res) === 0): ?>
            <p class="mb-0">No packages found. Click "Add New Package" to create one.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Days</th>
                            <th>Coordinates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($p = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td>
                                <?php if (!empty($p['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($p['image']); ?>" alt="Package image"
                                         style="width: 60px; height: 40px; object-fit: cover;" class="border rounded">
                                <?php else: ?>
                                    <span class="text-muted small">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['location']); ?></td>
                            <td>₹<?php echo htmlspecialchars($p['price']); ?></td>
                            <td><?php echo htmlspecialchars($p['days']); ?></td>
                            <td>
                                <?php
                                if (!is_null($p['latitude']) && !is_null($p['longitude'])) {
                                    echo htmlspecialchars($p['latitude']) . ', ' . htmlspecialchars($p['longitude']);
                                } else {
                                    echo '<span class="text-muted">Not set</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="package_form.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <a href="packages.php?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this package?');">Delete</a>
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
