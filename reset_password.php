<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = "";
$errors = [];
$show_form = false;

$email = $_GET['email'] ?? '';
$email = mysqli_real_escape_string($conn, $email);

// ---------- FIRST LOAD: from link ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($email === '') {
        $msg = "Invalid password reset request.";
    } else {
        $sql = "SELECT id FROM customers WHERE email='$email' LIMIT 1";
        $res = mysqli_query($conn, $sql);
        if ($res && mysqli_num_rows($res) === 1) {
            // Email exists → show form
            $show_form = true;
        } else {
            $msg = "No account found for this reset link.";
        }
    }
}

// ---------- SUBMIT NEW PASSWORD ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($email === '') {
        $errors[] = "Invalid reset request.";
    } else {
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";

        $sql = "SELECT id FROM customers WHERE email='$email' LIMIT 1";
        $res = mysqli_query($conn, $sql);
        if (!$res || mysqli_num_rows($res) !== 1) {
            $errors[] = "No account found for this reset request.";
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $hash_esc = mysqli_real_escape_string($conn, $hash);

            $sql_up = "UPDATE customers SET password='$hash_esc' WHERE email='$email'";
            if (mysqli_query($conn, $sql_up)) {
                $msg = "Password has been reset successfully. You can now <a href='login.php'>login</a>.";
                $show_form = false;
            } else {
                $errors[] = "Error updating password: " . mysqli_error($conn);
                $show_form = true;
            }
        } else {
            $show_form = true;
        }
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3">Reset Password</h3>

                <?php if ($msg): ?>
                    <div class="alert alert-info"><?php echo $msg; ?></div>
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

                <?php if ($show_form): ?>
                    <form method="post">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
