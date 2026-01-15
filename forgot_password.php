<?php
require 'db.php';
require_once __DIR__ . '/mailer/send_reset_mail.php';
include 'header.php';

$email = "";
$msg = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    } else {
        $email_esc = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT id FROM customers WHERE email='$email_esc' LIMIT 1";
        $res = mysqli_query($conn, $sql);

        if ($res && mysqli_num_rows($res) === 1) {

            // 1) Simple demo token (just for display, not stored)
            $token = bin2hex(random_bytes(8)); // 16-char token, not used in DB

            // 2) Build reset link with email in query string
            $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $domain     = $_SERVER['HTTP_HOST'];
            $folder     = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $reset_link = $protocol . '://' . $domain . $folder
                        . '/reset_password.php?email=' . urlencode($email) 
                        . '&token=' . urlencode($token);

            // 3) Send email (for real, if SMTP configured)
            $sent = send_reset_mail($email, $reset_link);

            if ($sent) {
                $msg = "A password reset link has been sent to your registered email address.<br>
                        </a>";
            } else {
                $msg = "Email sending failed (demo mode).<br>
                        <strong>Reset link:</strong> <a href='$reset_link'>$reset_link</a><br>
                        Please configure SMTP / app password for real email sending.";
            }

        } else {
            $errors[] = "No account found with that email.";
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-shadow">
            <div class="card-body">
                <h3 class="card-title mb-3">Forgot Password</h3>
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
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Registered Email Address</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Generate Reset Link</button>
                </form>
                <p class="mt-3 mb-0 text-center">
                    <a href="login.php">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
