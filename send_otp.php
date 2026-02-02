<?php
// Prevent any unwanted output
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
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

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'elisreji2028@mca.ajce.in';
    $mail->Password = 'dtedqdekswzflopc';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('elisreji2028@mca.ajce.in', 'PlayMatrix');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Verify Your Email - PlayMatrix';
    $mail->Body = "
    <div style='background: #050505; color: white; padding: 40px; font-family: Outfit, sans-serif; border-radius: 20px; text-align: center;'>
        <h1 style='color: #39ff14;'>Email Verification</h1>
        <p>Use the code below to verify your email address:</p>
        <div style='background: rgba(57, 255, 20, 0.1); border: 2px solid #39ff14; color: #39ff14; font-size: 32px; font-weight: 900; letter-spacing: 10px; padding: 20px; border-radius: 12px; margin: 30px 0; display: inline-block;'>
            $otp
        </div>
        <p style='color: #a1a1a1; font-size: 14px;'>This code will expire in 10 minutes.</p>
    </div>";

    $mail->send();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Return error as JSON
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>