<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Initialize variables
$error_message = '';
$username = '';
$remember = false;

// Check if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
   header('Location: index.php');
   exit;
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Verify CSRF token
   if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
       $error_message = 'Security validation failed. Please try again.';
   } else {
       $username = trim($_POST['username'] ?? '');
       $password = $_POST['password'] ?? '';
       $remember = isset($_POST['remember']);
       
       // Basic validation
       if (empty($username) || empty($password)) {
           $error_message = 'Please enter both username and password.';
       } else {
           // Here you would connect to your database and verify credentials
           // This is a placeholder for your actual authentication logic
           include_once './Configuration/Connection.php';
           
           try {
               // Use prepared statement to prevent SQL injection - check both username and email
               $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
               $stmt->execute(['identifier' => $username]);
               $user = $stmt->fetch(PDO::FETCH_ASSOC);
               
               if ($user && password_verify($password, $user['password'])) {
                   // Login successful
                   $_SESSION['user_logged_in'] = true;
                   $_SESSION['user_id'] = $user['id'];
                   $_SESSION['username'] = $user['username'];
                   $_SESSION['user_role'] = $user['role'];
                   $_SESSION['user_full_name'] = $user['full_name'];
                   
                   // Set admin flag if role is admin
                   if ($user['role'] === 'admin') {
                       $_SESSION['is_admin'] = true;
                   }
                   
                   // Set remember me cookie if requested
                   if ($remember) {
                       $token = bin2hex(random_bytes(32));
                       // Store token in database associated with user
                       $stmt = $pdo->prepare("UPDATE users SET remember_token = :token WHERE id = :id");
                       $stmt->execute([
                           'token' => $token,
                           'id' => $user['id']
                       ]);
                       
                       // Set cookie to expire in 30 days
                       setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
                   }
                   
                   // Update last login time
                   $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                   $stmt->execute(['id' => $user['id']]);
                   
                   // Determine if login was via username or email
                   $login_type = ($username === $user['email']) ? 'email' : 'username';
                   $log_message = [
                       'message' => "User {$user['username']} logged in successfully via {$login_type}",
                       'timestamp' => date('Y-m-d H:i:s')
                   ];
                   
                   $log_file = './Model/Logs/session_messages.json';
                   if (file_exists($log_file)) {
                       $log_data = json_decode(file_get_contents($log_file), true);
                       $log_data['success'][] = $log_message;
                       file_put_contents($log_file, json_encode($log_data));
                   }
                   
                   // Redirect to dashboard
                   header('Location: index.php');
                   exit;
               } else {
                   // Login failed
                   $error_message = 'Invalid credentials provided.';
                   
                   $log_message = [
                       'message' => "Failed login attempt with identifier: {$username}",
                       'timestamp' => date('Y-m-d H:i:s')
                   ];
                   
                   $log_file = './Model/Logs/session_messages.json';
                   if (file_exists($log_file)) {
                       $log_data = json_decode(file_get_contents($log_file), true);
                       $log_data['error'][] = $log_message;
                       file_put_contents($log_file, json_encode($log_data));
                   }
               }
           } catch (PDOException $e) {
               $error_message = 'Database error. Please try again later.';
               error_log("Login error: " . $e->getMessage());
           }
       }
   }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login - Asset Management System</title>
   <link rel="shortcut icon" href="./Configuration/3shape-intraoral-logo.png" type="image/x-icon">
   
   <!-- Critical CSS -->
   <link href="./Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="./Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
   <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
   <link rel="stylesheet" href="./Configuration/JQuery/all.min.css">
   
   <style>
       html, body {
           height: 100%;
           margin: 0;
           padding: 0;
           overflow: hidden;
       }
       
       body {
           font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
       }
       
       .login-container {
           display: flex;
           height: 100vh;
           width: 100%;
       }
       
       .login-image {
           flex: 1;
           background-image: url('./View/assets/images/login_image.png');
           background-size: cover;
           background-position: center;
           position: relative;
       }
       
       .login-form-container {
           flex: 1;
           display: flex;
           flex-direction: column;
           justify-content: center;
           align-items: center;
           background-color: #f8f9fa;
           padding: 2rem;
       }
       
       .login-form {
           width: 100%;
           max-width: 400px;
           padding: 2rem;
       }
       
       .login-logo {
           width: 200px;
           height: 200px;
           margin-bottom: 2rem;
           object-fit: contain;
       }
       
       .form-floating {
           margin-bottom: 1rem;
       }
       
       .form-control {
           border-radius: 4px;
           padding: 0.75rem;
           border: 1px solid #ced4da;
       }
       
       .form-control:focus {
           border-color: #0d6efd;
           box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
       }
       
       .btn-login {
           width: 100%;
           padding: 0.75rem;
           background-color: #0d6efd;
           border: none;
           border-radius: 4px;
           color: white;
           font-weight: 500;
           margin-top: 1rem;
       }
       
       .btn-login:hover {
           background-color: #0b5ed7;
       }
       
       .login-footer {
           margin-top: 2rem;
           text-align: center;
           font-size: 0.875rem;
           color: #6c757d;
       }
       
       .login-links {
           display: flex;
           flex-direction: column;
           align-items: center;
           margin-top: 1rem;
           gap: 0.5rem;
       }
       
       .login-links a {
           color: #0d6efd;
           text-decoration: none;
           font-size: 0.875rem;
       }
       
       .login-links a:hover {
           text-decoration: underline;
       }
       
       .alert {
           border-radius: 4px;
           padding: 0.75rem 1rem;
           margin-bottom: 1rem;
           border: none;
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
       
       @media (max-width: 992px) {
           .login-container {
               flex-direction: column;
           }
           
           .login-image {
               height: 30vh;
           }
           
           .login-form-container {
               height: 70vh;
           }
       }
       
       @media (max-width: 576px) {
           .login-form {
               padding: 1rem;
           }
           
           .login-image {
               height: 20vh;
           }
           
           .login-form-container {
               height: 80vh;
           }
       }
   </style>
</head>
<body>
   <div class="login-container">
       <div class="login-image"></div>
       
       <div class="login-form-container">
           <div class="login-form">
               <div class="text-center mb-4">
                   <img src="./Configuration/3shape-logo.png" alt="Logo" class="login-logo">
                   <h1 class="h4 mb-2">Asset Management System</h1>
                   <p class="text-muted">Sign in to access your dashboard</p>
               </div>
               
               <?php if (!empty($error_message)): ?>
                   <div class="alert alert-danger" role="alert">
                       <i class="fas fa-exclamation-triangle me-2"></i>
                       <?php echo htmlspecialchars($error_message); ?>
                   </div>
               <?php endif; ?>
               
               <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                   <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                   
                   <div class="form-floating mb-3">
                       <input type="text" class="form-control" id="username" name="username" placeholder="Username or Email" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
                       <label for="username">Username or Email</label>
                   </div>
                   
                   <div class="form-floating mb-3 position-relative">
                       <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                       <label for="password">Password</label>
                       <span class="password-toggle" id="togglePassword">
                           <i class="fas fa-eye"></i>
                       </span>
                   </div>
                   
                   <div class="form-check mb-3">
                       <input class="form-check-input" type="checkbox" id="remember" name="remember" <?php echo $remember ? 'checked' : ''; ?>>
                       <label class="form-check-label" for="remember">
                           Remember Me
                       </label>
                   </div>
                   
                   <button type="submit" class="btn btn-login">
                       <i class="fas fa-sign-in-alt me-2"></i> Log In
                   </button>
               </form>
               
               <div class="login-links">
                   <a href="./View/reset_password.php">Lost your password?</a>
                   <a href="index.php">← Back to site</a>
               </div>
               
               <div class="login-footer">
                   <p>&copy; <?php echo date('Y'); ?> Asset Management System. All rights reserved.</p>
               </div>
           </div>
       </div>
   </div>
   
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           // Toggle password visibility
           const togglePassword = document.getElementById('togglePassword');
           const passwordField = document.getElementById('password');
           
           togglePassword.addEventListener('click', function() {
               const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
               passwordField.setAttribute('type', type);
               
               // Toggle eye icon
               this.querySelector('i').classList.toggle('fa-eye');
               this.querySelector('i').classList.toggle('fa-eye-slash');
           });
           
           // Auto-hide alert after 5 seconds
           const alert = document.querySelector('.alert');
           if (alert) {
               setTimeout(function() {
                   alert.style.opacity = '0';
                   alert.style.transition = 'opacity 0.5s';
                   setTimeout(function() {
                       alert.style.display = 'none';
                   }, 500);
               }, 5000);
           }
       });
   </script>
</body>
</html>
