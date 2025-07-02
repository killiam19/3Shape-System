<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../Configuration/Connection.php';
include_once '../Configuration/MailConfig.php';

// Initialize variables
$error_message = '';
$success_message = '';
$identifier = '';
$showResetForm = false;

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = 'Error de validación de seguridad. Por favor, inténtalo de nuevo.';
    } else {
        if (isset($_POST['request_reset'])) {
            // Procesar solicitud de restablecimiento
            $identifier = trim($_POST['identifier'] ?? '');
            
            if (empty($identifier)) {
                $error_message = 'Por favor, ingresa tu nombre de usuario o correo electrónico.';
            } else {
                try {
                    // Verificar si el usuario existe
                    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
                    $stmt->execute(['identifier' => $identifier]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        // Generar token de restablecimiento
                        $resetToken = bin2hex(random_bytes(32));
                        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        
                        // Guardar token en la base de datos
                        $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
                        $stmt->execute([
                            'token' => $resetToken,
                            'expiry' => $expiryTime,
                            'id' => $user['id']
                        ]);
                        
                        // Enviar correo electrónico
                        $mailer = new MailConfig();
                        try {
                            if ($mailer->sendPasswordResetEmail($user['email'], $resetToken)) {
                                $success_message = 'Se ha enviado un correo electrónico con instrucciones para restablecer tu contraseña.';
                                $identifier = ''; // Limpiar el formulario
                            }
                        } catch (Exception $e) {
                            $error_message = 'Error al enviar el correo: ' . $e->getMessage();
                            error_log("Error en reset_password.php: " . $e->getMessage());
                        }
                    } else {
                        $error_message = 'No se encontró ninguna cuenta con ese nombre de usuario o correo electrónico.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos. Por favor, inténtalo más tarde.';
                    error_log("Error de restablecimiento de contraseña: " . $e->getMessage());
                }
            }
        } elseif (isset($_POST['reset_password'])) {
            // Procesar restablecimiento de contraseña
            $token = $_POST['token'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($token)) {
                $error_message = 'Token de restablecimiento inválido.';
            } elseif (empty($new_password)) {
                $error_message = 'Por favor, ingresa una nueva contraseña.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'Las contraseñas no coinciden.';
            } else {
                try {
                    // Verificar token y expiración
                    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE reset_token = :token AND reset_token_expiry > NOW() LIMIT 1");
                    $stmt->execute(['token' => $token]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        // Actualizar contraseña
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
                        $result = $stmt->execute([
                            'password' => $hashed_password,
                            'id' => $user['id']
                        ]);
                        
                        if ($result) {
                            $success_message = 'La contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.';
                            header("refresh:3;url=../../login.php");
                        } else {
                            $error_message = 'Error al restablecer la contraseña. Por favor, inténtalo de nuevo.';
                        }
                    } else {
                        $error_message = 'Token de restablecimiento inválido o expirado.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos. Por favor, inténtalo más tarde.';
                    error_log("Error de restablecimiento de contraseña: " . $e->getMessage());
                }
            }
        }
    }
}

// Verificar si hay un token en la URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expiry > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        if ($stmt->fetch()) {
            $showResetForm = true;
        } else {
            $error_message = 'Token de restablecimiento inválido o expirado.';
        }
    } catch (PDOException $e) {
        $error_message = 'Error de base de datos. Por favor, inténtalo más tarde.';
        error_log("Error de verificación de token: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema de Gestión de Activos</title>
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    
    <!-- Critical CSS -->
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
            background-color: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reset-container {
            max-width: 500px;
            width: 100%;
            animation: fadeIn 0.5s ease-out;
        }
        
        .reset-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border: none;
        }
        
        .reset-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .reset-logo {
            max-width: 150px;
            margin-bottom: 1rem;
        }
        
        .reset-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
        }
        
        .reset-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .reset-body {
            padding: 1.5rem;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .form-floating > .form-control {
            padding: 1rem 0.75rem;
        }
        
        .form-floating > label {
            padding: 1rem 0.75rem;
        }
        
        .reset-footer {
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .btn-reset {
            width: 100%;
            padding: 0.75rem;
            background-color: #0d6efd;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            margin-top: 1rem;
        }
        
        .btn-reset:hover {
            background-color: #0b5ed7;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            cursor: pointer;
            color: #6c757d;
            padding: 5px;
        }
        
        .password-toggle:hover {
            color: #0d6efd;
        }
        
        .alert {
            border-radius: 4px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border: none;
            animation: shake 0.5s ease-in-out;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .strength-weak {
            background-color: #dc3545;
            width: 30%;
        }
        
        .strength-medium {
            background-color: #ffc107;
            width: 60%;
        }
        
        .strength-strong {
            background-color: #198754;
            width: 100%;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <img src="../Configuration/3shape-intraoral-logo.png" alt="Logo" class="reset-logo">
                <h1 class="reset-title">Restablecer Contraseña</h1>
                <p class="reset-subtitle">
                    <?php echo $showResetForm ? 'Ingresa tu nueva contraseña' : 'Ingresa tu nombre de usuario o correo electrónico'; ?>
                </p>
            </div>
            
            <div class="reset-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($showResetForm): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                        
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <label for="new_password">Nueva Contraseña</label>
                            <span class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <label for="confirm_password">Confirmar Contraseña</label>
                            <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn btn-reset">
                            Restablecer Contraseña
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="identifier" name="identifier" value="<?php echo htmlspecialchars($identifier); ?>" required>
                            <label for="identifier">Nombre de Usuario o Correo Electrónico</label>
                        </div>
                        
                        <button type="submit" name="request_reset" class="btn btn-reset">
                            Enviar Enlace de Restablecimiento
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="../../login.php" class="text-decoration-none">Volver al Inicio de Sesión</a>
                </div>
            </div>
            
            <div class="reset-footer">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Asset Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script src="../Configuration/JQuery/jquery-3.6.0.min.js"></script>
    <script src="../Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
