<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userEmail = $_SESSION['user'];
$trainerEmail = $data['trainerEmail'] ?? '';

if (empty($trainerEmail)) {
    echo json_encode(['success' => false, 'message' => 'Trainer email is required']);
    exit;
}

if ($pdo) {
    try {
        // Check if already requested
        $stmt = $pdo->prepare("SELECT id FROM TRAINER_REQUESTS WHERE user_email = ? AND trainer_email = ?");
        $stmt->execute([$userEmail, $trainerEmail]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Request already exists.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO TRAINER_REQUESTS (user_email, trainer_email, status) VALUES (?, ?, 'Pending')");
        $stmt->execute([$userEmail, $trainerEmail]);

        echo json_encode(['success' => true, 'message' => 'Trainer request sent successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
