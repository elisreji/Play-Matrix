<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$currentUserEmail = $_SESSION['user'];
$otherUserEmail = $_GET['email'] ?? null;

if (!$otherUserEmail) {
    echo json_encode(['success' => false, 'message' => 'Recipient email missing']);
    exit;
}

if ($pdo) {
    try {
        // Mark messages as read
        $stmtRead = $pdo->prepare("UPDATE MESSAGES SET is_read = 1 WHERE sender_email = ? AND receiver_email = ?");
        $stmtRead->execute([$otherUserEmail, $currentUserEmail]);

        // Fetch messages between these two users
        $stmt = $pdo->prepare("SELECT * FROM MESSAGES 
                               WHERE (sender_email = ? AND receiver_email = ?) 
                                  OR (sender_email = ? AND receiver_email = ?) 
                               ORDER BY created_at ASC");
        $stmt->execute([$currentUserEmail, $otherUserEmail, $otherUserEmail, $currentUserEmail]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'messages' => $messages]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
