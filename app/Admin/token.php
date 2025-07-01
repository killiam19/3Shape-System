<?php
include '../Configuration/Connection.php';
session_start(); // Iniciar sesión

//Función helper para debugging
function debugLog($message, $data = null) {
    $_SESSION['debug_messages'][] = [
        'message' => $message,
        'data' => $data !== null ? print_r($data, true) : null
    ];
}

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: token CSRF inválido.");
    }

    // Validar la contraseña
    if (isset($_POST['passwordadmin']) && !empty($_POST['passwordadmin'])) {
        $clave_admin = $_POST['passwordadmin']; // Capturar la clave enviada
        debugLog("Contraseña recibida", $clave_admin);

        // Consultar la base de datos
        $stmt = $pdo->prepare("SELECT clave_admin FROM configuracion_sistema WHERE id = 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !isset($row['clave_admin'])) {
            debugLog("No se encontró la contraseña en la BD");
            $_SESSION['error'] = "Error retrieving current password.";
            header("Location: ../index.php");
            exit;
        }
        // Obtener la contraseña almacenada
        $storedPassword = $row['clave_admin'];
        debugLog("Contraseña almacenada en BD", $storedPassword);

        // Mejor método para detectar si es un hash
        $isHashed = (
            strlen($storedPassword) == 60 && // Longitud típica de un hash bcrypt
            preg_match('/^\$2[ayb]\$[0-9]{2}\$[A-Za-z0-9\.\/]{53}$/', $storedPassword) // Formato bcrypt
        );
        
        debugLog("¿Es hash?", $isHashed ? "Sí" : "No");

        // Verificar la contraseña
        $passwordIsValid = false;
        if ($isHashed) {
            debugLog("Verificando como hash");
            // Verificar la contraseña con el hash almacenado
            $passwordIsValid = password_verify($clave_admin, $storedPassword);
        } else {
            debugLog("Verificando como texto plano");
            $passwordIsValid = ($clave_admin === $storedPassword);
        }

        debugLog("¿Contraseña válida?", $passwordIsValid ? "Sí" : "No");
        // Si la contraseña no es válida, mostrar un mensaje de error
        if (!$passwordIsValid) {
            debugLog("Invalida password - Comparación fallida");
            $_SESSION['error'] = "Incorrect Password.";
            header("Location: ../index.php");
            exit;
        }

        // Crear sesión para el admin
        $_SESSION['is_admin'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generar token nuevo
        
        // Redireccionar al panel admin
        header("Location: index_admin.php");
        exit;
    } else {
        $_SESSION['error'] = "Please, Enter your password.";
        header("Location: ../index.php");
    }
}
?>

