<?php
require 'db.php';
include 'header.php';

$sql = "SELECT * FROM packages ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
?>
<h3 class="mb-3">Tour Packages</h3>
<div class="row">
<?php while ($p = mysqli_fetch_assoc($res)): ?>
    <div class="col-md-4 mb-4">
        <div class="card card-shadow h-100">
            <?php if (!empty($p['image'])): ?>
                <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="Package image" style="height: 180px; object-fit: cover;">
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                    <span class="text-muted">No image</span>
                </div>
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
                <p class="card-text mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($p['location']); ?></p>
                <p class="card-text mb-1"><strong>Price:</strong> ₹<?php echo htmlspecialchars($p['price']); ?></p>
                <p class="card-text small text-muted mb-3"><?php echo nl2br(htmlspecialchars(substr($p['description'], 0, 100))); ?>...</p>
                <a href="package_details.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary mt-auto">View Details</a>
            </div>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php include 'footer.php'; ?>
