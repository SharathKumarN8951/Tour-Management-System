<?php
require '../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$email = "";

// Auto-create default admin if none exists
$check = mysqli_query($conn, "SELECT COUNT(*) AS c FROM admins");
if ($check) {
    $row = mysqli_fetch_assoc($check);
    if ((int)$row['c'] === 0) {
        $defaultName = 'Default Admin';
        $defaultEmail = 'admin@admin.com';
        $defaultPass = password_hash('admin123', PASSWORD_BCRYPT);

        $n = mysqli_real_escape_string($conn, $defaultName);
        $e = mysqli_real_escape_string($conn, $defaultEmail);
        $p = mysqli_real_escape_string($conn, $defaultPass);

        mysqli_query($conn, "INSERT INTO admins (name, email, password) VALUES ('$n', '$e', '$p')");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if ($password === '') {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $email_esc = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT * FROM admins WHERE email='$email_esc' LIMIT 1";
        $res = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($res)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - TourMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f7fb; }
        .card-shadow { box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-5">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3 text-center">Admin Login</h3>
                <p class="text-muted small text-center mb-3">
                    <br>
                   <code></code><br>
                   <code></code>
                </p>
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
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Login</button>
                </form>
                <p class="mt-3 mb-0 text-center">
                    <a href="../index.php">Back to Site</a>
                </p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
