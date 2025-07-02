<?php
// Database Configuration
// TODO: Change these example values to your actual database credentials
define('DB_HOST', 'localhost'); // e.g., '127.0.0.1'
define('DB_USER', 'db_user'); // e.g., 'root'
define('DB_PASS', 'db_password'); // e.g., 'your_db_password'
define('DB_NAME', 'your_database'); // e.g., 'attendance_db'

// SMTP Configuration (for emails)
// TODO: Change these example values to your actual SMTP credentials
define('SMTP_USER', 'your_email@example.com'); // e.g., 'user@gmail.com'
define('SMTP_PASS', 'your_smtp_app_password'); // e.g., 'app password from Google'
define('SMTP_FROM', 'your_email@example.com'); // e.g., 'user@gmail.com'

// Error Reporting (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Create connection with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL Connection Failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4 (for full Unicode support)
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $conn->error);
    }

    // Verify database exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $conn->real_escape_string(DB_NAME) . "'");
    if ($result->num_rows == 0) {
        throw new Exception("Database '" . DB_NAME . "' does not exist");
    }

} catch (Exception $e) {
    die("System Error: " . $e->getMessage() . 
        "<br>Please contact administrator. Error logged at: " . date('Y-m-d H:i:s'));
}

// Timezone settings
date_default_timezone_set('Asia/Kolkata');

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Session Security (if using sessions)
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}
?>