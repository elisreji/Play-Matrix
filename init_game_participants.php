<?php
require_once 'db_connect.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS GAME_PARTICIPANTS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game_id INT NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (game_id, user_email),
        FOREIGN KEY (game_id) REFERENCES GAMES(id) ON DELETE CASCADE
    )");
    echo "GAME_PARTICIPANTS table initialized successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>