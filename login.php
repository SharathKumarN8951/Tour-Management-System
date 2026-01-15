<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$email = "";
$password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = "Email and password are required.";
    } else {
        $email_esc = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT * FROM customers WHERE email = '$email_esc' LIMIT 1";
        $res = mysqli_query($conn, $sql);

        if ($res && mysqli_num_rows($res) === 1) {
            $user = mysqli_fetch_assoc($res);

            // ✅ Check hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];

                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="mb-3 text-center">Customer Login</h3>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               required
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-ripple w-100">
                        Login
                    </button>

                    <div class="mt-3 d-flex justify-content-between">
                        <a href="forgot_password.php">Forgot Password?</a>
                        <a href="register.php">Create Account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
