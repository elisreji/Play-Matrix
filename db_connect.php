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

    // 1. Trainer Profiles (Extends User)
    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_PROFILES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL UNIQUE,
        bio TEXT,
        specializations TEXT, -- JSON or comma-separated
        certifications TEXT, -- JSON paths or comma-separated
        years_experience INT DEFAULT 0,
        hourly_rate DECIMAL(10, 2) DEFAULT 0.00,
        rating DECIMAL(3, 2) DEFAULT 5.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Coaching Programs & Tournaments
    $pdo->exec("CREATE TABLE IF NOT EXISTS COACHING_PROGRAMS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_email VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        duration_weeks INT DEFAULT 1,
        type VARCHAR(100) NOT NULL DEFAULT 'Fitness',
        location VARCHAR(255) DEFAULT 'Online',
        event_date DATE NULL,
        event_time TIME NULL,
        event_end_time TIME NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        registration_deadline DATE NULL,
        max_participants INT DEFAULT 100,
        tournament_format VARCHAR(100) DEFAULT 'Knockout',
        prize_details TEXT,
        contact_info VARCHAR(255),
        banner_path VARCHAR(255),
        is_tournament TINYINT(1) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'Published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Trainer Schedule (Availability)
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN status VARCHAR(50) DEFAULT 'Published'"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN start_date DATE NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN end_date DATE NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN registration_deadline DATE NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN max_participants INT DEFAULT 100"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN tournament_format VARCHAR(100) DEFAULT 'Knockout'"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN prize_details TEXT"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN contact_info VARCHAR(255)"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN banner_path VARCHAR(255)"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN is_tournament TINYINT(1) DEFAULT 0"); } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN location VARCHAR(255) DEFAULT 'Online'");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN event_date DATE NULL");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN event_time TIME NULL");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE COACHING_PROGRAMS ADD COLUMN event_end_time TIME NULL");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE COACHING_PROGRAMS MODIFY COLUMN type VARCHAR(100) DEFAULT 'Fitness'");
    } catch (Exception $e) {
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_SCHEDULE (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_email VARCHAR(100) NOT NULL,
        day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Bookings / Sessions
    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_BOOKINGS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_email VARCHAR(100) NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        program_id INT NULL,
        session_date DATE NOT NULL,
        session_time TIME NOT NULL,
        status ENUM('Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled') DEFAULT 'Pending',
        payment_id VARCHAR(100) DEFAULT NULL,
        payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
        amount_paid DECIMAL(10, 2) DEFAULT 0.00,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Migration for TRAINER_BOOKINGS
    try {
        $pdo->exec("ALTER TABLE TRAINER_BOOKINGS ADD COLUMN payment_id VARCHAR(100) DEFAULT NULL AFTER status");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE TRAINER_BOOKINGS ADD COLUMN payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending' AFTER payment_id");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE TRAINER_BOOKINGS ADD COLUMN amount_paid DECIMAL(10, 2) DEFAULT 0.00 AFTER payment_status");
    } catch (Exception $e) {
    }

    // 5. Training Resources (Plans, Diets)
    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_RESOURCES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_email VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        type ENUM('Workout Plan', 'Diet Plan', 'Video', 'Other') NOT NULL,
        file_path VARCHAR(255),
        assigned_to_user_email VARCHAR(100) NULL, -- NULL = Public/Template, or specific user
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 6. Reviews
    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_REVIEWS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_email VARCHAR(100) NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 6.1 Venue Reviews
    $pdo->exec("CREATE TABLE IF NOT EXISTS VENUE_REVIEWS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venue_id VARCHAR(255) NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 6.2 Program/Event Reviews
    $pdo->exec("CREATE TABLE IF NOT EXISTS PROGRAM_REVIEWS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_id VARCHAR(255) NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        $pdo->exec("ALTER TABLE PROGRAM_REVIEWS MODIFY COLUMN program_id VARCHAR(255)");
    } catch (Exception $e) {}

    // 7. Health Profile
    $pdo->exec("CREATE TABLE IF NOT EXISTS USER_HEALTH_PROFILES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL UNIQUE,
        height_cm DECIMAL(5,2),
        weight_kg DECIMAL(5,2),
        blood_group VARCHAR(5),
        allergies TEXT,
        medical_conditions TEXT,
        fitness_goals TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Add payment_id column to GAME_PARTICIPANTS if not exists
    try {
        $pdo->exec("ALTER TABLE GAME_PARTICIPANTS ADD COLUMN payment_id VARCHAR(100) DEFAULT NULL");
    } catch (Exception $e) {
        // Ignore if exists
    }

    // 8. Complaints
    $pdo->exec("CREATE TABLE IF NOT EXISTS COMPLAINTS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('Pending', 'Looking', 'Resolved') DEFAULT 'Pending',
        admin_reply TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add admin_reply column if it doesn't exist (migration helper)
    try {
        $pdo->exec("ALTER TABLE COMPLAINTS ADD COLUMN admin_reply TEXT DEFAULT NULL");
    } catch (Exception $e) {
        // Ignore if exists
    }
    // 9. Trainer Connectivity Requests
    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_REQUESTS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        trainer_email VARCHAR(100) NOT NULL,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        $pdo->exec("ALTER TABLE TRAINER_REQUESTS ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE TRAINER_REQUESTS ADD UNIQUE KEY unique_request (user_email, trainer_email)");
    } catch (Exception $e) {
    }

    // 10. User Notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS USER_NOTIFICATIONS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS TRAINER_VENUES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trainer_email VARCHAR(100) NOT NULL,
        venue_name VARCHAR(100) NOT NULL,
        location VARCHAR(255) NOT NULL,
        about_venue TEXT,
        timing VARCHAR(100),
        sports_available VARCHAR(255),
        pic1 VARCHAR(255),
        pic2 VARCHAR(255),
        pic3 VARCHAR(255),
        approval_status VARCHAR(20) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Migration helper: Add missing columns if table already existed without them
    try {
        $pdo->exec("ALTER TABLE TRAINER_VENUES ADD COLUMN location VARCHAR(255) NOT NULL AFTER venue_name");
    } catch (Exception $e) { /* Column likely exists */
    }

    try {
        $pdo->exec("ALTER TABLE TRAINER_VENUES ADD COLUMN approval_status VARCHAR(20) DEFAULT 'Pending' AFTER pic3");
    } catch (Exception $e) { /* Column likely exists */
    }

    // 11. Refund Requests
    $pdo->exec("CREATE TABLE IF NOT EXISTS REFUND_REQUESTS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        booking_type ENUM('Game', 'Trainer', 'Event') NOT NULL,
        booking_id INT NOT NULL,
        account_name VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        upi_id VARCHAR(100) DEFAULT NULL,
        transaction_id VARCHAR(100) DEFAULT NULL,
        proof_file VARCHAR(255) DEFAULT NULL,
        reason_category VARCHAR(100) DEFAULT NULL,
        reason TEXT NOT NULL,
        people_count INT DEFAULT 1,
        amount DECIMAL(10, 2) NOT NULL,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        admin_reply TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Migration helper for Refunds
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN reason_category VARCHAR(100) DEFAULT NULL AFTER proof_file");
    } catch (Exception $e) {
    }
    // Migration helper for Refunds
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN people_count INT DEFAULT 1 AFTER reason");
    } catch (Exception $e) {
    }

    // Migration helper for Refunds
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN account_name VARCHAR(100) DEFAULT NULL AFTER booking_id");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER account_name");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN upi_id VARCHAR(100) DEFAULT NULL AFTER phone");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL AFTER upi_id");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE REFUND_REQUESTS ADD COLUMN proof_file VARCHAR(255) DEFAULT NULL AFTER transaction_id");
    } catch (Exception $e) {
    }

    // 12. Venue Bookings
    $pdo->exec("CREATE TABLE IF NOT EXISTS VENUE_BOOKINGS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        venue_id VARCHAR(255) NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME NOT NULL,
        amount_paid DECIMAL(10, 2) DEFAULT 0.00,
        status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Paid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 13. Messages / Chat
    $pdo->exec("CREATE TABLE IF NOT EXISTS MESSAGES (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_email VARCHAR(100) NOT NULL,
        receiver_email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // If database doesn't exist, we fallback to NULL.
    // The application scripts check if $pdo exists.
    $pdo = null;
    // Log error significantly
    file_put_contents('db_error.log', $e->getMessage());
}
?>