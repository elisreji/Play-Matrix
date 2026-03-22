<?php
require_once '../db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$user_email = $_SESSION['user'] ?? null;
if (!$user_email) {
    die(json_encode(['success' => false, 'message' => 'Please login to submit a review']));
}

$type = $_POST['type'] ?? ''; // 'trainer', 'venue', 'program'
$target_id = $_POST['target_id'] ?? '';
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$type || !$target_id || $rating < 1 || $rating > 5) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    switch ($type) {
        case 'trainer':
            // Verify booking
            $check = $pdo->prepare("SELECT 1 FROM TRAINER_BOOKINGS WHERE user_email = ? AND trainer_email = ? LIMIT 1");
            $check->execute([$user_email, $target_id]);
            if (!$check->fetch()) {
                die(json_encode(['success' => false, 'message' => 'You can only review trainers you have booked sessions with.']));
            }
            $stmt = $pdo->prepare("INSERT INTO TRAINER_REVIEWS (trainer_email, user_email, rating, comment) VALUES (?, ?, ?, ?)");
            break;
        case 'venue':
            // Verify booking
            $check = $pdo->prepare("SELECT 1 FROM VENUE_BOOKINGS WHERE user_email = ? AND venue_id = ? LIMIT 1");
            $check->execute([$user_email, $target_id]);
            if (!$check->fetch()) {
                die(json_encode(['success' => false, 'message' => 'You can only review venues you have previously booked.']));
            }
            $stmt = $pdo->prepare("INSERT INTO VENUE_REVIEWS (venue_id, user_email, rating, comment) VALUES (?, ?, ?, ?)");
            break;
        case 'program':
            // Verify booking
            $check = $pdo->prepare("SELECT 1 FROM TRAINER_BOOKINGS WHERE user_email = ? AND program_id = ? LIMIT 1");
            $check->execute([$user_email, $target_id]);
            if (!$check->fetch()) {
                die(json_encode(['success' => false, 'message' => 'You can only review programs you have enrolled in.']));
            }
            $stmt = $pdo->prepare("INSERT INTO PROGRAM_REVIEWS (program_id, user_email, rating, comment) VALUES (?, ?, ?, ?)");
            break;
        default:
            die(json_encode(['success' => false, 'message' => 'Invalid review type']));
    }

    $stmt->execute([$target_id, $user_email, $rating, $comment]);
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
