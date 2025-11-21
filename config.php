<?php
// Database configuration - uses environment variables for Railway deployment
// Railway provides MYSQL_URL in format: mysql://user:password@host:port/database
$mysql_url = getenv('MYSQL_URL');

if ($mysql_url) {
    // Parse Railway's MYSQL_URL
    $url_parts = parse_url($mysql_url);
    define('DB_HOST', $url_parts['host'] ?? 'localhost');
    define('DB_USER', $url_parts['user'] ?? 'root');
    define('DB_PASS', $url_parts['pass'] ?? '');
    define('DB_NAME', isset($url_parts['path']) ? ltrim($url_parts['path'], '/') : 'ecommerce_db');
    define('DB_PORT', $url_parts['port'] ?? '3306');
} else {
    // Fall back to individual environment variables (for local development or manual setup)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'ecommerce_db');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}

// Email configuration (update with your email)
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'gergedavas252@gmail.com');

// Create database connection
function getDBConnection() {
    $host = DB_HOST;
    $port = DB_PORT;
    
    // If port is specified, append it to host
    if ($port && $port != '3306') {
        $host = DB_HOST . ':' . $port;
    }
    
    $conn = new mysqli($host, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getUsername() {
    return $_SESSION['username'] ?? null;
}

// Get current user full name
function getUserFullName() {
    return $_SESSION['full_name'] ?? null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>

