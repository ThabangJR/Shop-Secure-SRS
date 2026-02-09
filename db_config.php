<?php

ob_start(); 

define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'ShopSecureDB');
define('DB_USER', 'root');
define('DB_PASS', 'Thabang@23768');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    // Generate a secure, cryptographically random token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}



try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("A required service is unavailable.");
}

//basic input sanitizer
function sanitize($data) {
    //trims whitespace and remove HTML tags. HTML escaping happens at output.
    return trim(strip_tags($data));
}

//authorization check function
function require_manager() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
        header('Location: index.php?auth_error=denied');
        exit;
    }
}

//CSRF check
function check_csrf() {
    //use filter_input for security, falling back to $_POST
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_SPECIAL_CHARS) ?: ($_POST['csrf_token'] ?? '');
    
    if (empty($token) || $token !== $_SESSION['csrf_token']) {
        //logging a security breach attempt
        error_log("CSRF attack detected: Token mismatch for user ID " . ($_SESSION['customerID'] ?? 'unknown'));
        //Die with a generic error
        die('Security token validation failed.'); 
    }
}
?>