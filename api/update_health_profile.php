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

$height = $data['height'] ?? '';
$weight = $data['weight'] ?? '';
$bloodGroup = $data['blood_group'] ?? '';
$conditions = $data['medical_conditions'] ?? '';

try {
    $stmt = $pdo->prepare("UPDATE TRAINER_PROFILES SET height = ?, weight = ?, blood_group = ?, medical_conditions = ? WHERE user_email = ?");
    $stmt->execute([$height, $weight, $bloodGroup, $conditions, $email]);

    echo json_encode(['success' => true, 'message' => 'Health profile updated']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
