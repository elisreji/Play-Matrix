<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$refundable = [];

try {
    // 1. Get Paid Games joined by user
    // We assume if price contains a number and isn't "Free", it's paid.
    $stmt = $pdo->prepare("
        SELECT g.id, g.sport as title, g.price, g.game_date as date, 'Game' as type 
        FROM GAME_PARTICIPANTS gp
        JOIN GAMES g ON gp.game_id = g.id
        WHERE gp.user_email = ? AND g.price != 'Free'
    ");
    $stmt->execute([$email]);
    $games = $stmt->fetchAll();

    foreach ($games as $g) {
        $refundable[] = $g;
    }

    // 2. Get Paid Trainer Sessions / Programs
    $stmt = $pdo->prepare("
        SELECT b.id, cp.title, b.amount_paid as price, b.session_date as date, 'Trainer' as type 
        FROM TRAINER_BOOKINGS b
        JOIN COACHING_PROGRAMS cp ON b.program_id = cp.id
        WHERE b.user_email = ? AND b.payment_status = 'Paid'
    ");
    $stmt->execute([$email]);
    $trainer_bookings = $stmt->fetchAll();

    foreach ($trainer_bookings as $tb) {
        $refundable[] = $tb;
    }

    echo json_encode(['success' => true, 'refundable' => $refundable]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
