<?php
session_start();
header('Content-Type: application/json');
require_once '../db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'No email provided']);
    exit;
}

// 1. Check DB
$userFound = null;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ?");
    $stmt->execute([$email]);
    $userFound = $stmt->fetch();
}

// 2. Check JSON
if (!$userFound) {
    $users = json_decode(file_get_contents('users.json'), true);
    foreach ($users as $u) {
        if ($u['email'] === $email) {
            $userFound = $u;
            break;
        }
    }
}

$redirect = 'dashboard.php';
if ($userFound) {
    // Set Session
    $_SESSION['user'] = $email;

    // Check Role
    if (isset($userFound['role'])) {
        if ($userFound['role'] === 'Trainer') {
            $redirect = 'trainer_dashboard.php';
        } elseif ($userFound['role'] === 'Admin') {
            $redirect = 'admin.php';
        }
    }

    // Check if blocked
    if (isset($userFound['is_blocked']) && $userFound['is_blocked']) {
        echo json_encode(['success' => false, 'message' => 'Account blocked']);
        exit;
    }

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} else {
    // If user doesn't exist, we might want to register them or redirect to registration
    // For now, let's redirect to dashboard which might handle "new user" logic or register.php
    // But usually Google Sign In implies registration if not exists.
    // Let's assume for now we just redirect to dashboard (or register)
    $_SESSION['user'] = $email; // strict login
    echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
}
?>
