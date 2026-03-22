<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$payments = [];

try {
    // 1. Get Game Payments
    $stmt = $pdo->prepare("
        SELECT gp.payment_id, g.sport as title, g.price as amount, g.game_date as date, 'Game' as type, 'Success' as status
        FROM GAME_PARTICIPANTS gp
        JOIN GAMES g ON gp.game_id = g.id
        WHERE gp.user_email = ? AND gp.payment_id IS NOT NULL
    ");
    $stmt->execute([$email]);
    $payments = array_merge($payments, $stmt->fetchAll());

    // 2. Get Trainer Payments
    $stmt = $pdo->prepare("
        SELECT b.payment_id, cp.title, b.amount_paid as amount, b.session_date as date, 'Trainer' as type, 
               CASE WHEN b.payment_status = 'Refunded' THEN 'Refunded' ELSE 'Success' END as status
        FROM TRAINER_BOOKINGS b
        JOIN COACHING_PROGRAMS cp ON b.program_id = cp.id
        WHERE b.user_email = ? AND (b.payment_id IS NOT NULL OR b.amount_paid > 0)
    ");
    $stmt->execute([$email]);
    $payments = array_merge($payments, $stmt->fetchAll());

    // Sort by date descending
    usort($payments, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    echo json_encode(['success' => true, 'payments' => $payments]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
