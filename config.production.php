<?php
// Database configuration for production environment
$servername = getenv('MYSQL_HOST');
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$dbname = getenv('MYSQL_DATABASE');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Production settings
error_reporting(0); // Disable error reporting
ini_set('display_errors', 0); // Don't display errors to users 