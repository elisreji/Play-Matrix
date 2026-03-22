<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = $_SESSION['user'];

$isJson = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
if ($isJson) {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

$bio = trim($data['bio'] ?? '');
$specializations = trim($data['specializations'] ?? '');
$experience = intval($data['experience'] ?? 0);
$certifications = trim($data['certifications'] ?? '');
$hourly_rate = floatval($data['hourly_rate'] ?? 0.00);

// Server-side Validation
if (empty($bio) || strlen($bio) < 10) {
    echo json_encode(['success' => false, 'message' => 'Bio must be at least 10 characters long.']);
    exit;
}
if (empty($specializations)) {
    echo json_encode(['success' => false, 'message' => 'Please provide at least one specialization.']);
    exit;
}
if ($experience < 0 || $experience > 60) {
    echo json_encode(['success' => false, 'message' => 'Experience must be between 0 and 60 years.']);
    exit;
}
if ($hourly_rate < 0) {
    echo json_encode(['success' => false, 'message' => 'Hourly rate cannot be negative.']);
    exit;
}

// Handle File Uploads
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$profile_photo = '';
$certification_doc = '';

if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $filename = time() . '_photo_' . basename($_FILES['profile_photo']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
        $profile_photo = $targetPath;
    }
}

if (isset($_FILES['certification_doc']) && $_FILES['certification_doc']['error'] === UPLOAD_ERR_OK) {
    $filename = time() . '_cert_' . basename($_FILES['certification_doc']['name']);
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['certification_doc']['tmp_name'], $targetPath)) {
        $certification_doc = $targetPath;
    }
}

try {
    // Check if profile exists
    $stmt = $pdo->prepare("SELECT id, profile_photo, certification_doc FROM TRAINER_PROFILES WHERE user_email = ?");
    $stmt->execute([$email]);
    $profile = $stmt->fetch();

    // Preserve existing files if not overwritten
    if ($profile) {
        if (empty($profile_photo))
            $profile_photo = $profile['profile_photo'];
        if (empty($certification_doc))
            $certification_doc = $profile['certification_doc'];
    }

    if ($profile) {
        // Update
        $stmt = $pdo->prepare("UPDATE TRAINER_PROFILES SET bio = ?, specializations = ?, years_experience = ?, certifications = ?, hourly_rate = ?, profile_photo = ?, certification_doc = ? WHERE user_email = ?");
        $stmt->execute([$bio, $specializations, $experience, $certifications, $hourly_rate, $profile_photo, $certification_doc, $email]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO TRAINER_PROFILES (user_email, bio, specializations, years_experience, certifications, hourly_rate, profile_photo, certification_doc) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $bio, $specializations, $experience, $certifications, $hourly_rate, $profile_photo, $certification_doc]);
    }

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
