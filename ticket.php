<?php
// ticket.php - force download PDF ticket for a Paid booking

session_start();
require 'db.php';
require_once 'fpdf.php'; // fpdf.php must be in the same folder

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];
$booking_id  = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    die("Invalid booking.");
}

// Fetch booking + package + customer + latest payment info
$sql = "
    SELECT 
        b.*, 
        p.name AS package_name, 
        p.location, 
        p.days,
        c.name AS customer_name,
        c.email AS customer_email,
        c.phone AS customer_phone,
        (
            SELECT transaction_id 
            FROM payments 
            WHERE booking_id = b.id 
            ORDER BY created_at DESC LIMIT 1
        ) AS payment_ref,
        (
            SELECT status
            FROM payments
            WHERE booking_id = b.id
            ORDER BY created_at DESC LIMIT 1
        ) AS payment_status,
        (
            SELECT created_at
            FROM payments
            WHERE booking_id = b.id
            ORDER BY created_at DESC LIMIT 1
        ) AS payment_time
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    JOIN customers c ON b.customer_id = c.id
    WHERE b.id = $booking_id AND b.customer_id = $customer_id
    LIMIT 1
";

$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) === 0) {
    die("Booking not found.");
}

$data = mysqli_fetch_assoc($res);

// Allow ticket only if booking is Paid
if ($data['status'] !== 'Paid') {
    die("Ticket is available only after payment is approved (status = Paid).");
}

// --------------- Generate PDF using FPDF ---------------
$pdf = new FPDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'Tour Booking Ticket / Invoice', 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Tour Management System - Demo Project', 0, 1, 'C');
$pdf->Ln(8);

// Booking details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Booking Details', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(90, 6, 'Booking ID: ' . $data['id'], 0, 0);
$pdf->Cell(90, 6, 'Booking Date: ' . $data['booking_date'], 0, 1);
$pdf->Cell(90, 6, 'Status: ' . $data['status'], 0, 1);
$pdf->Ln(4);

// Customer details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Customer Details', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(90, 6, 'Name: ' . $data['customer_name'], 0, 0);
$pdf->Cell(90, 6, 'Email: ' . $data['customer_email'], 0, 1);
$pdf->Cell(90, 6, 'Phone: ' . ($data['customer_phone'] ?? 'N/A'), 0, 1);
$pdf->Ln(4);

// Package info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Package Details', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Package: ' . $data['package_name'], 0, 1);
$pdf->Cell(0, 6, 'Location: ' . $data['location'], 0, 1);
$pdf->Cell(0, 6, 'Duration: ' . $data['days'] . ' days', 0, 1);
$pdf->Ln(2);

// Amount summary
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Payment Summary', 0, 1);

$pdf->SetFont('Arial', '', 11);
$seats = max((int)$data['seats'], 1);
$pricePerSeat = $seats > 0 ? $data['total_amount'] / $seats : $data['total_amount'];

$pdf->Cell(90, 6, 'Seats: ' . $seats, 0, 0);
$pdf->Cell(90, 6, 'Price per Seat: Rs ' . number_format($pricePerSeat, 2), 0, 1);
$pdf->Cell(90, 6, 'Total Amount: Rs ' . number_format($data['total_amount'], 2), 0, 1);

if (!empty($data['payment_ref'])) {
    $pdf->Cell(0, 6, 'Payment Ref ID: ' . $data['payment_ref'], 0, 1);
}
if (!empty($data['payment_time'])) {
    $pdf->Cell(0, 6, 'Payment Time: ' . $data['payment_time'], 0, 1);
}
if (!empty($data['payment_status'])) {
    $pdf->Cell(0, 6, 'Payment Status: ' . $data['payment_status'], 0, 1);
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5,
    "Note: This ticket is generated for academic project demonstration.\n" .
    "In a real system, extra travel details and QR codes can be added."
);

// Force download
$filename = 'ticket_' . $booking_id . '.pdf';
$pdf->Output('D', $filename); // 'D' = download
exit;
