<?php
require 'db.php';
include 'header.php';

$name = $email = $password = $confirm = $phone = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));  // force lowercase
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    // 🔥 Gmail-only validation (lowercase)
    if (!preg_match('/^[a-z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = "Email must be a valid Gmail address (example: name@gmail.com).";
    }

    // Phone: optional, but if present must be 10 digits starting 6–9
    if ($phone !== '') {
        if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            $errors[] = "Enter a valid 10-digit phone number (starting from 6-9).";
        }
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $email_esc = mysqli_real_escape_string($conn, $email);
        $res = mysqli_query($conn, "SELECT id FROM customers WHERE email='$email_esc'");
        if (mysqli_num_rows($res) > 0) {
            $errors[] = "Email is already registered.";
        } else {
            $name_esc  = mysqli_real_escape_string($conn, $name);
            $phone_esc = mysqli_real_escape_string($conn, $phone);
            $hash      = password_hash($password, PASSWORD_BCRYPT);
            $hash_esc  = mysqli_real_escape_string($conn, $hash);

            $sql = "INSERT INTO customers (name, email, password, phone) 
                    VALUES ('$name_esc', '$email_esc', '$hash_esc', '$phone_esc')";
            if (mysqli_query($conn, $sql)) {
                echo '<div class="alert alert-success mb-3">Registration successful. You can now <a href="login.php" class="alert-link">login</a>.</div>';
                $name = $email = $phone = "";
            } else {
                $errors[] = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!-- ====== DARK THEME ALERT FIX ====== -->
<style>
/* Make error/success alerts readable in dark theme */
[data-theme="dark"] .alert-danger {
    background-color: rgba(248, 113, 113, 0.16) !important; /* soft red */
    border-color: #f97373 !important;
    color: #fee2e2 !important; /* light text */
}
[data-theme="dark"] .alert-danger ul li,
[data-theme="dark"] .alert-danger p {
    color: #fee2e2 !important;
}

[data-theme="dark"] .alert-success {
    background-color: rgba(34, 197, 94, 0.16) !important; /* soft green */
    border-color: #4ade80 !important;
    color: #bbf7d0 !important;
}
[data-theme="dark"] .alert-success a {
    color: #ecfccb !important;
    text-decoration: underline;
}
</style>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3">Customer Registration</h3>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?php echo htmlspecialchars($name); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required
                               pattern="^[a-z0-9._%+-]+@gmail\.com$"
                               style="text-transform: lowercase;"
                               oninput="this.value = this.value.toLowerCase();"
                               title="Email must be in lowercase and end with @gmail.com"
                               value="<?php echo htmlspecialchars($email); ?>">
                        <div class="form-text text-info">
                            Only Gmail format allowed (example: username@gmail.com)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               maxlength="10" pattern="[0-9]{10}"
                               placeholder="10-digit mobile number"
                               value="<?php echo htmlspecialchars($phone); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password (min 6 chars)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>

                <p class="mt-3 mb-0 text-center">
                    Already have an account? <a href="login.php">Login here</a>.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
