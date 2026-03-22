<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];

$bookingId = $_POST['bookingId'] ?? null;
$bookingType = $_POST['bookingType'] ?? null;
$amount = $_POST['amount'] ?? null;
$reason_detail = $_POST['reason'] ?? '';
$reason_category = $_POST['reason_category'] ?? null;
$people_count = $_POST['people_count'] ?? 1;
$account_name = $_POST['account_name'] ?? null;
$phone = $_POST['phone'] ?? null;
$upi_id = $_POST['upi_id'] ?? null;
$transaction_id = $_POST['transaction_id'] ?? null;

if (!$bookingId || !$bookingType || !$reason_category || !$amount || !$upi_id || !$transaction_id || !$account_name || !$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Final reason string
$final_reason = ($reason_category === 'Other') ? $reason_detail : $reason_category . ($reason_detail ? ": " . $reason_detail : "");

// Handle File Upload
$proofPath = null;
if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/refunds/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmpPath = $_FILES['proof_file']['tmp_name'];
    $fileName = time() . '_' . $_FILES['proof_file']['name'];
    $destPath = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $proofPath = $destPath;
    }
}

if (!$proofPath) {
    echo json_encode(['success' => false, 'message' => 'Payment proof is required.']);
    exit;
}

try {
    // Updated INSERT statement to include reason_category, final_reason, and people_count
    $stmt = $pdo->prepare("INSERT INTO REFUND_REQUESTS (user_email, booking_type, booking_id, account_name, phone, upi_id, transaction_id, proof_file, reason_category, reason, people_count, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $email,
        $bookingType,
        $bookingId,
        $account_name,
        $phone,
        $upi_id,
        $transaction_id,
        $proofPath,
        $reason_category, // New field
        $final_reason,    // Using the concatenated/conditional reason
        $people_count,    // New field
        $amount
    ]);

    echo json_encode(['success' => true, 'message' => 'refund request submitted successfully we will get back to you as soon as possible']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
