<?php
require '../db.php';
require 'admin_header.php';

$msg = "";
$errors = [];

// Fetch existing settings
$sql = "SELECT * FROM payment_settings ORDER BY id ASC LIMIT 1";
$res = mysqli_query($conn, $sql);
$settings = mysqli_fetch_assoc($res);
if (!$settings) {
    mysqli_query($conn, "INSERT INTO payment_settings (upi_id, qr_image) VALUES ('', NULL)");
    $res = mysqli_query($conn, "SELECT * FROM payment_settings ORDER BY id ASC LIMIT 1");
    $settings = mysqli_fetch_assoc($res);
}
$id = (int)$settings['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upi_id = trim($_POST['upi_id'] ?? '');
    $upi_id_esc = mysqli_real_escape_string($conn, $upi_id);
    $qr_path_sql = $settings['qr_image'];

    // Handle QR image upload
    if (!empty($_FILES['qr_image']['name'])) {
        $file = $_FILES['qr_image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmp = $file['tmp_name'];
            $name = basename($file['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allowed)) {
                $errors[] = "Only JPG, PNG or GIF images are allowed for QR code.";
            } else {
                if (!is_dir('../uploads')) {
                    mkdir('../uploads', 0777, true);
                }
                $newName = 'qr_' . time() . '.' . $ext;
                $destRel = 'uploads/' . $newName; // relative from project root
                $dest = dirname(__DIR__) . '/' . $destRel;
                if (move_uploaded_file($tmp, $dest)) {
                    $qr_path_sql = $destRel;
                } else {
                    $errors[] = "Failed to upload QR code image.";
                }
            }
        } else {
            $errors[] = "Error uploading file.";
        }
    }

    if (empty($errors)) {
        $qr_esc = mysqli_real_escape_string($conn, $qr_path_sql);
        $upSql = "UPDATE payment_settings SET upi_id='$upi_id_esc', qr_image='$qr_esc' WHERE id = $id";
        if (mysqli_query($conn, $upSql)) {
            $msg = "Payment settings updated successfully.";
            $res = mysqli_query($conn, "SELECT * FROM payment_settings WHERE id = $id");
            $settings = mysqli_fetch_assoc($res);
        } else {
            $errors[] = "Error updating settings: " . mysqli_error($conn);
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3">Payment Settings (UPI / QR)</h3>
                <p class="text-muted small">
                    Here admin can configure the real UPI ID and upload the QR image. 
                    This QR will be shown to customers on the payment page.
                </p>
                <?php if ($msg): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="form-control"
                               placeholder="example@upi"
                               value="<?php echo htmlspecialchars($settings['upi_id'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">QR Code Image</label>
                        <input type="file" name="qr_image" class="form-control">
                        <div class="form-text">Upload JPG / PNG / GIF image.</div>
                    </div>
                    <?php if (!empty($settings['qr_image'])): ?>
                        <div class="mb-3">
                            <label class="form-label d-block">Current QR Image</label>
                            <img src="../<?php echo htmlspecialchars($settings['qr_image']); ?>" 
                                 alt="QR Code" class="img-fluid border rounded" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'admin_footer.php'; ?>
