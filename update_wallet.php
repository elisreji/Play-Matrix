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
$amount = floatval($data['amount'] ?? 0);
$payment_id = $data['payment_id'] ?? '';
$email = $_SESSION['user'];

if ($amount <= 0 || empty($payment_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// SECURE VERIFICATION with SDK
if (RAZORPAY_TEST_SIMULATION !== true) {
    try {
        $api = getRazorpayApi();
        $payment = $api->payment->fetch($payment_id);
        if (!($payment->status === 'captured' || $payment->status === 'authorized')) {
            echo json_encode(['success' => false, 'message' => 'Payment not successful: ' . $payment->status]);
            exit;
        }
        // Amount verification - Note: Razorpay uses paise
        if (round($payment->amount / 100) != round($amount)) {
            echo json_encode(['success' => false, 'message' => 'Amount mismatch']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Razorpay Error: ' . $e->getMessage()]);
        exit;
    }
}

if ($pdo) {
    try {
        // 1. Update Database
        $stmt = $pdo->prepare("UPDATE USERS SET wallet_balance = wallet_balance + ? WHERE email = ?");
        $stmt->execute([$amount, $email]);

        // 2. Log Payment
        $stmt = $pdo->prepare("INSERT INTO PAYMENTS (user_id, amount, payment_method, payment_status, paid_at) 
                              SELECT user_id, ?, 'Card', 'Success', NOW() FROM USERS WHERE email = ?");
        $stmt->execute([$amount, $email]);

        // 3. Get New Balance
        $stmt = $pdo->prepare("SELECT wallet_balance FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $new_balance = $stmt->fetchColumn();

        // 4. Update JSON fallback if needed
        $file = 'users.json';
        if (file_exists($file)) {
            $users = json_decode(file_get_contents($file), true);
            foreach ($users as &$u) {
                if ($u['email'] === $email) {
                    $u['wallet_balance'] = ($u['wallet_balance'] ?? 0) + $amount;
                    break;
                }
            }
            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
        }

        echo json_encode(['success' => true, 'new_balance' => $new_balance]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
}
?>
