<?php
header('Content-Type: application/json');
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        // Fallback for form data if not JSON
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
    } else {
        $category = $data['category'] ?? '';
        $description = $data['description'] ?? '';
    }

    $email = $_SESSION['user'];

    if (empty($category) || empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all fields.']);
        exit;
    }

    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Once you start MySQL in XAMPP, this will work.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO COMPLAINTS (user_email, category, description) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $category, $description])) {
            echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully! Redirecting to Admin Panel...']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit task to database.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}
?>
