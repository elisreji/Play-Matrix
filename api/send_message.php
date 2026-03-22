<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$currentUserEmail = $_SESSION['user'];
$data = json_decode(file_get_contents('php://input'), true);

$receiverEmail = $data['receiver_email'] ?? null;
$message = $data['message'] ?? null;

if (!$receiverEmail || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

if ($pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO MESSAGES (sender_email, receiver_email, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$currentUserEmail, $receiverEmail, $message])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
