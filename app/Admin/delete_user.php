<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit("Access denied.");
}

include_once '../Configuration/Connection.php';

// Check for CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = "Security validation failed. Please try again.";
    header("Location: add_user.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid user ID.";
    header("Location: add_user.php");
    exit;
}

$user_id = (int)$_GET['id'];

try {
    // First, check if the user exists and is not the main admin
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: add_user.php");
        exit;
    }
    
    if ($user['username'] === 'admin') {
        $_SESSION['error_message'] = "Cannot delete the main administrator account.";
        header("Location: add_user.php");
        exit;
    }
    
    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $result = $stmt->execute(['id' => $user_id]);
    
    if ($result) {
        // Log the action
        $log_message = [
            'message' => "User '{$user['username']}' deleted by admin",
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $log_file = '../Model/Logs/session_messages.json';
        if (file_exists($log_file)) {
            $log_data = json_decode(file_get_contents($log_file), true);
            $log_data['success'][] = $log_message;
            file_put_contents($log_file, json_encode($log_data));
        }
        
        $_SESSION['success_message'] = "User deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete user.";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

header("Location: add_user.php");
exit;
?>