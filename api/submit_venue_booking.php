<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];

// For simplicity, we assume one booking from cart for this example
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['venue_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    // Ensure payment_id column exists
    try {
        $pdo->exec("ALTER TABLE VENUE_BOOKINGS ADD COLUMN IF NOT EXISTS payment_id VARCHAR(100) NULL");
    } catch (Exception $e) {
        // Column might already exist or DB doesn't support IF NOT EXISTS on ALTER
    }

    $stmt = $pdo->prepare("
        INSERT INTO VENUE_BOOKINGS (user_email, venue_id, booking_date, booking_time, amount_paid, status, payment_id)
        VALUES (?, ?, ?, ?, ?, 'Paid', ?)
    ");
    
    // Convert format if needed. 4.php sends dates like "10, March 2026"
    $dateStr = $data['date'];
    $bookingDate = date('Y-m-d', strtotime($dateStr));
    
    // Time range is "10:00 AM to 11:00 AM"
    $timeRange = $data['timeRange'];
    $startTimeStr = explode(' to ', $timeRange)[0];
    $bookingTime = date('H:i:s', strtotime($startTimeStr));

    $stmt->execute([
        $email,
        $data['venue_id'],
        $bookingDate,
        $bookingTime,
        $data['price'],
        $data['payment_id'] ?? null
    ]);

    echo json_encode(['success' => true, 'message' => 'Booking saved']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
