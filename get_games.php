<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

$email = $_SESSION['user'] ?? '';

try {
    // Fetch all games
    $stmt = $pdo->query("SELECT * FROM GAMES ORDER BY game_date DESC, start_time DESC");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch games joined by the current user
    $joinedIds = [];
    if ($email) {
        $stmtJoined = $pdo->prepare("SELECT game_id FROM GAME_PARTICIPANTS WHERE user_email = ?");
        $stmtJoined->execute([$email]);
        $joinedIds = $stmtJoined->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    echo json_encode([
        'success' => true,
        'games' => $games,
        'joinedIds' => $joinedIds
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>