<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$email = $_SESSION['user'];

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Both passwords are required']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
    exit;
}

if ($pdo) {
    try {
        // 1. Get current password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current_password, $hash)) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }

        // 2. Hash new password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // 3. Update Database
        $stmt = $pdo->prepare("UPDATE USERS SET password_hash = ? WHERE email = ?");
        $stmt->execute([$new_hash, $email]);

        // 4. Update JSON fallback
        $file = 'users.json';
        if (file_exists($file)) {
            $users = json_decode(file_get_contents($file), true);
            foreach ($users as &$u) {
                if ($u['email'] === $email) {
                    $u['password'] = $new_hash;
                    break;
                }
            }
            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
        }

        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
