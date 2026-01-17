<?php
include 'db_connect.php';

$queries = [];

// Users Table
$queries[] = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    gender ENUM('Male', 'Female', 'Other'),
    age INT,
    membership_type ENUM('Monthly', 'Quarterly', 'Yearly'),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('pending', 'active', 'blocked', 'archived', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Settings Table
$queries[] = "CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
)";

// Payments Table
$queries[] = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('registration', 'monthly') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    month_year VARCHAR(7), -- Format: YYYY-MM
    screenshot_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Workouts Table
$queries[] = "CREATE TABLE IF NOT EXISTS workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date DATE NOT NULL,
    exercise_name VARCHAR(100) NOT NULL,
    sets INT,
    reps INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Insert Default Admin if not exists
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$queries[] = "INSERT IGNORE INTO users (full_name, phone, email, password, role, status) 
              VALUES ('System Admin', '0000000000', 'admin@gym.com', '$admin_pass', 'admin', 'active')";

// Payments Table - update enum to include 'yearly'
$queries[] = "ALTER TABLE payments MODIFY COLUMN type ENUM('registration', 'monthly', 'yearly') NOT NULL";

// Insert Default Settings (Fees & UPI)
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('registration_fee', '500')";
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('monthly_fee', '30')";
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('yearly_fee', '300')";
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('registration_enabled', '1')";

// UPI Settings
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('upi_id', 'gym@upi')";
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('upi_name', 'IronForge Gym')";
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('bank_name', 'IronBank')";
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('qr_mode', 'auto')"; // auto or custom
$queries[] = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('qr_image', '')";

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

$conn->close();
?>