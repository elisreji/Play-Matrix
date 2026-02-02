<?php
require_once 'db_connect.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS GAMES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        host_name VARCHAR(100) NOT NULL,
        sport VARCHAR(50) NOT NULL,
        venue VARCHAR(255) NOT NULL,
        game_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        game_type VARCHAR(100),
        skill_level VARCHAR(50),
        price VARCHAR(50) DEFAULT 'Free',
        max_players INT DEFAULT 4,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "GAMES table initialized successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>