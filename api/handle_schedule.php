<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

try {
    // Basic schedule update: clear and re-insert for the given trainer
    // In a real app, you might want to update specific entries
    $pdo->beginTransaction();

    // Clear old schedule
    $stmt = $pdo->prepare("DELETE FROM TRAINER_SCHEDULE WHERE trainer_email = ?");
    $stmt->execute([$email]);

    foreach ($data['schedule'] as $item) {
        if (isset($item['active']) && $item['active']) {
            $stmt = $pdo->prepare("INSERT INTO TRAINER_SCHEDULE (trainer_email, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $item['day'], $item['start'], $item['end']]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
