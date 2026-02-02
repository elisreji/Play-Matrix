<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = $_SESSION['user'];
$specialization = $_POST['specialization'] ?? '';
$experience = $_POST['experience'] ?? '';
$fullName = $_POST['full_name'] ?? '';

if (empty($specialization) || empty($experience) || empty($fullName)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$uploadDir = 'uploads/certificates/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filePath = null;
if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['certificate']['tmp_name'];
    $fileName = $_FILES['certificate']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, PNG allowed.']);
        exit;
    }

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $filePath = $destPath;
    } else {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Certificate file is required']);
    exit;
}

if ($pdo) {
    try {
        // Check if already applied
        $check = $pdo->prepare("SELECT id FROM TRAINER_APPLICATIONS WHERE user_email = ? AND status = 'Pending'");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You already have a pending application.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO TRAINER_APPLICATIONS (user_email, full_name, specialization, experience, certificate_file) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$email, $fullName, $specialization, $experience, $filePath]);

        echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Fallback? Ideally we need DB for this as requested.
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>