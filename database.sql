-- Run this in phpMyAdmin (SQL tab) after creating database `fixit_db`

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    location VARCHAR(200),
    photo_path VARCHAR(255),
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    assigned_to VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Optional: a quick test account so you can log in immediately
-- password below is "password123" hashed with PHP's password_hash()
-- (we'll generate real ones once register.php exists)
