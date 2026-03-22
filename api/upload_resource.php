<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];

if (isset($_FILES['resource'])) {
    $file = $_FILES['resource'];
    $title = $_POST['title'] ?? 'New Resource';
    $type = $_POST['type'] ?? 'Workout Plan';

    $uploadDir = 'uploads/resources/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO TRAINER_RESOURCES (trainer_email, title, type, file_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $title, $type, $targetPath]);
            echo json_encode(['success' => true, 'message' => 'Resource uploaded successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?>
