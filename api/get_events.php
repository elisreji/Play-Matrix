<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Fetch future events
    $stmt = $pdo->prepare("SELECT cp.*, u.full_name as trainer_name 
                           FROM COACHING_PROGRAMS cp 
                           JOIN USERS u ON cp.trainer_email = u.email 
                           WHERE cp.event_date >= CURDATE() 
                           ORDER BY cp.event_date ASC, cp.event_time ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'events' => $events]);
}
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
