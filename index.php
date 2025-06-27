<?php
include_once 'auth_check.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de idioma
$default_lang = 'en';
$available_langs = ['en', 'es', 'pl'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $default_lang;
}

$lang_file = './lang/' . $_SESSION['lang'] . '.json';
if (file_exists($lang_file)) {
    $lang = json_decode(file_get_contents($lang_file), true);
} else {
    $lang = json_decode(file_get_contents('./lang/en.json'), true);
}

function __($key, $lang) {
    return $lang[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('page_title', $lang); ?></title>
    <link rel="shortcut icon" href="./Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    
    <!-- CSS Dependencies -->
    <link href="./Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="./Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./View/Css/dashboard.css">
    <link rel="stylesheet" href="./View/Css/dark-mode.css">
    <link rel="stylesheet" href="./View/Css/button-styles.css">
    <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <!-- JavaScript Dependencies -->
    <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
    <script src="./Configuration/JQuery/sweetalert2.all.min.js" defer></script>
</head>
<body>
    <!-- Dashboard Layout -->
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="./Configuration/3shape-logo.png" alt="Logo" class="sidebar-logo">
                    <span class="logo-text"><?php echo __('page_title', $lang); ?></span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title"><?php echo __('platform', $lang); ?></span>
                    <ul class="nav-menu">
                        <li class="nav-item active">
                            <a href="#dashboard" class="nav-link" data-section="dashboard">
                                <i class="bi bi-speedometer2"></i>
                                <span><?php echo __('dashboard', $lang); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#assets" class="nav-link" data-section="assets">
                                <i class="bi bi-laptop"></i>
                                <span><?php echo __('assets', $lang); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#adjustment" class="nav-link" data-section="adjustment">
                                <i class="bi bi-arrow-left-right"></i>
                                <span><?php echo __('asset_adjustment', $lang); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#import" class="nav-link" data-section="import">
                                <i class="bi bi-upload"></i>
                                <span><?php echo __('import_data', $lang); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#reports" class="nav-link" data-section="reports">
                                <i class="bi bi-file-earmark-text"></i>
                                <span><?php echo __('reports', $lang); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager')): ?>
                <div class="nav-section">
                    <span class="nav-section-title"><?php echo __('administration', $lang); ?></span>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="#admin" class="nav-link" data-section="admin">
                                <i class="bi bi-gear"></i>
                                <span><?php echo __('admin_panel', $lang); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="nav-section">
                    <span class="nav-section-title"><?php echo __('repository', $lang); ?></span>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="#documentation" class="nav-link" data-section="documentation">
                                <i class="bi bi-book"></i>
                                <span><?php echo __('documentation', $lang); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Sección de Perfil de Usuario -->
            <div class="sidebar-footer">
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <div class="user-profile dropdown">
                    <a href="#" class="user-profile-link dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['username']); ?></span>
                            <span class="user-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'User'); ?></span>
                        </div>
                        <i class="bi bi-chevron-up"></i>
                    </a>
             <ul class="dropdown-menu dropdown-menu-end">
    <li><h6 class="dropdown-header"><?php echo __('language', $lang); ?></h6></li>
    <li>
        <a class="dropdown-item language-selector d-flex align-items-center" href="#" data-lang="en">
            <span class="fi fi-us flag-icon me-2"></span>
            <span>English</span>
        </a>
    </li>
    <li>
        <a class="dropdown-item language-selector d-flex align-items-center" href="#" data-lang="es">
            <span class="fi fi-es flag-icon me-2"></span>
            <span>Español</span>
        </a>
    </li>
    <li>
        <a class="dropdown-item language-selector d-flex align-items-center" href="#" data-lang="pl">
            <span class="fi fi-pl flag-icon me-2"></span>
            <span>Polski</span>
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
        <div class="dropdown-item">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="darkModeToggle">
                <label class="form-check-label" for="darkModeToggle"><?php echo __('dark_mode', $lang); ?></label>
            </div>
        </div>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
        <a class="dropdown-item" href="logout.php">
            <i class="bi bi-box-arrow-right me-2"></i><?php echo __('logout', $lang); ?>
        </a>
    </li>
</ul>

                </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-sidebar-toggle d-lg-none" id="mobileSidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="page-title" id="pageTitle"><?php echo __('dashboard', $lang); ?></h1>
                </div>
                <div class="header-right">
                    <!-- Notificaciones -->
                    <div class="notification-container">
                        <button class="notification-btn" onclick="showModal()">
                            <i class="bi bi-bell"></i>
                            <span class="notification-badge">0</span>
                        </button>
                    </div>
                    
                    <!-- Acciones Rápidas -->
                    <div class="quick-actions">
                        <a href="./View/Int_Registro_equipo.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg"></i>
                            <span class="d-none d-md-inline"><?php echo __('register_new_device', $lang); ?></span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Secciones de contenido -->
            <div class="content-wrapper">
                <!-- Sección de Dashboard -->
                <section id="dashboard-section" class="content-section active">
                    <div class="section-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2><?php echo __('dashboard_overview', $lang); ?></h2>
                                <p class="text-muted"><?php echo __('welcome_message', $lang); ?></p>
                            </div>
                            <div class="notification-alert" id="notificationAlert" style="display: none;">
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <span id="notificationText"></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <?php
                            include_once './Configuration/Connection.php';
                            try {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vista_equipos_usuarios");
                                $stmt->execute();
                                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                                $Totalclient = $resultado['total'];
                            } catch (PDOException $e) {
                                $Totalclient = 0;
                                error_log("Error counting assets: " . $e->getMessage());
                            }
                            ?>
                            <div class="stats-card">
                                <div class="stats-icon bg-primary">
                                    <i class="bi bi-laptop"></i>
                                </div>
                                <div class="stats-content">
                                    <h3><?php echo number_format($Totalclient, 0, ',', '.'); ?></h3>
                                    <p><?php echo __('total_assets', $lang); ?></p>
                                    <span class="stats-trend positive">
                                        <i class="bi bi-arrow-up"></i> 12%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as active FROM vista_equipos_usuarios WHERE user_status = 'Active User'");
                                $stmt->execute();
                                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                                $activeAssets = $resultado['active'];
                            } catch (PDOException $e) {
                                $activeAssets = 0;
                            }
                            ?>
                            <div class="stats-card">
                                <div class="stats-icon bg-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="stats-content">
                                    <h3><?php echo number_format($activeAssets, 0, ',', '.'); ?></h3>
                                    <p><?php echo __('active_assets', $lang); ?></p>
                                    <span class="stats-trend positive">
                                        <i class="bi bi-arrow-up"></i> 8%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as expired FROM equipos WHERE expired = 1");
                                $stmt->execute();
                                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                                $expiredAssets = $resultado['expired'];
                            } catch (PDOException $e) {
                                $expiredAssets = 0;
                            }
                            ?>
                            <div class="stats-card">
                                <div class="stats-icon bg-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="stats-content">
                                    <h3><?php echo number_format($expiredAssets, 0, ',', '.'); ?></h3>
                                    <p><?php echo __('expired_warranties', $lang); ?></p>
                                    <span class="stats-trend negative">
                                        <i class="bi bi-arrow-down"></i> 3%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <?php
                            try {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as stock FROM vista_equipos_usuarios WHERE user_status = 'Stock'");
                                $stmt->execute();
                                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                                $stockAssets = $resultado['stock'];
                            } catch (PDOException $e) {
                                $stockAssets = 0;
                            }
                            ?>
                            <div class="stats-card">
                                <div class="stats-icon bg-info">
                                    <i class="bi bi-box"></i>
                                </div>
                                <div class="stats-content">
                                    <h3><?php echo number_format($stockAssets, 0, ',', '.'); ?></h3>
                                    <p><?php echo __('in_stock', $lang); ?></p>
                                    <span class="stats-trend neutral">
                                        <i class="bi bi-dash"></i> 0%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Grid -->
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?php echo __('recent_assets', $lang); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tabla-equipos" class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo __('asset_name', $lang); ?></th>
                                                    <th><?php echo __('serial_number', $lang); ?></th>
                                                    <th><?php echo __('user_status', $lang); ?></th>
                                                    <th><?php echo __('last_user', $lang); ?></th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    $stmt = $pdo->prepare("SELECT * FROM vista_equipos_usuarios ORDER BY cedula DESC LIMIT 5");
                                                    $stmt->execute();
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row['assetname']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
                                                        echo "<td><span class='badge bg-" . ($row['user_status'] == 'Active User' ? 'success' : ($row['user_status'] == 'Stock' ? 'warning' : 'secondary')) . "'>" . htmlspecialchars($row['user_status']) . "</span></td>";
                                                        echo "<td>" . htmlspecialchars($row['last_user']) . "</td>";
                                                        echo "<td><button class='btn btn-sm btn-outline-primary'><i class='bi bi-eye'></i></button></td>";
                                                        echo "</tr>";
                                                    }
                                                } catch (PDOException $e) {
                                                    echo "<tr><td colspan='5' class='text-center text-muted'>Error loading data</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?php echo __('quick_actions', $lang); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="./View/Int_Registro_equipo.php" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-2"></i><?php echo __('register_new_device', $lang); ?>
                                        </a>
                                        <a href="#import" class="btn btn-outline-secondary" data-section="import">
                                            <i class="bi bi-upload me-2"></i><?php echo __('import_data', $lang); ?>
                                        </a>
                                        <a href="#reports" class="btn btn-outline-info" data-section="reports">
                                            <i class="bi bi-file-earmark-text me-2"></i><?php echo __('generate_report', $lang); ?>
                                        </a>
                                        <button class="btn btn-outline-warning" onclick="showModal()">
                                            <i class="bi bi-bell me-2"></i><?php echo __('view_notifications', $lang); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Assets Section -->
                <section id="assets-section" class="content-section">
                    <div class="section-header">
                        <h2><?php echo __('asset_management', $lang); ?></h2>
                        <p class="text-muted"><?php echo __('manage_all_assets', $lang); ?></p>
                    </div>

                    <!-- Filtros de búsqueda -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i><?php echo __('filter_assets', $lang); ?>
                            </h5>
                            <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                        <div class="collapse" id="filterCollapse">
                            <div class="card-body">
                                <form method="GET" action="./Controller/busqueda_Multicriterio.php">
                                    <div class="row g-3">
                                        <!-- Asset Information -->
                                        <div class="col-lg-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <i class="bi bi-laptop me-2"></i><?php echo __('asset_information', $lang); ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('asset_name', $lang); ?></label>
                                                        <input type="text" name="search_assetname" class="form-control" placeholder="Enter asset name" oninput="this.value = this.value.toUpperCase()">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('serial_number', $lang); ?></label>
                                                        <input type="text" name="search_serial" class="form-control" placeholder="Enter serial number" oninput="this.value = this.value.toUpperCase()">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('user_status', $lang); ?></label>
                                                        <select name="search_user_status[]" class="form-select">
                                                            <option value="0"><?php echo __('select_user_status', $lang); ?></option>
                                                            <option value="Stock"><?php echo __('stock', $lang); ?></option>
                                                            <option value="Active User"><?php echo __('active_user', $lang); ?></option>
                                                            <option value="Old User"><?php echo __('old_user', $lang); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Información del usuario -->
                                        <div class="col-lg-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <i class="bi bi-person me-2"></i><?php echo __('user_information', $lang); ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('user_id', $lang); ?></label>
                                                        <input type="number" name="search_cedula" class="form-control" placeholder="Enter user ID" min="0">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('last_user', $lang); ?></label>
                                                        <input type="text" name="search_user" class="form-control" placeholder="Enter last user">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('job_title', $lang); ?></label>
                                                        <input type="text" name="search_job_title" class="form-control" placeholder="Enter job title">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Estado y Fechas -->
                                        <div class="col-lg-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title mb-0">
                                                        <i class="bi bi-shield-check me-2"></i><?php echo __('status_warranty', $lang); ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('status_change', $lang); ?></label>
                                                        <input type="text" name="search_status_change" class="form-control" placeholder="Enter status change">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('entry_date', $lang); ?></label>
                                                        <input type="date" name="search_entry_date" class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?php echo __('departure_date', $lang); ?></label>
                                                        <input type="date" name="search_departure_date" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success me-2">
                                            <i class="bi bi-search"></i> <?php echo __('search', $lang); ?>
                                        </button>
                                        <button type="reset" class="btn btn-secondary me-2">
                                            <i class="bi bi-arrow-clockwise"></i> <?php echo __('clear', $lang); ?>
                                        </button>
                                        <a href="./Controller/busqueda_Multicriterio.php" class="btn btn-primary">
                                            <i class="bi bi-arrow-repeat"></i> <?php echo __('refresh', $lang); ?>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><?php echo __('all_assets', $lang); ?></h5>
                            <div class="btn-group">
                                <a href="./View/Int_Registro_equipo.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg"></i> <?php echo __('register_new_device', $lang); ?>
                                </a>
                                <button id="procesarSeleccionados" type="button" class="btn btn-warning btn-sm">
                                    <i class="fas fa-cog"></i> <?php echo __('select_process', $lang); ?>
                                </button>
                                <button id="deleteAllButton" type="button" class="btn btn-danger btn-sm" onclick="deleteAllRecords()">
                                    <i class="bi bi-trash"></i> <?php echo __('delete_all_logs', $lang); ?>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="mainTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" class="form-check-input select-all-checkbox" title="Select All"></th>
                                            <th><?php echo __('asset_name', $lang); ?></th>
                                            <th><?php echo __('serial_number', $lang); ?></th>
                                            <th><?php echo __('user_status', $lang); ?></th>
                                            <th><?php echo __('last_user', $lang); ?></th>
                                            <th><?php echo __('job_title', $lang); ?></th>
                                            <th>ID</th>
                                            <th><?php echo __('entry_date', $lang); ?></th>
                                            <th><?php echo __('departure_date', $lang); ?></th>
                                            <?php
                                            $selectedColumns = $_SESSION['selected_fields'] ?? [];
                                            foreach ($selectedColumns as $column) {
                                                echo "<th class='text-center'>" . htmlspecialchars($column) . "</th>";
                                            }
                                            ?>
                                            <th class="text-center"><i class="bi bi-arrow-repeat" title="Update"></i></th>
                                            <th class="text-center"><i class="bi bi-eye" title="Preview PDF"></i></th>
                                            <th class="text-center"><i class="bi bi-check-square" title="Status And Observations"></i></th>
                                            <th class="text-center"><i class="bi bi-trash" title="Delete"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php include './Model/Main_Table.php'; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección de ajustes de equipos (Cambiar equipo y fecha de salida) -->
                <section id="adjustment-section" class="content-section">
                    <div class="section-header">
                        <h2><?php echo __('asset_adjustment', $lang); ?></h2>
                        <p class="text-muted"><?php echo __('asset_adjustment_description', $lang); ?></p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card text-center h-100">
                                <div class="card-body d-flex flex-column">
                                    <i class="bi bi-arrow-repeat text-primary fs-1 mb-3"></i>
                                    <h5 class="card-title"><?php echo __('change_asset', $lang); ?></h5>
                                    <p class="card-text flex-grow-1"><?php echo __('change_asset_description', $lang); ?></p>
                                    <div class="mt-auto">
                                        <a href="./View/Int_entrada.php" class="btn btn-primary">
                                            <i class="bi bi-arrow-repeat me-2"></i><?php echo __('change_asset', $lang); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card text-center h-100">
                                <div class="card-body d-flex flex-column">
                                    <i class="bi bi-box-arrow-up text-warning fs-1 mb-3"></i>
                                    <h5 class="card-title"><?php echo __('output_asset', $lang); ?></h5>
                                    <p class="card-text flex-grow-1"><?php echo __('output_asset_description', $lang); ?></p>
                                    <div class="mt-auto">
                                        <a href="./View/Int_salida.php" class="btn btn-warning">
                                            <i class="bi bi-box-arrow-up me-2"></i><?php echo __('output_asset', $lang); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección de Importación -->
                <section id="import-section" class="content-section">
                    <div class="section-header">
                        <h2><?php echo __('import_asset_data', $lang); ?></h2>
                        <p class="text-muted"><?php echo __('upload_description', $lang); ?></p>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="./Controller/Importar_BD.php" method="POST" enctype="multipart/form-data">
                                <?php include_once "./Controller/validation_message.php"; ?>
                                
                                <div class="upload-area" id="dropZone">
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-upload"></i>
                                    </div>
                                    <h4><?php echo __('drop_file', $lang); ?></h4>
                                    <p class="text-muted"><?php echo __('or_browse', $lang); ?></p>
                                    <input type="file" name="Proyeccion_garan" class="d-none" accept=".csv, .txt" required id="file-input">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file-input').click()">
                                        <?php echo __('select_file', $lang); ?>
                                    </button>
                                </div>
                                
                                <div class="mt-3">
                                    <div id="file-name" class="text-muted"><?php echo __('no_file_selected', $lang); ?></div>
                                    <button type="submit" class="btn btn-primary mt-2">
                                        <i class="bi bi-upload me-2"></i><?php echo __('upload_file', $lang); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Sección de Informes -->
                <section id="reports-section" class="content-section">
                    <div class="section-header">
                        <h2><?php echo __('reports', $lang); ?></h2>
                        <p class="text-muted"><?php echo __('generate_various_reports', $lang); ?></p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-pdf text-danger fs-1 mb-3"></i>
                                    <h5 class="card-title"><?php echo __('general_report', $lang); ?></h5>
                                    <p class="card-text"><?php echo __('complete_asset_report', $lang); ?></p>
                                    <a href="./Model/GeneralReport.php" target="_blank" class="btn btn-danger">
                                        <i class="bi bi-download"></i> <?php echo __('generate', $lang); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-arrow-up text-success fs-1 mb-3"></i>
                                    <h5 class="card-title"><?php echo __('entry_certificate', $lang); ?></h5>
                                    <p class="card-text"><?php echo __('employee_entry_certificate', $lang); ?></p>
                                    <a href="./Model/Acta_entrada.php" target="_blank" class="btn btn-success">
                                        <i class="bi bi-download"></i> <?php echo __('generate', $lang); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-arrow-down text-warning fs-1 mb-3"></i>
                                    <h5 class="card-title"><?php echo __('departure_certificate', $lang); ?></h5>
                                    <p class="card-text"><?php echo __('employee_departure_certificate', $lang); ?></p>
                                    <a href="./Model/Acta_salida.php" target="_blank" class="btn btn-warning">
                                        <i class="bi bi-download"></i> <?php echo __('generate', $lang); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sección de Admin (Exclusiva) -->
                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager')): ?>
                <section id="admin-section" class="content-section">
                    <div class="section-header">
                        <h2><?php echo __('admin_panel', $lang); ?></h2>
                        <p class="text-muted"><?php echo __('administrative_functions', $lang); ?></p>
                    </div>

                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-gear fs-1 text-primary mb-3"></i>
                            <h5><?php echo __('administrator_access', $lang); ?></h5>
                            <p class="text-muted"><?php echo __('restricted_access', $lang); ?></p>
                            <button class="btn btn-primary" id="showAdminModal">
                                <i class="bi bi-unlock"></i> <?php echo __('access_admin_panel', $lang); ?>
                            </button>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Sección de documentación -->
                <section id="documentation-section" class="content-section">
                    <div class="section-header">
                        <h2><?php echo __('documentation', $lang); ?></h2>
                        <p class="text-muted"><?php echo __('system_documentation', $lang); ?></p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-book me-2"></i><?php echo __('user_manual', $lang); ?>
                                    </h5>
                                    <p class="card-text"><?php echo __('complete_user_guide', $lang); ?></p>
                                    <a href="./View/Int_manual.html" target="_blank" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> <?php echo __('view', $lang); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-question-circle me-2"></i><?php echo __('faq', $lang); ?>
                                    </h5>
                                    <p class="card-text"><?php echo __('frequently_asked_questions', $lang); ?></p>
                                    <a href="./View/Int_faq.php" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> <?php echo __('view', $lang); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="./Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./Configuration/DataTables/datatables.min.js"></script>
    <script src="./View/Js/dashboard.js"></script>
    <script src="./View/Js/ModalReportGeneral.js"></script>
    <script src="./View/Js/dark-mode-toggle.js"></script>
    <script src="./View/Js/Select_proccess.js"></script>

    <!-- Modal de confirmación para procesos -->
    <div id="modalConfirmacion" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; display: none;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
            <button id="btnCerrarModal" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
            <p id="modalMensaje" style="color: black;"></p>
            <button id="btnAceptar" class="btn btn-secondary"><?php echo __('option_generate_departure', $lang); ?></button>
            <button id="btnCancelar" class="btn btn-secondary my-2"><?php echo __('option_create_entry', $lang); ?></button>
            <button id="btnOpcion3" class="btn btn-secondary my-2"><?php echo __('option_asset_information', $lang); ?></button>
            <button id="btnQR" class="btn btn-secondary my-2"><?php echo __('option_generate_qr', $lang); ?></button>
        </div>
    </div>

    <!-- Modal de Notificaciones -->
    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-bell me-2"></i><?php echo __('notification_history', $lang); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="<?php echo __('filter_notifications', $lang); ?>">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <select id="categoryFilter" class="form-select">
                                <option value="all">Todas las categorías</option>
                                <option value="success">Éxito</option>
                                <option value="error">Error</option>
                                <option value="error_message">Error</option>
                                <option value="warnings">Advertencia</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="date" id="dateFilter" class="form-control" placeholder="Filtrar por fecha">
                        </div>
                    </div>
                    <div class="notification-container" id="notificationHistoryContainer">
                        <?php
                        $jsonFilePath = './Model/Logs/session_messages.json';
                        $alerts = [];
                        if (file_exists($jsonFilePath)) {
                            $logData = json_decode(file_get_contents($jsonFilePath), true);
                            $typeLabels = [
                                'success' => '<span class="badge bg-success me-2">Éxito</span>',
                                'error' => '<span class="badge bg-danger me-2">Error</span>',
                                'error_message' => '<span class="badge bg-danger me-2">Error</span>',
                                'warnings' => '<span class="badge bg-warning text-dark me-2">Advertencia</span>'
                            ];
                            foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
                                if (!empty($logData[$type])) {
                                    foreach (array_slice($logData[$type], 0, 8) as $entry) {
                                        $message = is_array($entry) ? $entry['message'] : $entry;
                                        $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';
                                        $label = $typeLabels[$type] ?? '';
                                        $alerts[] = "<div class='notification-item p-3 mb-2 bg-light rounded'>{$label}{$message}{$timestamp}</div>";
                                    }
                                }
                            }
                        }
                        
                        if (!empty($alerts)) {
                            echo implode('', $alerts);
                        } else {
                            echo "<div class='text-center p-4'><i class='bi bi-inbox fs-1 text-muted'></i><p class='mt-3 text-muted'>" . __('no_notifications', $lang) . "</p></div>";
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="clearLogs()">
                        <i class="bi bi-trash"></i> <?php echo __('clean_history', $lang); ?>
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php echo __('close', $lang); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

   <!-- Modal de inicio de sesión de admin - Mejorado -->
<div class="modal fade" id="adminLoginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock me-2"></i><?php echo __('admin_login', $lang); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="./Admin/token.php" method="POST" id="adminLoginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    
                    <div class="mb-3">
                        <label for="passwordadmin" class="form-label"><?php echo __('admin_password', $lang); ?></label>
                        <div class="input-group">
                            <input type="password" 
                                   id="passwordadmin" 
                                   name="passwordadmin" 
                                   class="form-control" 
                                   placeholder="<?php echo __('enter_password', $lang); ?>" 
                                   required 
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="toggleAdminPassword"
                                    title="<?php echo __('show_hide_password', $lang); ?>">
                                <i class="bi bi-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i><?php echo __('admin_access_required', $lang); ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="adminLoginBtn">
                        <span class="btn-text">
                            <i class="bi bi-unlock me-2"></i><?php echo __('login', $lang); ?>
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            <?php echo __('logging_in', $lang); ?>...
                        </span>
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="./View/Int_forgot_pasword.php" class="text-decoration-none">
                            <i class="bi bi-question-circle me-1"></i><?php echo __('forgot_password', $lang); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript para mejorar la funcionalidad del modal de admin
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleBtn = document.getElementById('toggleAdminPassword');
    const passwordInput = document.getElementById('passwordadmin');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        });
    }
    
    // Form submission with loading state
    const adminForm = document.getElementById('adminLoginForm');
    const loginBtn = document.getElementById('adminLoginBtn');
    
    if (adminForm && loginBtn) {
        adminForm.addEventListener('submit', function() {
            const btnText = loginBtn.querySelector('.btn-text');
            const btnLoading = loginBtn.querySelector('.btn-loading');
            
            if (btnText && btnLoading) {
                btnText.classList.add('d-none');
                btnLoading.classList.remove('d-none');
                loginBtn.disabled = true;
            }
        });
    }
    
    // Reset form when modal is closed
    const adminModal = document.getElementById('adminLoginModal');
    if (adminModal) {
        adminModal.addEventListener('hidden.bs.modal', function() {
            const form = adminModal.querySelector('form');
            if (form) {
                form.reset();
                // Reset button state
                const btnText = loginBtn.querySelector('.btn-text');
                const btnLoading = loginBtn.querySelector('.btn-loading');
                if (btnText && btnLoading) {
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                    loginBtn.disabled = false;
                }
                // Reset password visibility
                passwordInput.setAttribute('type', 'password');
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        });
    }

    // Filtro de notificaciones por texto, categoría y fecha
    const notificationFilter = document.getElementById('notificationFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const dateFilter = document.getElementById('dateFilter');
    const notificationItems = document.querySelectorAll('#notificationHistoryContainer .notification-item');

    function filterNotifications() {
        const text = notificationFilter.value.toLowerCase();
        const category = categoryFilter.value;
        const date = dateFilter.value;
        notificationItems.forEach(item => {
            let show = true;
            // Filtrado por texto
            if (text && !item.textContent.toLowerCase().includes(text)) {
                show = false;
            }
            // Filtrado por categoría
            if (category !== 'all') {
                if (!item.innerHTML.includes('badge') || !item.innerHTML.includes(category)) {
                    // Detectar por clase de badge o por texto
                    const badgeMap = {
                        'success': 'Éxito',
                        'error': 'Error',
                        'error_message': 'Error',
                        'warnings': 'Advertencia'
                    };
                    if (!item.innerHTML.includes(badgeMap[category])) {
                        show = false;
                    }
                }
            }
            // Filtrado por fecha
            if (date) {
                // Buscar la fecha en el texto (formato YYYY-MM-DD)
                const regex = /(\d{4}-\d{2}-\d{2})/;
                const match = item.textContent.match(regex);
                if (!match || match[1] !== date) {
                    show = false;
                }
            }
            item.style.display = show ? '' : 'none';
        });
    }
    if (notificationFilter) notificationFilter.addEventListener('input', filterNotifications);
    if (categoryFilter) categoryFilter.addEventListener('change', filterNotifications);
    if (dateFilter) dateFilter.addEventListener('change', filterNotifications);
});
</script>
    <?php include './View/Fragments/footer.php'; ?>
</body>
</html>
