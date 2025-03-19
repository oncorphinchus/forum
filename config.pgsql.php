<?php
// Database configuration for PostgreSQL
try {
    // Get database credentials from environment variables
    $host = getenv('DATABASE_URL') ?: "dpg-cvdfoqofnakc73dji1h0-a"; // Updated hostname
    $username = getenv('DATABASE_USER') ?: "login_system_mzct_user"; // Updated username
    $password = getenv('DATABASE_PASSWORD') ?: "27cSz4yMXbFiiRKYwcoOKq0T5la891wm"; // Updated password
    $dbname = getenv('DATABASE_NAME') ?: "login_system_mzct"; // Updated database name
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