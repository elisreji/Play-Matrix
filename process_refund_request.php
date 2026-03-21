<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['payment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$email = $_SESSION['user'];
$paymentId = $data['payment_id'];
$description = $data['description'] ?? 'Refund Request';
$amount = $data['amount'] ?? '0.00';

// Get user name from users.json
$users = json_decode(file_get_contents('users.json'), true);
$userName = 'Unknown User';
foreach ($users as $u) {
    if ($u['email'] === $email) {
        $userName = $u['name'] ?? 'Player';
        break;
    }
}

// Add to disputes.json
$disputesFile = 'disputes.json';
$disputes = [];
if (file_exists($disputesFile)) {
    $disputes = json_decode(file_get_contents($disputesFile), true);
}

// Check for duplicates
foreach ($disputes as $d) {
    if (isset($d['payment_id']) && $d['payment_id'] === $paymentId && $d['status'] === 'Pending') {
        echo json_encode(['success' => false, 'message' => 'A refund request for this payment is already pending.']);
        exit;
    }
}

$newDispute = [
    'id' => 'REF-' . strtoupper(substr(md5(time() . $paymentId), 0, 5)),
    'reported_by' => $userName,
    'user_email' => $email,
    'payment_id' => $paymentId,
    'category' => 'Refund Request',
    'priority' => 'High',
    'status' => 'Pending',
    'description' => "Refund requested for: $description (Amount: ₹$amount)",
    'created_at' => date('Y-m-d H:i:s')
];

$disputes[] = $newDispute;
file_put_contents($disputesFile, json_encode($disputes, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Refund request submitted successfully! Our team will review it.']);
