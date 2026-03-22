<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$currentUserEmail = $_SESSION['user'];

if ($pdo) {
    try {
        // Fetch User role first
        $stmtRole = $pdo->prepare("SELECT role FROM USERS WHERE email = ?");
        $stmtRole->execute([$currentUserEmail]);
        $userRole = $stmtRole->fetchColumn();

        if ($userRole === 'Admin') {
            $stmt = $pdo->prepare("SELECT email, full_name, role FROM USERS WHERE email != ? AND role IN ('Trainer', 'User') ORDER BY role DESC, full_name ASC");
            $stmt->execute([$currentUserEmail]);
        } else if ($userRole === 'Trainer') {
            $stmt = $pdo->prepare("SELECT DISTINCT u.email, u.full_name, u.role 
                                    FROM USERS u
                                    LEFT JOIN TRAINER_REQUESTS tr ON u.email = tr.user_email 
                                    WHERE u.email != ? AND (u.role = 'Admin' OR (tr.trainer_email = ? AND tr.status = 'Accepted'))
                                    ORDER BY u.role DESC, u.full_name ASC");
            $stmt->execute([$currentUserEmail, $currentUserEmail]);
        } else {
            // User
            $stmt = $pdo->prepare("SELECT DISTINCT u.email, u.full_name, u.role 
                                    FROM USERS u
                                    LEFT JOIN TRAINER_REQUESTS tr ON u.email = tr.trainer_email 
                                    WHERE u.email != ? AND (u.role = 'Admin' OR (tr.user_email = ? AND tr.status = 'Accepted'))
                                    ORDER BY u.role DESC, u.full_name ASC");
            $stmt->execute([$currentUserEmail, $currentUserEmail]);
        }

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Also fetch the last message for each user to show in the list
        foreach ($users as &$user) {
            $stmtLast = $pdo->prepare("SELECT message, created_at, sender_email 
                                      FROM MESSAGES 
                                      WHERE (sender_email = ? AND receiver_email = ?) 
                                         OR (sender_email = ? AND receiver_email = ?) 
                                      ORDER BY created_at DESC LIMIT 1");
            $stmtLast->execute([$currentUserEmail, $user['email'], $user['email'], $currentUserEmail]);
            $lastMsg = $stmtLast->fetch();
            
            if ($lastMsg) {
                $user['last_message'] = $lastMsg['message'];
                $user['last_message_time'] = $lastMsg['created_at'];
                $user['last_message_sender'] = $lastMsg['sender_email'];
            } else {
                $user['last_message'] = null;
            }

            // Count unread messages
            $stmtUnread = $pdo->prepare("SELECT COUNT(*) as unread FROM MESSAGES WHERE sender_email = ? AND receiver_email = ? AND is_read = 0");
            $stmtUnread->execute([$user['email'], $currentUserEmail]);
            $unreadCount = $stmtUnread->fetch();
            $user['unread_count'] = $unreadCount['unread'];
        }

        echo json_encode(['success' => true, 'users' => $users]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>
