<?php
session_start();
require_once '../db_connect.php';

$type = $_GET['type'] ?? '';
$target_id = $_GET['target_id'] ?? '';

if (!$type || !$target_id) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    $reviews = [];
    $stmt = null; // Initialize $stmt to null

    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    switch ($type) {
        case 'trainer':
            $stmt = $pdo->prepare("SELECT rating, comment, user_email, created_at FROM TRAINER_REVIEWS WHERE trainer_email = ? ORDER BY created_at DESC");
            $stmt->execute([$target_id]);
            $reviews = $stmt->fetchAll();
            break;
        case 'venue':
            $stmt = $pdo->prepare("SELECT rating, comment, user_email, created_at FROM VENUE_REVIEWS WHERE venue_id = ? ORDER BY created_at DESC");
            $stmt->execute([$target_id]);
            $reviews = $stmt->fetchAll();
            break;
        case 'program':
            $stmt = $pdo->prepare("SELECT rating, comment, user_email, created_at FROM PROGRAM_REVIEWS WHERE program_id = ? ORDER BY created_at DESC");
            $stmt->execute([$target_id]);
            $reviews = $stmt->fetchAll();
            break;
        case 'user':
            $stmt = $pdo->prepare("
                (SELECT CAST('Trainer' AS CHAR) COLLATE utf8mb4_unicode_ci as type, CAST(trainer_email AS CHAR) COLLATE utf8mb4_unicode_ci as target, rating, comment, created_at FROM TRAINER_REVIEWS WHERE user_email = ?)
                UNION
                (SELECT CAST('Venue' AS CHAR) COLLATE utf8mb4_unicode_ci as type, CAST(venue_id AS CHAR) COLLATE utf8mb4_unicode_ci as target, rating, comment, created_at FROM VENUE_REVIEWS WHERE user_email = ?)
                UNION
                (SELECT CAST('Program' AS CHAR) COLLATE utf8mb4_unicode_ci as type, CAST(program_id AS CHAR) COLLATE utf8mb4_unicode_ci as target, rating, comment, created_at FROM PROGRAM_REVIEWS WHERE user_email = ?)
                ORDER BY created_at DESC
            ");
            $stmt->execute([$target_id, $target_id, $target_id]);
            $reviews = $stmt->fetchAll();
            break;
        default:
            die(json_encode(['success' => false, 'message' => 'Invalid review type']));
    }


    // Calculate Average Rating
    $total_rating = 0;
    foreach ($reviews as $row) {
        $total_rating += $row['rating'];
    }
    $average_rating = count($reviews) > 0 ? round($total_rating / count($reviews), 1) : 0;

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'average_rating' => $average_rating,
        'review_count' => count($reviews)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
