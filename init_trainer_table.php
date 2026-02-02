<?php
require_once 'db_connect.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS TRAINER_APPLICATIONS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        specialization VARCHAR(100) NOT NULL,
        experience INT NOT NULL,
        certificate_file VARCHAR(255) NOT NULL,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table TRAINER_APPLICATIONS created successfully or already exists.";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>