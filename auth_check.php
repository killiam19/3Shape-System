<?php
/**
 * Authentication check file
 * Include this at the beginning of any page that requires authentication
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in via session
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

// If not logged in via session, check for remember me cookie
if (!$is_logged_in && isset($_COOKIE['remember_token'])) {
    $remember_token = $_COOKIE['remember_token'];
    
    // Verify the remember token
    include_once './Configuration/Connection.php';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->execute(['token' => $remember_token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Valid token, log the user in
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_full_name'] = $user['full_name'];
            
            // Set admin flag if role is admin
            if ($user['role'] === 'admin') {
                $_SESSION['is_admin'] = true;
            }
            
            // Regenerate the session ID for security
            session_regenerate_id(true);
            
            // Log the auto-login
            $log_message = [
                'message' => "User {$user['username']} auto-logged in via remember token",
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $user['id']
            ];
            
            $log_file = './Model/Logs/session_messages.json';
            if (file_exists($log_file)) {
                $log_data = json_decode(file_get_contents($log_file), true);
                $log_data['success'][] = $log_message;
                file_put_contents($log_file, json_encode($log_data));
            }
            
            $is_logged_in = true;
        } else {
            // Invalid token, clear the cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
    } catch (PDOException $e) {
        error_log("Auth check error: " . $e->getMessage());
    }
}

// If still not logged in, redirect to login page
if (!$is_logged_in) {
    // Store the requested URL for redirection after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    header("Location: login.php");
    exit;
}

// Check for session timeout (optional)
$session_timeout = 3600; // 1 hour in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired
    $_SESSION = [];
    session_destroy();
    
    // Redirect to login with timeout message
    header("Location: login.php?timeout=1");
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();
