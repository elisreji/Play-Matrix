<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];

// Switch to $_POST since we are using FormData for file uploads
$action = $_POST['action'] ?? '';

try {
    if ($action === 'create') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0.00;
        $type = $_POST['type'] ?? 'Fitness';
        $location = $_POST['location'] ?? 'Online';
        $is_tournament = isset($_POST['is_tournament']) ? (int)$_POST['is_tournament'] : 0;
        
        // Date & Times
        $event_date = $_POST['event_date'] ?? null;
        $event_time = $_POST['event_time'] ?? null;
        $event_end_time = $_POST['event_end_time'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $registration_deadline = $_POST['registration_deadline'] ?? null;
        
        // Tournament Specifics
        $max_participants = $_POST['max_participants'] ?? 100;
        $tournament_format = $_POST['tournament_format'] ?? 'Knockout';
        $prize_details = $_POST['prize_details'] ?? '';
        $contact_info = $_POST['contact_info'] ?? '';
        
        // Handle Banner Upload
        $banner_path = '';
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/tournaments/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_name = uniqid() . '_' . basename($_FILES['banner']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['banner']['tmp_name'], $target_file)) {
                $banner_path = 'uploads/tournaments/' . $file_name;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO COACHING_PROGRAMS 
            (trainer_email, title, description, price, type, location, event_date, event_time, event_end_time, 
             start_date, end_date, registration_deadline, max_participants, tournament_format, prize_details, 
             contact_info, banner_path, is_tournament, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Published')");
            
        $stmt->execute([
            $email, $title, $description, $price, $type, $location, $event_date, $event_time, $event_end_time,
            $start_date, $end_date, $registration_deadline, $max_participants, $tournament_format, $prize_details,
            $contact_info, $banner_path, $is_tournament
        ]);

        $newId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Tournament/Event created successfully', 'id' => $newId]);
        
    } elseif ($action === 'delete') {
        // For delete, we might still get JSON or POST
        $rawData = json_decode(file_get_contents('php://input'), true);
        $id = $_POST['id'] ?? ($rawData['id'] ?? 0);
        
        $stmt = $pdo->prepare("DELETE FROM COACHING_PROGRAMS WHERE id = ? AND trainer_email = ?");
        $stmt->execute([$id, $email]);
        echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
