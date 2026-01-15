<?php
require '../db.php';
require 'admin_header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $id > 0;

$name = $location = $description = "";
$price = "";
$days = "";
$latitude = "";
$longitude = "";
$image = "";
$from_location = "";
$to_location = "";
$errors = [];
$msg = "";

// ---------- LOAD DATA FOR EDIT ----------
if ($is_edit) {
    $res = mysqli_query($conn, "SELECT * FROM packages WHERE id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        $name          = $row['name'];
        $location      = $row['location'];
        $description   = $row['description'];
        $price         = $row['price'];
        $days          = $row['days'];
        $latitude      = $row['latitude'];
        $longitude     = $row['longitude'];
        $image         = $row['image'];
        $from_location = $row['from_location'];
        $to_location   = $row['to_location'];
    } else {
        $is_edit = false;
    }
}

// ---------- FORM SUBMIT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $price         = trim($_POST['price'] ?? '');
    $days          = trim($_POST['days'] ?? '');
    $latitude      = trim($_POST['latitude'] ?? '');
    $longitude     = trim($_POST['longitude'] ?? '');
    $from_location = trim($_POST['from_location'] ?? '');
    $to_location   = trim($_POST['to_location'] ?? '');

    $image_path_sql = $image; // default old image if editing

    // Validations
    if ($name === '') $errors[] = "Name is required.";
    if ($location === '') $errors[] = "Location is required.";
    if ($description === '') $errors[] = "Description is required.";
    if ($price === '' || !is_numeric($price)) $errors[] = "Valid price is required.";
    if ($days === '' || !ctype_digit($days)) $errors[] = "Valid number of days is required.";
    if ($from_location === '') $errors[] = "From (Start Location) is required.";
    if ($to_location === '') $errors[] = "To (Destination) is required.";

    $lat_val = $latitude !== '' ? (float)$latitude : null;
    $lng_val = $longitude !== '' ? (float)$longitude : null;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $tmp = $file['tmp_name'];
            $nameFile = basename($file['name']);
            $ext = strtolower(pathinfo($nameFile, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];

            if (!in_array($ext, $allowed)) {
                $errors[] = "Only JPG, PNG, or GIF images allowed.";
            } else {
                if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
                $newName = 'package_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                $destRel = 'uploads/' . $newName;
                $destAbs = dirname(__DIR__) . '/' . $destRel;

                if (move_uploaded_file($tmp, $destAbs)) {
                    $image_path_sql = $destRel;
                } else {
                    $errors[] = "Failed to upload package image.";
                }
            }
        } else {
            $errors[] = "Error uploading package image.";
        }
    }

    // Insert or update DB
    if (empty($errors)) {
        $name_esc         = mysqli_real_escape_string($conn, $name);
        $loc_esc          = mysqli_real_escape_string($conn, $location);
        $desc_esc         = mysqli_real_escape_string($conn, $description);
        $from_esc         = mysqli_real_escape_string($conn, $from_location);
        $to_esc           = mysqli_real_escape_string($conn, $to_location);
        $img_esc          = mysqli_real_escape_string($conn, $image_path_sql);
        $price_esc        = (float)$price;
        $days_esc         = (int)$days;
        $lat_sql = is_null($lat_val) ? "NULL" : $lat_val;
        $lng_sql = is_null($lng_val) ? "NULL" : $lng_val;

        if ($is_edit) {
            $sql = "UPDATE packages SET
                        name='$name_esc',
                        location='$loc_esc',
                        description='$desc_esc',
                        price=$price_esc,
                        days=$days_esc,
                        image='$img_esc',
                        latitude=$lat_sql,
                        longitude=$lng_sql,
                        from_location='$from_esc',
                        to_location='$to_esc'
                    WHERE id = $id";
            if (mysqli_query($conn, $sql)) $msg = "Package updated successfully.";
            else $errors[] = "Error updating package: " . mysqli_error($conn);

        } else {
            $sql = "INSERT INTO packages 
                    (name, location, description, price, days, image, latitude, longitude, from_location, to_location)
                    VALUES (
                        '$name_esc', '$loc_esc', '$desc_esc', $price_esc, $days_esc,
                        '$img_esc', $lat_sql, $lng_sql, '$from_esc', '$to_esc')";
            if (mysqli_query($conn, $sql)) {
                $msg = "Package created successfully.";
                $is_edit = true;
                $id = mysqli_insert_id($conn);
            } else {
                $errors[] = "Error creating package: " . mysqli_error($conn);
            }
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3">
                    <?php echo $is_edit ? 'Edit Package' : 'Add New Package'; ?>
                </h3>

                <?php if ($msg): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo $e; ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Package Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($name); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">From (Start Location)</label>
                        <input type="text" name="from_location" class="form-control" required
                               value="<?php echo htmlspecialchars($from_location); ?>"
                               placeholder="e.g. Bangalore, Karnataka">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">To (Destination)</label>
                        <input type="text" name="to_location" class="form-control" required
                               value="<?php echo htmlspecialchars($to_location); ?>"
                               placeholder="e.g. Coorg, Karnataka">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City / Location</label>
                        <input type="text" name="location" class="form-control" required
                               value="<?php echo htmlspecialchars($location); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price (₹)</label>
                        <input type="text" name="price" class="form-control" required
                               value="<?php echo htmlspecialchars($price); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Days</label>
                        <input type="number" name="days" class="form-control" required
                               value="<?php echo htmlspecialchars($days); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Latitude (optional)</label>
                        <input type="text" name="latitude" class="form-control"
                               value="<?php echo htmlspecialchars($latitude); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Longitude (optional)</label>
                        <input type="text" name="longitude" class="form-control"
                               value="<?php echo htmlspecialchars($longitude); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Package Photo (optional)</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <?php if (!empty($image)): ?>
                        <div class="mb-3">
                            <label class="form-label d-block">Current Photo</label>
                            <img src="../<?php echo htmlspecialchars($image); ?>"
                                 class="img-fluid border rounded"
                                 style="max-width: 250px;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?php
                            echo htmlspecialchars($description);
                        ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Package</button>
                    <a href="packages.php" class="btn btn-secondary">Back to List</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'admin_footer.php'; ?>
