<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];

try {
    $stmt = $pdo->prepare("SELECT * FROM REFUND_REQUESTS WHERE user_email = ? ORDER BY created_at DESC");
    $stmt->execute([$email]);
    $refunds = $stmt->fetchAll();

    echo json_encode(['success' => true, 'refunds' => $refunds]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
