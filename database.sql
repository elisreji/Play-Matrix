-- Database Schema for Play Matrix

CREATE TABLE USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255),
    role ENUM('User','Trainer','Admin') DEFAULT 'User',
    plan ENUM('Free','Silver','Gold','Platinum') DEFAULT 'Free',
    is_verified BOOLEAN DEFAULT FALSE,
    is_blocked BOOLEAN DEFAULT FALSE,
    otp VARCHAR(10),
    reset_otp VARCHAR(10),
    skill_level ENUM('Beginner','Intermediate','Advanced'),
    subscription_id INT,
    emergency_contact VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE USER_HEALTH_PROFILE (
    health_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    medical_conditions TEXT,
    fitness_goal TEXT,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id)
);

CREATE TABLE TRAINERS (
    trainer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    specialization VARCHAR(100),
    experience_years INT,
    certification_details TEXT,
    rating DECIMAL(3,2),
    verification_status ENUM('Pending','Verified','Rejected'),
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id)
);

CREATE TABLE SUBSCRIPTIONS (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name ENUM('Free','Silver','Gold','Platinum'),
    price DECIMAL(10,2),
    validity_days INT,
    benefits TEXT
);

CREATE TABLE VENUES (
    venue_id INT AUTO_INCREMENT PRIMARY KEY,
    venue_name VARCHAR(150),
    venue_type VARCHAR(100),
    location TEXT,
    price_per_slot DECIMAL(10,2),
    rating DECIMAL(3,2),
    admin_approval ENUM('Pending','Approved','Rejected'),
    created_at DATETIME
);

CREATE TABLE ACTIVITIES (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    activity_name VARCHAR(150),
    activity_type ENUM('Sport','Fitness','Adventure'),
    risk_level ENUM('Low','Medium','High'),
    description TEXT
);

CREATE TABLE BOOKINGS (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    venue_id INT,
    activity_id INT,
    booking_date DATE,
    time_slot VARCHAR(50),
    booking_status ENUM('Pending','Confirmed','Cancelled','Completed'),
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id),
    FOREIGN KEY (venue_id) REFERENCES VENUES(venue_id),
    FOREIGN KEY (activity_id) REFERENCES ACTIVITIES(activity_id)
);

CREATE TABLE TRAINER_SESSIONS (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT,
    user_id INT,
    session_type ENUM('Personal','Group'),
    schedule DATETIME,
    status ENUM('Scheduled','Completed','Cancelled'),
    FOREIGN KEY (trainer_id) REFERENCES TRAINERS(trainer_id),
    FOREIGN KEY (user_id) REFERENCES USERS(user_id)
);

CREATE TABLE TOURNAMENTS (
    tournament_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_name VARCHAR(150),
    venue_id INT,
    start_date DATE,
    end_date DATE,
    max_participants INT,
    status ENUM('Upcoming','Ongoing','Completed') DEFAULT 'Upcoming',
    admin_approval ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id) REFERENCES VENUES(venue_id)
);

CREATE TABLE TOURNAMENT_REGISTRATION (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    user_id INT,
    registered_at DATETIME,
    FOREIGN KEY (tournament_id) REFERENCES TOURNAMENTS(tournament_id),
    FOREIGN KEY (user_id) REFERENCES USERS(user_id)
);

CREATE TABLE PAYMENTS (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    booking_id INT,
    amount DECIMAL(10,2),
    payment_method ENUM('UPI','Card','Wallet','NetBanking'),
    payment_status ENUM('Pending','Success','Failed','Refunded'),
    paid_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id),
    FOREIGN KEY (booking_id) REFERENCES BOOKINGS(booking_id)
);

CREATE TABLE REVIEWS (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reference_type ENUM('Venue','Trainer','Activity'),
    reference_id INT,
    rating INT,
    comment TEXT,
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id)
);

CREATE TABLE NOTIFICATIONS (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT,
    notification_type VARCHAR(50),
    is_read BOOLEAN,
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id)
);

CREATE TABLE ADMIN_ACTION_LOGS (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action TEXT,
    target_table VARCHAR(50),
    created_at DATETIME,
    FOREIGN KEY (admin_id) REFERENCES USERS(user_id)
);

CREATE TABLE TRAINER_APPLICATIONS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    experience INT NOT NULL,
    certificate_file VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
