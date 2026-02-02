<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$otp = $data['otp'] ?? '';
$email = strtolower(trim($data['email'] ?? ''));

if (
    isset($_SESSION['verification_otp']) &&
    (string) $_SESSION['verification_otp'] === (string) $otp &&
    isset($_SESSION['verification_email']) &&
    $_SESSION['verification_email'] === $email
) {
    // Verified
    $_SESSION['is_email_verified'] = true;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
}
?>