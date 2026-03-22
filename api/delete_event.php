<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $email = $_SESSION['user'];

    if ($id > 0) {
        // Ensure the event belongs to the current user
        $stmt = $pdo->prepare("DELETE FROM COACHING_PROGRAMS WHERE id = ? AND trainer_email = ?");
        $success = $stmt->execute([$id, $email]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete event.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid event ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
