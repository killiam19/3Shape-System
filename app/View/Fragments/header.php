<!-- Barra Busqueda -->
       <!-- Navbar -->
       <nav id="Navitem" class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="navbar-brand d-flex align-items-center">
                <img src="./Configuration/3shape-logo.png" alt="Logo" width="150" height="31">
            </a>

            <!-- Botón hamburguesa para móviles -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Contenido colapsable con múltiples funciones -->
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto">
                    <!-- Reporte PDF que contiene la información de todos los activos registrados -->
                    <a id="generatePDF" class="nav-link" data-url="./Model/GeneralReport.php" target="_blank" title="Generate General PDF">
                        <i class="far fa-file-pdf" style="color:#dc3545;"></i> <?php echo __('general_report', $lang); ?> <!-- General Report -->
                    </a>
                    <!-- Documento PDF con las actas de salida de la empresa de los empleados y activos -->
                    <a id="generateActaSalida" class="nav-link" data-url="./Model/Acta_salida.php" title="Generate Departure Certificate">
                        <i class="fas fa-file-export"></i> <?php echo __('departure_certificate', $lang); ?> <!-- Departure Certificate -->
                    </a>
                    <!-- Documento PDF con las actas de ingreso de la empresa de los empleados y activos que se les entregan -->
                    <a id="generateActaEntrada" class="nav-link" data-url="./Model/Acta_entrada.php" title="Generate Entry Certificate">
                        <i class="far fa-file-alt"></i> <?php echo __('entry_certificate', $lang); ?> <!-- Entry Certificate -->
                    </a>
                    <!-- Formulario para cambiar de aquipo a un usuario  -->
                    <a href="./View/Int_entrada.php" class="nav-link" title="Change Asset">
                        <i class="bi bi-arrow-repeat"></i> <?php echo __('change_asset', $lang); ?> <!-- Change Asset -->
                    </a>
                    <!-- Formulario para asignar una fecha de salida de un empleado y un activo-->
                    <a href="./View/Int_salida.php" class="nav-link" title="Output Asset">
                        <i class="bi bi-box-arrow-up"></i> <?php echo __('output_asset', $lang); ?> <!-- Output Asset -->
                    </a>
                     
                    <!-- Botón de Campana para mostrar modal de notificaciones -->
                    <li class="nav-item bell-container">
                        <div class="bell-wrapper">
                            <a type="button" class="btn btn-outline-warning bell-button">
                                <i class="fa-solid fa-bell"></i>
                                <span class="notification-badge">0</span>
                            </a>
                        </div>
                    </li>
                      <!-- Cuenta del usuario con dropdown -->
                      <li class="nav-item dropdown ms-auto">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <!-- Language options -->
                                <li><h6 class="dropdown-header"><?php echo __('language', $lang); ?></h6></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="en">English</a></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="es">Español</a></li>
                                <li><a class="dropdown-item language-selector" href="#" data-lang="pl">Polski</a></li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Dark mode toggle -->
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="darkModeToggle">
                                            <label class="form-check-label" for="darkModeToggle"><?php echo __('dark_mode', $lang); ?></label>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Logout option -->
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i><?php echo __('logout', $lang); ?>
                                    </a>
                                </li>
                            </ul>
                        <?php else: ?>
                            <a href="login.php" class="nav-link" title="Login">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        <?php endif; ?>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-container">
        <!-- Header Section -->
        <header class="app-header">
            <h1 class="app-title animate__animated animate__fadeInDown"><?php echo __('main_title', $lang); ?></h1> <!-- Asset Management System -->
            <p class="app-subtitle animate__animated animate__fadeInUp"><?php echo __('welcome_message', $lang); ?></p> <!--Track, manage, and optimize your company assets efficiently-->
        </header>