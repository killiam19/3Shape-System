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
                    <span class="nav-section-title">Platform</span>
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
                    <span class="nav-section-title">Administration</span>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="#admin" class="nav-link" data-section="admin">
                                <i class="bi bi-gear"></i>
                                <span><?php echo __('admin_panel', $lang); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#users" class="nav-link" data-section="users">
                                <i class="bi bi-people"></i>
                                <span><?php echo __('user_management', $lang); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="nav-section">
                    <span class="nav-section-title">Repository</span>
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

            <!-- User Profile Section -->
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
                        <li><a class="dropdown-item language-selector" href="#" data-lang="en">🇺🇸 English</a></li>
                        <li><a class="dropdown-item language-selector" href="#" data-lang="es">🇪🇸 Español</a></li>
                        <li><a class="dropdown-item language-selector" href="#" data-lang="pl">🇵🇱 Polski</a></li>
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
                    <!-- Notifications -->
                    <div class="notification-container">
                        <button class="notification-btn" onclick="showModal()">
                            <i class="bi bi-bell"></i>
                            <span class="notification-badge">0</span>
                        </button>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="./View/Int_Registro_equipo.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg"></i>
                            <span class="d-none d-md-inline"><?php echo __('register_new_device', $lang); ?></span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content Sections -->
            <div class="content-wrapper">
                <!-- Dashboard Section -->
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
                                        <table class="table table-hover">
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

                                        <!-- User Information -->
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

                                        <!-- Status & Warranty -->
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
                                <button id="deleteAllButton" type="button" class="btn btn-danger btn-sm">
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

                <!-- Asset Adjustment Section -->
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

                    <!-- Quick Asset Search -->
                    <div class="row g-4 mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-search me-2"></i><?php echo __('quick_asset_search', $lang); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form class="row g-3" onsubmit="searchAsset(event)">
                                        <div class="col-md-6">
                                            <label class="form-label"><?php echo __('search_by_serial', $lang); ?></label>
                                            <input type="text" class="form-control" id="quickSearchSerial" placeholder="<?php echo __('enter_serial_number', $lang); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"><?php echo __('search_by_asset_name', $lang); ?></label>
                                            <input type="text" class="form-control" id="quickSearchName" placeholder="<?php echo __('enter_asset_name', $lang); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-search"></i> <?php echo __('search', $lang); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <div id="quickSearchResults" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Import Section -->
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

                <!-- Reports Section -->
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
                                    <a href="./Model/Acta_entrada.php" class="btn btn-success">
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
                                    <a href="./Model/Acta_salida.php" class="btn btn-warning">
                                        <i class="bi bi-download"></i> <?php echo __('generate', $lang); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Admin Section -->
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

                <!-- Documentation Section -->
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
    <script src="./View/Js/ModalReport.js"></script>
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

    <!-- Notification Modal -->
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
                    <div class="notification-container">
                        <?php
                        $jsonFilePath = './Model/Logs/session_messages.json';
                        $alerts = [];
                        if (file_exists($jsonFilePath)) {
                            $logData = json_decode(file_get_contents($jsonFilePath), true);
                            foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
                                if (!empty($logData[$type])) {
                                    foreach (array_slice($logData[$type], 0, 8) as $entry) {
                                        $message = is_array($entry) ? $entry['message'] : $entry;
                                        $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';
                                        $alerts[] = "<div class='notification-item p-3 mb-2 bg-light rounded'>{$message}{$timestamp}</div>";
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

    <!-- Admin Login Modal -->
    <div class="modal fade" id="adminLoginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('admin_login', $lang); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="./Admin/token.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <input type="password" name="passwordadmin" class="form-control" placeholder="<?php echo __('enter_password', $lang); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?php echo __('login', $lang); ?></button>
                        <div class="text-center mt-2">
                            <a href="./View/Int_forgot_pasword.php"><?php echo __('forgot_password', $lang); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include './View/Fragments/footer.php'; ?>
</body>
</html>
