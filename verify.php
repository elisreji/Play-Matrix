<?php
$file = 'users.json';
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid token");
}

$users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$updated = false;

foreach ($users as &$user) {
    if (isset($user['verification_token']) && $user['verification_token'] === $token) {
        $user['is_verified'] = true;
        unset($user['verification_token']);
        $updated = true;
        break;
    }
}

if ($updated) {
    file_put_contents($file, json_encode($users));
    header("Location: login.php?verified=1");
    exit;
} else {
    die("Token invalid or expired");
}
?>