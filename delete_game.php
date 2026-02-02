<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $gameId = $data['gameId'] ?? 0;

    // Get host name of current user to verify ownership
    $email = $_SESSION['user'];
    $users = json_decode(file_get_contents('users.json'), true);
    $currentUserName = '';
    foreach ($users as $u) {
        if ($u['email'] === $email) {
            $currentUserName = $u['name'] ?? '';
            break;
        }
    }

    if (!$gameId) {
        echo json_encode(['success' => false, 'message' => 'Invalid Game ID.']);
        exit;
    }

    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT host_name FROM GAMES WHERE id = ?");
        $stmt->execute([$gameId]);
        $game = $stmt->fetch();

        if (!$game) {
            echo json_encode(['success' => false, 'message' => 'Game not found.']);
            exit;
        }

        if ($game['host_name'] !== $currentUserName) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this game.']);
            exit;
        }

        // Delete the game (foreign keys should handle participants if ON DELETE CASCADE is set, else delete manually)
        // From my earlier init_game_participants.php, I used ON DELETE CASCADE.
        $delete = $pdo->prepare("DELETE FROM GAMES WHERE id = ?");
        $result = $delete->execute([$gameId]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Game deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete game.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>