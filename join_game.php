<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to join games.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $gameId = $data['gameId'] ?? 0;
    $email = $_SESSION['user'];

    if (!$gameId) {
        echo json_encode(['success' => false, 'message' => 'Invalid Game ID.']);
        exit;
    }

    try {
        // Check if already joined
        $check = $pdo->prepare("SELECT id FROM GAME_PARTICIPANTS WHERE game_id = ? AND user_email = ?");
        $check->execute([$gameId, $email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already joined this game.']);
            exit;
        }

        // Insert participation
        $stmt = $pdo->prepare("INSERT INTO GAME_PARTICIPANTS (game_id, user_email) VALUES (?, ?)");
        $result = $stmt->execute([$gameId, $email]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Successfully joined the game!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to join game.']);
        }
    } catch (PDOException $e) {
        // Handle duplicate entry if check fails
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'You have already joined this game.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>