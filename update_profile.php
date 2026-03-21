<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$email_notif = isset($data['email_notif']) ? (int)$data['email_notif'] : 1;
$sms_notif = isset($data['sms_notif']) ? (int)$data['sms_notif'] : 0;
$email = $_SESSION['user'];

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
    exit;
}

if ($pdo) {
    try {
        // 1. Update Database
        $stmt = $pdo->prepare("UPDATE USERS SET full_name = ?, phone = ?, email_notif = ?, sms_notif = ? WHERE email = ?");
        $stmt->execute([$name, $phone, $email_notif, $sms_notif, $email]);

        // 2. Update JSON fallback
        $file = 'users.json';
        if (file_exists($file)) {
            $users = json_decode(file_get_contents($file), true);
            foreach ($users as &$u) {
                if ($u['email'] === $email) {
                    $u['name'] = $name;
                    $u['phone'] = $phone;
                    $u['email_notif'] = $email_notif;
                    $u['sms_notif'] = $sms_notif;
                    break;
                }
            }
            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
        }

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
