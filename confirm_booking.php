<?php
session_start();
require_once 'db_connect.php';
require_once 'razorpay_config.php';
use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$payment_id = $data['payment_id'] ?? '';
$cart = $data['cart'] ?? [];
$email = $_SESSION['user'];

if (empty($payment_id) || empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// 1. Verify Payment with Razorpay SDK
if (RAZORPAY_TEST_SIMULATION !== true) {
    try {
        $api = getRazorpayApi();
        $payment = $api->payment->fetch($payment_id);
        if (!($payment->status === 'captured' || $payment->status === 'authorized')) {
            echo json_encode(['success' => false, 'message' => 'Payment failed status check']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Verification failed']);
        exit;
    }
}

// 2. Process Bookings in DB
if ($pdo) {
    try {
        $pdo->beginTransaction();
        
        $stmt_user = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
        $stmt_user->execute([$email]);
        $user_id = $stmt_user->fetchColumn();

        foreach ($cart as $item) {
            $venue = $item['venue'];
            $sport = $item['court'];
            $dateSelect = date('Y-m-d', strtotime($item['date']));
            $timeRange = $item['timeRange'];
            $price = $item['price'];

            // Insert into BOOKINGS (simplified - venue_id is mock 1 for now)
            $stmt = $pdo->prepare("INSERT INTO BOOKINGS (user_id, venue_id, booking_date, time_slot, booking_status) 
                                 VALUES (?, 1, ?, ?, 'Confirmed')");
            $stmt->execute([$user_id, $dateSelect, $timeRange]);
            $booking_id = $pdo->lastInsertId();

            // Insert into PAYMENTS history
            $stmt_p = $pdo->prepare("INSERT INTO PAYMENTS (user_id, booking_id, amount, payment_method, payment_status, paid_at) 
                                   VALUES (?, ?, ?, 'Card', 'Success', NOW())");
            $stmt_p->execute([$user_id, $booking_id, $price]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'DB down']);
}
?>
