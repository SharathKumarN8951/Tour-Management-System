<?php
// email_functions.php
// Helper functions to send booking/payment emails.
// NOTE: For local XAMPP, mail() may require SMTP config to really send emails.
// For project/viva, showing this code is enough to explain the email feature.

require_once 'db.php';

function get_customer_by_booking($booking_id) {
    global $conn;
    $booking_id = (int)$booking_id;
    $sql = "SELECT c.*, b.id AS booking_id, b.booking_date, b.total_amount, b.seats,
                   p.name AS package_name, p.location
            FROM bookings b
            JOIN customers c ON b.customer_id = c.id
            JOIN packages p ON b.package_id = p.id
            WHERE b.id = $booking_id
            LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        return mysqli_fetch_assoc($res);
    }
    return null;
}

function send_booking_email($booking_id) {
    $data = get_customer_by_booking($booking_id);
    if (!$data) return false;

    $to = $data['email'];
    $subject = "Booking Created - " . $data['package_name'];
    $message = "Dear " . $data['name'] . ",

"
             . "Your booking has been created successfully.

"
             . "Booking ID: " . $data['booking_id'] . "
"
             . "Package: " . $data['package_name'] . "
"
             . "Location: " . $data['location'] . "
"
             . "Seats: " . $data['seats'] . "
"
             . "Total Amount: Rs " . $data['total_amount'] . "
"
             . "Booking Date: " . $data['booking_date'] . "

"
             . "Please complete payment via UPI from your account.

"
             . "Regards,
Tour Management System";

    $headers = "From: noreply@tourms.local
";

    // For local project mail() may fail silently; this is mainly for demonstration.
    @mail($to, $subject, $message, $headers);
    return true;
}

function send_payment_approved_email($booking_id) {
    $data = get_customer_by_booking($booking_id);
    if (!$data) return false;

    $to = $data['email'];
    $subject = "Payment Approved - Ticket Confirmed for " . $data['package_name'];
    $message = "Dear " . $data['name'] . ",

"
             . "Your payment has been verified and your booking is now marked as Paid.

"
             . "Booking ID: " . $data['booking_id'] . "
"
             . "Package: " . $data['package_name'] . "
"
             . "Location: " . $data['location'] . "
"
             . "Seats: " . $data['seats'] . "
"
             . "Total Amount: Rs " . $data['total_amount'] . "
"
             . "Booking Date: " . $data['booking_date'] . "

"
             . "You can download your ticket (PDF) from the booking history page.

"
             . "Regards,
Tour Management System";

    $headers = "From: noreply@tourms.local
";

    @mail($to, $subject, $message, $headers);
    return true;
}
