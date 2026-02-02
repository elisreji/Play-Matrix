<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'playmatrix';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Create GAMES table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS GAMES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        host_name VARCHAR(100) NOT NULL,
        sport VARCHAR(50) NOT NULL,
        venue VARCHAR(255) NOT NULL,
        game_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        game_type VARCHAR(50) NOT NULL,
        skill_level VARCHAR(50) NOT NULL,
        price VARCHAR(20) DEFAULT 'Free',
        max_players INT DEFAULT 4,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Add columns if they missed (migration helper)
    try {
        $pdo->exec("ALTER TABLE GAMES ADD COLUMN price VARCHAR(20) DEFAULT 'Free'");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE GAMES ADD COLUMN max_players INT DEFAULT 4");
    } catch (Exception $e) {
    }

    // Create TRAINER_APPLICATIONS table
    $sql_trainer = "CREATE TABLE IF NOT EXISTS TRAINER_APPLICATIONS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        specialization VARCHAR(100),
        experience VARCHAR(50),
        certificate_file VARCHAR(255),
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_trainer);

} catch (PDOException $e) {
    // If database doesn't exist, we fallback to NULL.
    // The application scripts check if $pdo exists.
    $pdo = null;
}
?>