<?php
// Prevent any unwanted output
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include 'db_connect.php';

// Clean buffer before sending headers
ob_clean();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON input');
    }

    $email = strtolower(trim($data['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    // Check if email already exists
    if ($pdo) {
        // ERROR FIX: Changed 'id' to 'user_id' to match database schema
        $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
    } else {
        // Fallback to JSON check
        $file = 'users.json';
        if (file_exists($file)) {
            $users = json_decode(file_get_contents($file), true);
            if (is_array($users)) {
                foreach ($users as $user) {
                    if (strtolower(trim($user['email'])) === $email) {
                        echo json_encode(['success' => false, 'message' => 'Email already registered']);
                        exit;
                    }
                }
            }
        }
    }

    $otp = rand(100000, 999999);
    $_SESSION['verification_otp'] = $otp;
    $_SESSION['verification_email'] = $email;

    // Return success immediately, with OTP to auto-fill
    echo json_encode([
        'success' => true, 
        'otp' => $otp
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>