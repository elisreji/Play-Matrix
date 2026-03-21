<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$data = json_decode(file_get_contents('php://input'), true);

$planId       = $data['planId'] ?? '';
$paymentId    = $data['razorpay_payment_id'] ?? '';
$amount       = (float)($data['amount'] ?? 0);

if (!$planId || !$paymentId) {
    echo json_encode(['success' => false, 'message' => 'Missing payment data']);
    exit;
}

// ---- Update users.json ----
$usersFile = 'users.json';
$users = json_decode(file_get_contents($usersFile), true);
$updated = false;

foreach ($users as &$u) {
    if (strtolower($u['email']) === strtolower($email)) {
        $u['plan']       = $planId;
        $u['payment_id'] = $paymentId;
        $updated = true;
        break;
    }
}

if (!$updated) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

// ---- Record payment in DB ----
require_once 'db_connect.php';
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $dbUser = $stmt->fetch();
        if ($dbUser) {
            $stmt = $pdo->prepare(
                "INSERT INTO PAYMENTS (user_id, amount, payment_method, payment_status, paid_at)
                 VALUES (?, ?, 'Razorpay', 'Success', NOW())"
            );
            $stmt->execute([$dbUser['user_id'], $amount]);
        }
    } catch (Exception $e) {
        // Non-fatal, continue
    }
}

echo json_encode(['success' => true, 'message' => 'Plan upgraded successfully']);
