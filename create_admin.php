<?php
include 'db_connect.php';

$email = 'admin@gmail.com';
$password = 'admin';
$name = 'Admin User';
$role = 'Admin';
$is_verified = 1;

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($pdo) {
    try {
        // Ensure USERS table has role column if it's missing (though it was in the SQL schema)
        try {
            $pdo->exec("ALTER TABLE USERS ADD COLUMN IF NOT EXISTS role ENUM('User','Trainer','Admin') DEFAULT 'User'");
        } catch (Exception $e) {}

        // Check if user exists
        $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Update existing user
            $stmt = $pdo->prepare("UPDATE USERS SET password_hash = ?, role = ?, is_verified = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $role, $is_verified, $email]);
            echo "Admin user updated in database.\n";
        } else {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO USERS (full_name, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role, $is_verified]);
            echo "Admin user created in database.\n";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Could not connect to database.\n";
}

// Also update users.json
$file = 'users.json';
$users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
if (!is_array($users)) $users = [];

$found = false;
foreach ($users as &$u) {
    if (strtolower(trim($u['email'])) === $email) {
        $u['password'] = $hashed_password;
        $u['role'] = $role;
        $u['is_verified'] = true;
        $found = true;
        break;
    }
}

if (!$found) {
    $users[] = [
        'email' => $email,
        'password' => $hashed_password,
        'name' => $name,
        'role' => $role,
        'is_verified' => true
    ];
}

file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
echo "Admin user updated in users.json.\n";
?>
