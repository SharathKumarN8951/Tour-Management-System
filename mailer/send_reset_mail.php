<?php
// mailer/send_reset_mail.php
// Uses PHPMailer to send the reset link to the user.
// You must download PHPMailer and put it under mailer/PHPMailer/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function send_reset_mail(string $toEmail, string $resetLink): bool
{
    $mail = new PHPMailer(true);

    try {
        // SMTP CONFIG (Gmail example)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sharath@gmail.com';     // TODO: change ## add your original gmail
        $mail->Password   = 'add_your app pass';       // TODO: change (Gmail App Password)
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender/receiver
        $mail->setFrom('yourgmail@gmail.com', 'Tour Management System');
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = '
            <p>Hi,</p>
            <p>You requested to reset your password for the Tour Management System.</p>
            <p>Click the link below to reset your password:</p>
            <p><a href="' . htmlspecialchars($resetLink) . '">' . htmlspecialchars($resetLink) . '</a></p>
            <p>This link will expire in 30 minutes.</p>
        ';
        $mail->AltBody = "Reset your password using this link: " . $resetLink;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // For debugging you can echo: $mail->ErrorInfo
        return false;
    }
}

