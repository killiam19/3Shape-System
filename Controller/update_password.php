<?php
session_start();
include '../Configuration/Connection.php';

// Función helper para debugging
function debugLog($message, $data = null)
{
    $_SESSION['debug_messages'][] = [
        'message' => $message,
        'data' => $data !== null ? print_r($data, true) : null
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = trim($_POST['NewPassword'] ?? '');
        $confirmPassword = trim($_POST['Confirmpassword'] ?? '');
        $securityCode = trim($_POST['securityCode'] ?? '');

        // Validar campos vacíos
        if (empty($newPassword) || empty($confirmPassword) || empty($securityCode)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: ../index.php");
            exit;
        }

        // Verificar coincidencia entre la nueva contraseña y su confirmación
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "New passwords do not match.";
            header("Location: ../index.php");
            exit;
        }

        // Validar el código de seguridad
        $expectedSecurityCode = '121312@'; // Replace with the actual expected security code
        if ($securityCode !== $expectedSecurityCode) {
            $_SESSION['error'] = "Invalid security code.";
            header("Location: ../index.php");
            exit;
        }

        // Generar hash de la nueva contraseña
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        debugLog("Nuevo hash generado", $newPasswordHash);

        // Actualizar la contraseña en la base de datos
        $updateStmt = $pdo->prepare("UPDATE configuracion_sistema SET clave_admin = :newPassword WHERE id = 1");
        $updateStmt->execute(['newPassword' => $newPasswordHash]);

        if ($updateStmt->rowCount() > 0) {
            debugLog("Contraseña actualizada exitosamente");
            $_SESSION['success'] = "Password changed successfully.";
            header("Location: ../index.php");
        } else {
            debugLog("No se actualizó la contraseña");
            $_SESSION['error'] = "No changes were made to the password.";
            header("Location: ../index.php");
        }
        header("Location: ../index.php");
        exit;
    }
} catch (PDOException $e) {
    debugLog("Error de PDO", $e->getMessage());
    $_SESSION['error'] = "An internal error occurred. Please try again later.";
    header("Location: ../index.php");
    exit;
}
