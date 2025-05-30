<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log the logout action
$username = $_SESSION['username'] ?? 'Unknown user';
$log_message = [
    'message' => "User {$username} logged out",
    'timestamp' => date('Y-m-d H:i:s')
];

$log_file = './Model/Logs/session_messages.json';
if (file_exists($log_file)) {
    $log_data = json_decode(file_get_contents($log_file), true);
    $log_data['success'][] = $log_message;
    file_put_contents($log_file, json_encode($log_data));
}

// Remove remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    // Remove token from database if needed
    if (isset($_SESSION['user_id'])) {
        include_once './Configuration/Connection.php';
        try {
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
        } catch (PDOException $e) {
            error_log("Logout error: " . $e->getMessage());
        }
    }
    
    // Delete the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
