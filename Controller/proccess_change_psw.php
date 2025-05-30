<?php
session_start();
include '../Configuration/Connection.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Token de seguridad inválido.");
        }

        // Validaciones básicas
        $currentPassword = trim($_POST['currentPassword'] ?? '');
        $newPassword = trim($_POST['newPassword'] ?? '');
        $confirmPassword = trim($_POST['confirmPassword'] ?? '');

        // Validar campos obligatorios
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // Verificar coincidencia de nueva contraseña
        if ($newPassword !== $confirmPassword) {
            throw new Exception("Las nuevas contraseñas no coinciden.");
        }

        // Validar complejidad de contraseña
        if (strlen($newPassword) < 8 || 
            !preg_match('/[A-Z]/', $newPassword) || 
            !preg_match('/[0-9]/', $newPassword) || 
            !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, números y caracteres especiales.");
        }

        // Obtener la contraseña actual
        $stmt = $pdo->prepare("SELECT clave_admin FROM configuracion_sistema WHERE id = 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("No se encontró la configuración del sistema.");
        }

        if ($currentPassword !== $row['clave_admin']) {
            // Esto no debería ocurrir nunca ya que el campo es readonly
            throw new Exception("Error en la verificación de contraseña. Por favor recarga la página.");
        }

        // Preparar actualización de contraseña
        $updateStmt = $pdo->prepare("UPDATE configuracion_sistema SET clave_admin = :newPassword WHERE id = 1");
        $updateResult = $updateStmt->execute(['newPassword' => $newPassword]);

        if (!$updateResult) {
            throw new Exception("No se pudo actualizar la contraseña.");
        }

        // Registrar cambio de contraseña
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'Password Changed',
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user' => $_SESSION['username'] ?? 'unknown'
        ];
        
        file_put_contents('../Model/Logs/password_changes.log', 
            json_encode($logEntry) . PHP_EOL, 
            FILE_APPEND
        );

        // Mensaje de éxito
        $_SESSION['success'] = "Contraseña actualizada exitosamente.";
        
        // Regenerar token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Redirección
        header("Location: ../View/Int_changePsw.php");
        exit();
    }
} catch (Exception $e) {
    // Manejo de errores
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../View/Int_changePsw.php");
    exit();
}
?>