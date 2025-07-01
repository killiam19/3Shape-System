<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de idioma
include '../View/Fragments/idioma.php';

include_once '../Configuration/Connection.php';

// Obtener la contraseña actual
try {
    $stmt = $pdo->prepare("SELECT clave_admin FROM configuracion_sistema WHERE id = 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentPassword = $row ? $row['clave_admin'] : '';
} catch (PDOException $e) {
    $currentPassword = '';
    $_SESSION['error'] = "Error al recuperar la contraseña actual.";
}

$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../index.php");
    include_once '../Controller/Cerrar_sesion.php';
    exit("Acceso denegado.");
}

// Mensajes de Usuario
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}

$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <title>Restore password</title>
    <link rel="stylesheet" href="../Configuration/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/Form.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../View/Css/button-styles.css">
    <style>
        .form-control:focus {
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4 fw-bold text-secondary"><?php echo __('reset_your_password', $lang); ?></h2>
                        <form action="../Controller/update_password.php" method="post" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="NewPassword" class="form-label fw-semibold"><?php echo __('new_password', $lang); ?></label>
                                <div class="input-group">
                                    <input type="password" name="NewPassword" id="NewPassword" 
                                        class="form-control shadow-sm" 
                                        placeholder="Enter new password" 
                                        required 
                                        oninput="validatePassword()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('NewPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="Confirmpassword" class="form-label fw-semibold"><?php echo __('confirm_new_password', $lang); ?></label>
                                <div class="input-group">
                                    <input type="password" name="Confirmpassword" id="Confirmpassword" 
                                        class="form-control shadow-sm" 
                                        placeholder="Confirm your password" 
                                        required 
                                        oninput="validatePassword()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('Confirmpassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="securityCode" class="form-label fw-semibold">Security Code</label>
                                <div class="input-group">
                                    <input type="password" name="securityCode" id="securityCode" 
                                        class="form-control shadow-sm" 
                                        pattern="121312@"
                                        placeholder="Enter security code" 
                                        required
                                        title="Please enter the correct security code (121312@)">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('securityCode')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text text-info mt-2" id="securityCodeHelp">
                                    Please enter your security code (e.g. 121312@)
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-secondary btn-lg shadow-sm" 
                                    onclick="return confirm('Are you sure you want to send this information?')">
                                    <i class="fas fa-paper-plane me-2"></i><?php echo __('send', $lang); ?>
                                </button>
                                <a href="../index.php" class="btn btn-danger btn-lg shadow-sm">
                                    <i class="fas fa-arrow-left me-2"></i><?php echo __('back', $lang); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>  
    
    <script src="../Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>
     <script src="../View/Js/dark-mode-toggle-new.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = event.currentTarget.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        function validatePassword() {
            const password = document.getElementById('NewPassword').value;
            const confirm = document.getElementById('Confirmpassword').value;
            if (confirm && password !== confirm) {
                document.getElementById('Confirmpassword').setCustomValidity('Passwords do not match');
            } else {
                document.getElementById('Confirmpassword').setCustomValidity('');
            }
        }

        // Enable Bootstrap form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
<?php include './Fragments/footer.php'; ?>
</html>