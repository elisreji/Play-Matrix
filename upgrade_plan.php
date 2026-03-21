<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$data = json_decode(file_get_contents('php://input'), true);
$newPlan = $data['planId'] ?? '';
$price = (float)($data['price'] ?? 0);

if (!$newPlan) {
    echo json_encode(['success' => false, 'message' => 'Invalid plan']);
    exit;
}

$usersFile = 'users.json';
$users = json_decode(file_get_contents($usersFile), true);
$userFound = false;

foreach ($users as &$u) {
    if (strtolower($u['email']) === strtolower($email)) {
        $userFound = true;
        $currentBalance = (float)($u['wallet_balance'] ?? 0);

        if ($currentBalance < $price) {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance. You need ₹' . number_format($price - $currentBalance, 2) . ' more.']);
            exit;
        }

        // Deduct balance and update plan
        $u['wallet_balance'] = $currentBalance - $price;
        $u['plan'] = $newPlan;

        // Optionally record a payment in DB
        require_once 'db_connect.php';
        if ($pdo) {
            try {
                // Fetch user_id from DB
                $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
                $stmt->execute([$email]);
                $dbUser = $stmt->fetch();
                
                if ($dbUser) {
                    $userId = $dbUser['user_id'];
                    $stmt = $pdo->prepare("INSERT INTO PAYMENTS (user_id, amount, payment_method, payment_status, paid_at) VALUES (?, ?, 'Matrix Wallet', 'Success', NOW())");
                    $stmt->execute([$userId, $price]);
                }
            } catch (Exception $e) {}
        }
        break;
    }
}

if ($userFound) {
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'message' => 'Plan upgraded successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
