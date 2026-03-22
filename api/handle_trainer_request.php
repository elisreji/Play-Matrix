<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$requestId = $data['requestId'] ?? null;
$action = $data['action'] ?? ''; // 'Accepted' or 'Rejected'
$trainerEmail = $_SESSION['user'];

if (!$requestId || !in_array($action, ['Accepted', 'Rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

if ($pdo) {
    try {
        // Verify this request belongs to this trainer
        $stmt = $pdo->prepare("SELECT trainer_email FROM TRAINER_REQUESTS WHERE id = ?");
        $stmt->execute([$requestId]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => "Request ID $requestId not found."]);
            exit;
        }

        $dbTrainerEmail = strtolower(trim($row['trainer_email']));
        $sessionTrainerEmail = strtolower(trim($trainerEmail));

        if ($dbTrainerEmail !== $sessionTrainerEmail) {
            echo json_encode([
                'success' => false,
                'message' => "Unauthorized: This request (ID $requestId) belongs to trainer '$dbTrainerEmail', but you are logged in as '$sessionTrainerEmail'. Please ensure you are using the correct trainer account."
            ]);
            exit;
        }

        // Update status
        $stmt = $pdo->prepare("UPDATE TRAINER_REQUESTS SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$action, $requestId]);

        // Feedback to the student (Notification)
        if ($action === 'Accepted') {
            $stmt = $pdo->prepare("SELECT user_email, trainer_email FROM TRAINER_REQUESTS WHERE id = ?");
            $stmt->execute([$requestId]);
            $reqData = $stmt->fetch();

            if ($reqData) {
                // Get trainer's name for the message
                $stmt = $pdo->prepare("SELECT full_name FROM USERS WHERE email = ?");
                $stmt->execute([$reqData['trainer_email']]);
                $trainerName = $stmt->fetchColumn() ?: 'A trainer';

                $msg = "Congratulations! $trainerName has accepted your training request. You can now book sessions with them.";
                $stmt = $pdo->prepare("INSERT INTO USER_NOTIFICATIONS (user_email, message) VALUES (?, ?)");
                $stmt->execute([$reqData['user_email'], $msg]);
            }
        }

        echo json_encode(['success' => true, 'message' => "Request " . strtolower($action) . " successfully!"]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
