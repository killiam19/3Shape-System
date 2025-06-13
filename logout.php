<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar la acción de cierre de sesión
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

// Eliminar la cookie de recordarme si existe
if (isset($_COOKIE['remember_token'])) {
    // Eliminar el token de la base de datos si es necesario
    if (isset($_SESSION['user_id'])) {
        include_once './Configuration/Connection.php';
        try {
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
        } catch (PDOException $e) {
            error_log("Logout error: " . $e->getMessage());
        }
    }
    
    // Eliminar la cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Borrar todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión
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

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location: login.php");
exit;
