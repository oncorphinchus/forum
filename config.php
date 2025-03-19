<?php
// Database configuration for PostgreSQL
try {
    // Get database credentials from Render environment variables (MySQL-style variable names)
    $host = getenv('MYSQL_HOST') ?: "dpg-cvdfoqofnakc73dji1h0-a"; // Use Render's MYSQL_HOST variable
    $username = getenv('MYSQL_USER') ?: "login_system_mzct_user"; // Use Render's MYSQL_USER variable
    $password = getenv('MYSQL_PASSWORD') ?: "27cSz4yMXbFiiRKYwcoOKq0T5la891wm"; // Use Render's MYSQL_PASSWORD variable
    $dbname = getenv('MYSQL_DATABASE') ?: "login_system_mzct"; // Use Render's MYSQL_DATABASE variable
    $port = getenv('DATABASE_PORT') ?: 5432; // PostgreSQL default port
    
    // Create PDO connection string for PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";
    
    // Set PDO options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    // Create PDO instance
    $conn = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Define base URL
// Automatically detect the base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$base_path = rtrim($script_name, '/');
define('BASE_URL', $protocol . '://' . $host . $base_path);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database helper functions
require_once __DIR__ . '/db_helpers.php'; 