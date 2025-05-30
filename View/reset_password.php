<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../Configuration/Connection.php';

// Initialize variables
$error_message = '';
$success_message = '';
$identifier = '';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = 'Security validation failed. Please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Basic validation
        if (empty($identifier)) {
            $error_message = 'Please enter your username or email.';
        } elseif (empty($new_password)) {
            $error_message = 'Please enter a new password.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'Password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        } else {
            try {
                // Check if user exists
                $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
                $stmt->execute(['identifier' => $identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                    $result = $stmt->execute([
                        'password' => $hashed_password,
                        'id' => $user['id']
                    ]);
                    
                    if ($result) {
                        // Log the action
                        $log_message = [
                            'message' => "Password reset for user {$user['username']}",
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        
                        $log_file = '../Model/Logs/session_messages.json';
                        if (file_exists($log_file)) {
                            $log_data = json_decode(file_get_contents($log_file), true);
                            $log_data['success'][] = $log_message;
                            file_put_contents($log_file, json_encode($log_data));
                        }
                        
                        $success_message = 'Password has been reset successfully. You can now login with your new password.';
                        $identifier = ''; // Clear the form
                    } else {
                        $error_message = 'Failed to reset password. Please try again.';
                    }
                } else {
                    $error_message = 'No account found with that username or email.';
                }
            } catch (PDOException $e) {
                $error_message = 'Database error. Please try again later.';
                error_log("Password reset error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Asset Management System</title>
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
                <img src="../Configuration/3shape-logo.png" alt="Logo" class="reset-logo">
                <h1 class="reset-title">Reset Password</h1>
                <p class="reset-subtitle">Enter your username or email and set a new password</p>
            </div>
            
            <div class="reset-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                        <div class="mt-2">
                            <a href="../login.php" class="btn btn-sm btn-success">Go to Login</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="identifier" name="identifier" placeholder="Username or Email" value="<?php echo htmlspecialchars($identifier); ?>" required autofocus>
                            <label for="identifier">Username or Email</label>
                        </div>
                        
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
                            <label for="new_password">New Password</label>
                            <span class="password-toggle" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="text-muted mb-3 d-block">Password must be at least 6 characters</small>
                        
                        <div class="form-floating mb-3 position-relative">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                            <label for="confirm_password">Confirm Password</label>
                            <span class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        
                        <button type="submit" class="btn btn-reset">
                            <i class="fas fa-key me-2"></i> Reset Password
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="../login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
            
            <div class="reset-footer">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Asset Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility for new password
            const toggleNewPassword = document.getElementById('toggleNewPassword');
            const newPasswordField = document.getElementById('new_password');
            
            if (toggleNewPassword && newPasswordField) {
                toggleNewPassword.addEventListener('click', function() {
                    const type = newPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    newPasswordField.setAttribute('type', type);
                    
                    // Toggle eye icon
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
            
            // Toggle password visibility for confirm password
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordField = document.getElementById('confirm_password');
            
            if (toggleConfirmPassword && confirmPasswordField) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordField.setAttribute('type', type);
                    
                    // Toggle eye icon
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
            
            // Password strength indicator
            const passwordField = document.getElementById('new_password');
            const strengthIndicator = document.getElementById('passwordStrength');
            
            if (passwordField && strengthIndicator) {
                passwordField.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    // Length check
                    if (password.length >= 6) {
                        strength += 1;
                    }
                    
                    // Contains number
                    if (/\d/.test(password)) {
                        strength += 1;
                    }
                    
                    // Contains special character
                    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                        strength += 1;
                    }
                    
                    // Update strength indicator
                    strengthIndicator.className = 'password-strength';
                    
                    if (password.length === 0) {
                        strengthIndicator.style.width = '0';
                    } else if (strength === 1) {
                        strengthIndicator.classList.add('strength-weak');
                    } else if (strength === 2) {
                        strengthIndicator.classList.add('strength-medium');
                    } else {
                        strengthIndicator.classList.add('strength-strong');
                    }
                });
            }
            
            // Auto-hide alert after 5 seconds
            const alerts = document.querySelectorAll('.alert-danger');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
