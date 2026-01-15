<?php
require 'db.php';
include 'header.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];
$msg = "";
$errors = [];

$sql = "SELECT * FROM customers WHERE id = $customer_id";
$res = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($res);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $profile_image = $customer['profile_image'] ?? '';

    if ($name === '') $errors[] = "Name is required.";

    // Phone validation: optional, but if given, must be 10 digits (Indian format 6-9 start)
    if ($phone !== '') {
        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            $errors[] = "Enter a valid 10-digit phone number (starting from 6-9).";
        }
    }

    // Handle profile image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $file = $_FILES['profile_image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmp = $file['tmp_name'];
            $nameFile = basename($file['name']);
            $ext = strtolower(pathinfo($nameFile, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allowed)) {
                $errors[] = "Only JPG, PNG, GIF images are allowed for profile photo.";
            } else {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $newName = 'profile_' . $customer_id . '_' . time() . '.' . $ext;
                $destRel = 'uploads/' . $newName;
                $dest = __DIR__ . '/' . $destRel;
                if (move_uploaded_file($tmp, $dest)) {
                    $profile_image = $destRel;
                } else {
                    $errors[] = "Failed to upload profile photo.";
                }
            }
        } else {
            $errors[] = "Error uploading profile photo.";
        }
    }

    if (empty($errors)) {
        $name_esc    = mysqli_real_escape_string($conn, $name);
        $phone_esc   = mysqli_real_escape_string($conn, $phone);
        $address_esc = mysqli_real_escape_string($conn, $address);
        $city_esc    = mysqli_real_escape_string($conn, $city);
        $state_esc   = mysqli_real_escape_string($conn, $state);
        $country_esc = mysqli_real_escape_string($conn, $country);
        $pincode_esc = mysqli_real_escape_string($conn, $pincode);
        $profile_esc = mysqli_real_escape_string($conn, $profile_image);

        $sql_up = "UPDATE customers SET
                    name='$name_esc',
                    phone='$phone_esc',
                    address='$address_esc',
                    city='$city_esc',
                    state='$state_esc',
                    country='$country_esc',
                    pincode='$pincode_esc',
                    profile_image='$profile_esc'
                   WHERE id = $customer_id";

        if (mysqli_query($conn, $sql_up)) {
            $msg = "Profile updated successfully.";
            $res = mysqli_query($conn, "SELECT * FROM customers WHERE id = $customer_id");
            $customer = mysqli_fetch_assoc($res);
            $_SESSION['customer_name'] = $customer['name'];
        } else {
            $errors[] = "Error updating profile: " . mysqli_error($conn);
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3">My Profile</h3>
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

                <div class="mb-3 text-center">
                    <?php if (!empty($customer['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($customer['profile_image']); ?>" 
                             alt="Profile photo" class="rounded-circle border"
                             style="width: 90px; height: 90px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center"
                             style="width: 90px; height: 90px;">
                            <span class="text-muted">No Photo</span>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="profile_image" class="form-control">
                        <div class="form-text">Optional. JPG / PNG / GIF.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email (read only)</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               pattern="[0-9]{10}" maxlength="10"
                               placeholder="10-digit mobile number"
                               value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                        <div class="form-text">Example: 9876543210</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control"
                                   value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                   value="<?php echo htmlspecialchars($customer['country'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control"
                                   value="<?php echo htmlspecialchars($customer['pincode'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
