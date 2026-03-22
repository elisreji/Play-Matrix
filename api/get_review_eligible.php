<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
session_start();

$user_email = $_SESSION['user'] ?? null;
if (!$user_email) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

try {
    $eligible = [
        'venues' => [],
        'trainers' => [],
        'programs' => []
    ];

    // Venues
    $stmt = $pdo->prepare("
        SELECT DISTINCT vb.venue_id as id, COALESCE(v.venue_name, vb.venue_id) as name 
        FROM VENUE_BOOKINGS vb
        LEFT JOIN VENUES v ON vb.venue_id = v.venue_id
        WHERE vb.user_email = ?
    ");
    $stmt->execute([$user_email]);
    $eligible['venues'] = $stmt->fetchAll();

    // Trainers
    $stmt = $pdo->prepare("
        SELECT DISTINCT tb.trainer_email as id, COALESCE(u.full_name, tb.trainer_email) as name 
        FROM TRAINER_BOOKINGS tb
        LEFT JOIN USERS u ON tb.trainer_email = u.email
        WHERE tb.user_email = ?
    ");
    $stmt->execute([$user_email]);
    $eligible['trainers'] = $stmt->fetchAll();

    // Programs
    $stmt = $pdo->prepare("
        SELECT DISTINCT tb.program_id as id, cp.title as name 
        FROM TRAINER_BOOKINGS tb
        JOIN COACHING_PROGRAMS cp ON tb.program_id = cp.id
        WHERE tb.user_email = ? AND tb.program_id IS NOT NULL
    ");
    $stmt->execute([$user_email]);
    $eligible['programs'] = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'eligible' => $eligible
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
