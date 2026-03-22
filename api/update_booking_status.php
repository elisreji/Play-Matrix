<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

$bookingId = $data['bookingId'] ?? 0;
$status = $data['status'] ?? '';

try {
    $stmt = $pdo->prepare("UPDATE TRAINER_BOOKINGS SET status = ? WHERE id = ? AND trainer_email = ?");
    $stmt->execute([$status, $bookingId, $email]);

    echo json_encode(['success' => true, 'message' => 'Booking updated to ' . $status]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
