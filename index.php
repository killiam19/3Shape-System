<?php
include_once 'auth_check.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión
}

// Configuración de idioma
$default_lang = 'en';
$available_langs = ['en', 'es', 'pl'];

// Determinar idioma actual
if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $default_lang;
}

// Cargar archivo de idioma al index
$lang_file = './lang/' . $_SESSION['lang'] . '.json';
if (file_exists($lang_file)) {
    $lang = json_decode(file_get_contents($lang_file), true);
} else {
    $lang = json_decode(file_get_contents('./lang/en.json'), true);
}

// Función de traducción
function __($key, $lang) {
    return $lang[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('page_title', $lang); ?></title> <!-- Asset Management System -->
    <link rel="shortcut icon" href="./Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <!-- Carga de jQuery primero para evitar errores de dependencia -->
    <script src="./Configuration/JQuery/jquery-3.7.1.js"></script>
    <!-- Consolidación de CSS -->
    <link href="./Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="./Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./View/Css/index.css">
    <link rel="stylesheet" href="./View/Css/dark-mode.css">
    <link rel="stylesheet" href="./View/Css/dropdown-fix.css">
    <link rel="stylesheet" href="./View/Css/Datatable.css">
    <link rel="stylesheet" href="./View/Css/button-styles.css">
    <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <!-- Carga diferida de scripts no críticos -->
    <script src="./Configuration/JQuery/sweetalert2.all.min.js" defer></script>
    <link rel="stylesheet" href="./View/Css/navbar.css">
    <link rel="stylesheet" href="./View/Css/search-form.css">
</head>
<body>
    <!-- Header Section -->
<header>
    <?php include './View/Fragments/header.php'; ?>
</header>
    <div class="row align-items-center justify-content-center mb-3">

        <div class="col-md-3 mb-2 my-5">
            <!--Archivo para traer los datos en caso de error o alerta y confirmacion de subida de datos-->
            <?php
            include_once "./Controller/validation_message.php";
            ?>
            <script src="./View/Js/qrcode.js"></script>
            <script src="./View/Js/animations.js"></script>
            <br>
        </div>
        <div class="row g-4">
            <!-- Stats Card -->
            <div class="col-lg-4 col-md-6">
                <?php
                include_once './Configuration/Connection.php';

                try {
                    // Consulta optimizada para contar el número de registros
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vista_equipos_usuarios");
                    $stmt->execute();
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Total de registros
                    $Totalclient = $resultado['total'];
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>
                <div class="stats-card animate__animated animate__fadeInLeft">
                <div class="stats-title"><?php echo __('total_assets', $lang); ?></div> <!-- Total Assets -->
                    <div class="stats-value"><?php echo number_format($Totalclient, 0, ',', '.'); ?></div>
                    <div class="stats-description"><?php echo __('registered_in_system', $lang); ?></div> <!-- Registered in the system -->
                </div>
            </div>

              <!-- Upload Card -->
              <div class="col-lg-8 col-md-6">
                <div class="upload-card animate__animated animate__fadeInRight">
                    <h2 class="upload-title"><?php echo __('import_asset_data', $lang); ?></h2><!-- Import Asset Data -->
                    <p class="upload-description"><?php echo __('upload_description', $lang); ?></p><!-- Upload your CSV or TXT file to import asset information into the system. -->
                    
                    <form action="./Controller/Importar_BD.php" method="POST" enctype="multipart/form-data">
                        <?php include_once "./Controller/validation_message.php"; ?>
                        
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-zone-title"><?php echo __('drop_file', $lang); ?></div> <!-- Drop your file here -->
                            <div class="drop-zone-description"><?php echo __('or_browse', $lang); ?></div><!-- or click to browse -->
                            <input type="file" name="Proyeccion_garan" class="form-control d-none" accept=".csv, .txt" required id="file-input">
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="file-name" class="text-muted"><?php echo __('no_file_selected', $lang); ?></div><!-- No file selected -->
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i><?php echo __('upload_file', $lang); ?><!-- Upload File -->
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                     <!-- Search Section -->
        <section class="dashboard-section mt-4 animate__animated animate__fadeIn">
            <div class="section-header text-center">
                <h2 class="section-title"><i class="bi bi-filter me-2"></i><?php echo __('filter_assets', $lang); ?></h2> <!-- Filter Assets -->
                <p class="section-description"><?php echo __('filter_description', $lang); ?></p> <!-- Refine your search by selection one or multiple criteria to quickly find and asset information -->
            </div>

            <form method="GET" action="">
                <div class="row g-3">
                    <!-- Categoría 1: Información del Activo -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#collapseAssetInfo" role="button" aria-expanded="false" aria-controls="collapseAssetInfo">
                                <div>
                                    <i class="bi bi-laptop me-2"></i> <?php echo __('asset_information', $lang); ?> <!-- Asset Information -->
                                </div>
                                <i class="bi bi-chevron-down"></i>
                            </div>
                            <div class="collapse" id="collapseAssetInfo">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('asset_name', $lang); ?></label> <!-- Asset Name -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                            <input type="text" name="search_assetname" placeholder="Enter asset name" class="form-control" oninput="this.value = this.value.toUpperCase()" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('serial_number', $lang); ?></label> <!-- Serial Number -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" id="search_serial" name="search_serial" placeholder="Enter serial number" class="form-control" autocomplete="off" oninput="this.value = this.value.toUpperCase()">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('user_status', $lang); ?></label> <!-- User Status -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                            <select id="search_user_status" name="search_user_status[]" class="form-select">
    <option value="0"><?php echo __('select_user_status', $lang); ?></option> <!-- Select User Status -->
    <option value="Stock"><?php echo __('stock', $lang); ?></option> <!-- Stock -->
    <option value="Active User"><?php echo __('active_user', $lang); ?></option> <!-- Active User -->
    <option value="Old User"><?php echo __('old_user', $lang); ?></option> <!-- Old User -->
</select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 2: Información del Usuario -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#collapseUserInfo" role="button" aria-expanded="false" aria-controls="collapseUserInfo">
                                <div>
                                    <i class="bi bi-person me-2"></i> <?php echo __('user_information', $lang); ?>  <!-- User Information -->
                                </div>
                                <i class="bi bi-chevron-down"></i>
                            </div>
                            <div class="collapse" id="collapseUserInfo">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"> <?php echo __('user_id', $lang); ?></label> <!-- User ID -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="far fa-id-card"></i></span>
                                            <input type="number" id="search_cedula" name="search_cedula" placeholder="Enter user ID" class="form-control" autocomplete="off" min="0">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('last_user', $lang); ?></label><!-- Last User -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                            <input type="text" id="search_user" name="search_user" placeholder="Enter last user" class="form-control" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo __('job_title', $lang); ?></label> <!-- Job Title -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                            <input type="text" id="search_job_title" name="search_job_title" placeholder="Enter job title" class="form-control" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                      
                                        <div class="input-group">
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría 3: Estado y Garantía -->
<div class="col-lg-4 col-md-12">
    <div class="card h-100">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#collapseStatusWarranty" role="button" aria-expanded="false" aria-controls="collapseStatusWarranty">
            <div>
                <i class="bi bi-shield-check me-2"></i> <?php echo __('status_warranty', $lang); ?> <!-- Status & Warranty -->
            </div>
            <i class="bi bi-chevron-down"></i>
        </div>
  <div class="collapse" id="collapseStatusWarranty">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label"><?php echo __('status_change', $lang); ?></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                <input type="text" name="search_status_change" placeholder="Enter status change" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php echo __('entry_date', $lang); ?></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-sign-in-alt"></i></span>
              <input type="date" id="entry_date" name="search_entry_date" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label"><?php echo __('departure_date', $lang); ?></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-sign-out-alt"></i></span>
                <input type="date" id="departure_date" name="search_departure_date" class="form-control">
            </div>
        </div>
    </div>
</div>
    </div>
</div>
                <div class="action-buttons mt-4">
                    <button type="submit" class="btn btn-success action-button" id="search_btn">
                        <i class="bi bi-search"></i> <?php echo __('search', $lang); ?> <!-- Search -->
                    </button>
                    <button type="reset" class="btn btn-danger action-button" id="reset_btn">
                        <i class="bi bi-eraser"></i> <?php echo __('clear', $lang); ?><!-- Clear -->
                    </button>
                    <button type="button" class="btn btn-primary action-button" id="refresh_btn">
                        <i class="bi bi-arrow-clockwise"></i> <?php echo __('refresh', $lang); ?><!-- Refresh -->
                    </button>
                </div>
            </form>
            <div id="resultados3" class="text-center"></div>
            <div id="resultados2" class="text-center"></div>
            <div id="resultados" class="text-center"></div>
        </section>
                        <!-- Modal para mostrar las alertas -->
                        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                            <?php
                            // Optimización: Carga de notificaciones más eficiente
                            $jsonFilePath = './Model/Logs/session_messages.json';
                            $alerts = [];
                            $maxMessages = 8;

                            if (file_exists($jsonFilePath)) {
                                // Uso de caché para el archivo JSON
                                $cacheKey = 'notification_' . filemtime($jsonFilePath);
                                $logData = isset($_SESSION[$cacheKey]) ? $_SESSION[$cacheKey] : null;

                                if (!$logData) {
                                    $logData = json_decode(file_get_contents($jsonFilePath), true);
                                    $_SESSION[$cacheKey] = $logData;
                                }

                                // Procesar todos los tipos de mensajes
                                foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
                                    if (!empty($logData[$type])) {
                                        $alertClass = match ($type) {
                                            'success' => 'success',
                                            'error', 'error_message' => 'danger',
                                            'warnings' => 'warning'
                                        };

                                        $count = 0;
                                        foreach ($logData[$type] as $entry) {
                                            $message = is_array($entry) ? $entry['message'] : $entry;
                                            $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';
                                            $alerts[] = "<div class='p-3 mb-2 bg-light shadow-sm'>{$message} - {$timestamp}</div>";
                                            $count++;

                                            // Limit to $maxMessages messages per type
                                            if ($count >= $maxMessages) {
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <!--Modal de notificciones -->
                            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h2 class="modal-title fs-4">
                                            <i class="bi bi-bell-fill me-2"></i>
                                            <?php echo __('notification_history', $lang); ?><!-- Notification History -->
                                        </h2>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="Filter notifications...">
                                        <div class="notification-container">
                                            <?php if (!empty($alerts)): ?>
                                                <div class="notification-list">
                                                    <?= implode('', array_map(function ($alert) {
                                                        return str_replace(
                                                            'class=\'p-3 mb-2 bg-light shadow-sm\'',
                                                            'class=\'notification-item p-3 mb-2 bg-white rounded shadow-sm border-start border-4 border-primary\'',
                                                            $alert
                                                        );
                                                    }, $alerts)) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center p-4">
                                                    <i class="bi bi-inbox-fill fs-1 text-muted"></i>
                                                    <p class="mt-3 text-muted"><?php echo __('no_notifications', $lang); ?></p> <!-- No notifications recorded -->
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" onclick="clearLogs()">
                                            <i class="bi bi-trash-fill me-1"></i>
                                            <?php echo __('clean_history', $lang); ?><!-- Clean History -->
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-lg me-1"></i>
                                            <?php echo __('close', $lang); ?><!-- Close -->
                                        </button>
                                    </div>
                                </div>
                                <script>
                                    // Filter notifications
                                    $('#notificationFilter').on('keyup', function() {
                                        const filterValue = $(this).val().toLowerCase();
                                        $('.modal-body div').filter(function() {
                                            $(this).toggle($(this).text().toLowerCase().indexOf(filterValue) > -1);
                                        });
                                    });
                                </script>
                            </div>
                        </div>

                        <script>
                            function clearLogs() {
                                if (confirm("Are you sure you want to delete all records?")) {
                                    fetch('./Model/clear_logs.php', {
                                            method: 'POST'
                                        })
                                        .then(response => response.text())
                                        .then(data => {
                                            alert(data); // Mostrar respuesta del servidor
                                            location.reload(); // Recargar la página para ver cambios
                                        })
                                        .catch(error => console.error('Error:', error));
                                }
                            }
                        </script>

                        <script>
                            // Botón de Clear
                            document.getElementById('reset_btn').addEventListener('click', function() {
                                const textInputs = document.querySelectorAll('input[type="text"]');
                                const numberInputs = document.querySelectorAll('input[type="number"]');
                                const selectElements = document.querySelectorAll('select');

                                textInputs.forEach(input => input.value = '');
                                numberInputs.forEach(input => input.value = '');
                                selectElements.forEach(select => select.selectedIndex = 0);
                            });

                            // Botón de Refresh
                            document.getElementById('refresh_btn').addEventListener('click', function() {
                                window.location.href = window.location.pathname;
                            });
                        </script>
                    </div>
                  
            </div>
            <div id="resultados3" class="text-center"></div>
            <div id="resultados2" class="text-center"></div>
            <div id="resultados" class="text-center"></div>
            <script>
                //Script automatico para la busqueda del ultimo usuario
                document.getElementById('search_user').addEventListener('input', function() {
                    const value = this.value;
                    if (value.length > 0) {
                        this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');
                    }
                });
            </script>
            <script>
                //Coment
                //Script para visualizar la contraseña
                // Password visibility toggle function
                document.addEventListener('DOMContentLoaded', function() {
                    // Create toggle button for password visibility
                    const passwordField = document.getElementById('passwordadmin');
                    if (passwordField) {
                        const formGroup = passwordField.parentElement;

                        // Create and insert toggle button
                        const toggleButton = document.createElement('button');
                        toggleButton.type = 'button';
                        toggleButton.className = 'btn btn-outline-secondary position-absolute end-0 top-0 h-100 d-flex align-items-center px-2';
                        toggleButton.style.borderTopLeftRadius = '0';
                        toggleButton.style.borderBottomLeftRadius = '0';
                        toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
                        toggleButton.setAttribute('title', 'Show/Hide Password');

                        // Create a wrapper div for the input with position relative
                        const inputWrapper = document.createElement('div');
                        inputWrapper.className = 'position-relative';

                        // Move the password input into the wrapper
                        passwordField.parentNode.insertBefore(inputWrapper, passwordField);
                        inputWrapper.appendChild(passwordField);
                        inputWrapper.appendChild(toggleButton);

                        // Add event listener for the toggle button
                        toggleButton.addEventListener('click', function() {
                            if (passwordField.type === 'password') {
                                passwordField.type = 'text';
                                toggleButton.innerHTML = '<i class="bi bi-eye-slash"></i>';
                            } else {
                                passwordField.type = 'password';
                                toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
                            }
                        });
                    }
                });
            </script>
            <script src="./View/Js/Automatic_search.js"></script>
        </div>
        <div class="col-md-4">
            <?php
            include_once './Controller/busqueda_Multicriterio.php';
            ?>
        </div>
    </div>
    <!--Inputs de Busqueda-->
    </div>
    </div>
    </form>
    <div class="table-responsive my-5">
        <!--Boton para registrar un nuevo equipo-->

        <!--Tabla Principal donde se muestran los datos en este caso los activos de la empresa-->
        <div class="table-container">
            <?php
            $selectedColumns = isset($_SESSION['selected_fields']) ? $_SESSION['selected_fields'] : [];
            ?>
            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <a href="./View/Int_Registro_equipo.php" class="shadow btn btn-secondary "><i class="fas fa-plus"></i>
                    <?php echo __('register_new_device', $lang); ?></a>
                <button id="deleteAllButton" type="button" class="shadow btn btn-danger ">
                    <i class="fas fa-trash-alt"></i>
                    <?php echo __('delete_all_logs', $lang); ?></button>
                <!-- Scripts para el modal de administración -->
                <script>
                    document.getElementById('deleteAllButton').addEventListener('click', function() {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action will delete all logs permanently!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6d6d6d',
                            confirmButtonText: 'Yes, delete all!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = './Controller/delete_regist.php';
                            }
                        });
                    });
                </script>

            </div>
            <style>
                .sync-icon {
                    transition: transform 0.3s ease;
                }

                .btn:hover .sync-icon {
                    transform: rotate(30deg);
                }
            </style>
            <table id="mainTable" class="shadow-lg table table-bordered table-striped fade-in">
                <thead class="text-center">
                    <tr class="align-items-center text-center">
                        <!--Columnas de la tabla-->
                        <th>@</th>
                        <th><?php echo __('asset_name', $lang); ?></th> <!-- Asset Name -->
                        <th><?php echo __('serial_number', $lang); ?></th><!-- Serial Number -->
                        <th><?php echo __('user_status', $lang); ?></th><!-- User Status -->
                        <th><?php echo __('last_user', $lang); ?></th><!-- Last User -->
                        <th><?php echo __('job_title', $lang); ?></th><!-- Job Title -->
                        <th>ID</th>
                        <th><?php echo __('entry_date', $lang); ?></th><!--Entry Date -->
                        <th><?php echo __('departure_date', $lang); ?></th><!--Departure Date -->
                        <?php
                        $selectedColumns = $_SESSION['selected_fields'] ?? [];
                        $headerTemplate = "<th class='text-center'>%s</th>";
                        $headers = implode('', array_map(function ($column) use ($headerTemplate) {
                            return sprintf($headerTemplate, $column);
                        }, $selectedColumns));
                        echo $headers;
                        ?>
                        <th title="Update" class="text-center"><i class="fas fa-sync-alt"></i></th>
                        <th title="Preview PDF" class="text-center"><i class="fas fa-eye"></i></th>
                        <th title="Status And Observations" class="text-center"><i class="far fa-check-square"></i></th>
                        <th title="Delete" class="text-center"><i class="fa fa-times"></i></th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php
                    include './Model/Main_Table.php';
                    ?>
                </tbody>
                <!--Ventana emergente donde se selecciona las opcion de select process-->
                <div id="modalConfirmacion" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; display: none;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
                        <button id="btnCerrarModal" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                        <p id="modalMensaje" style="color: black;"></p>
                        <button id="btnAceptar" class="btn btn-secondary"> <?php echo __('option_generate_departure', $lang); ?> </button><!-- Option 1:Generate Departure -->
                        <button id="btnCancelar" class="btn btn-secondary my-2"> <?php echo __('option_create_entry', $lang); ?> </button><!-- Option 2:Create Entry -->
                        <button id="btnOpcion3" class="btn btn-secondary my-2"> <?php echo __('option_asset_information', $lang); ?> </button><!-- Option 3:Asset Information -->
                        <button id="btnQR" class="btn btn-secondary my-2"> <?php echo __('option_generate_qr', $lang); ?> </button><!-- Option 4: Generate QR -->
                    </div>
                </div>
                <style>
                    /* Animación de giro para el icono */
                    @keyframes spin {
                        0% {
                            transform: rotate(0deg);
                        }

                        100% {
                            transform: rotate(360deg);
                        }
                    }

                    /* Aplica la animación al icono cuando se hace hover en el botón */
                    #procesarSeleccionados:hover .fa-cog {
                        animation: spin 2s linear infinite;
                        display: inline-block;
                        /* Necesario para transformaciones */
                    }

                    /* Opcional: Animación cuando se hace clic (como en tu código JS) */
                    .fa-cog.spin {
                        animation: spin 2s linear infinite;
                    }
                </style>
                <script src="./Configuration/JQuery/qrcode.min.js"></script>
                <script src="./View/Js/Select_proccess.js"></script>
            </table>
            <br>
            <br>

<?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager')): ?>
           <!-- Admin Access Section -->
        <section class="dashboard-section mt-4 animate__animated animate__fadeIn">
            <div class="container">
                <div class="card shadow-sm border-primary mx-auto" style="max-width: 500px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-shield me-2"></i><?php echo __('administrator_access', $lang); ?><!-- Administrator Access -->
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="card-text mb-4">Press <kbd>Ctrl</kbd> + <kbd>Q</kbd> to login as administrator</p>
                        <a href="#" id="showAdminModal" class="btn btn-outline-primary">
                            <i class="fas fa-lock me-2"></i><?php echo __('manager_login', $lang); ?><!-- Manager Login -->
                        </a>
                    </div>
                    <div class="card-footer bg-light text-muted small">
                        <i class="bi bi-info-circle me-1"></i><?php echo __('restricted_access', $lang); ?><!-- Restricted access for authorized personnel only -->
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php endif; ?>

        <!-- Modal para mostrar las alertas -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <?php
        // Optimización: Carga de notificaciones más eficiente
        $jsonFilePath = './Model/Logs/session_messages.json';
        $alerts = [];
        $maxMessages = 8;

        if (file_exists($jsonFilePath)) {
            // Uso de caché para el archivo JSON
            $cacheKey = 'notification_' . filemtime($jsonFilePath);
            $logData = isset($_SESSION[$cacheKey]) ? $_SESSION[$cacheKey] : null;

            if (!$logData) {
                $logData = json_decode(file_get_contents($jsonFilePath), true);
                $_SESSION[$cacheKey] = $logData;
            }

            // Procesar todos los tipos de mensajes
            foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
                if (!empty($logData[$type])) {
                    $alertClass = match ($type) {
                        'success' => 'success',
                        'error', 'error_message' => 'danger',
                        'warnings' => 'warning'
                    };

                    $count = 0;
                    foreach ($logData[$type] as $entry) {
                        $message = is_array($entry) ? $entry['message'] : $entry;
                        $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';
                        $alerts[] = "<div class='p-3 mb-2 bg-light shadow-sm'>{$message} - {$timestamp}</div>";
                        $count++;

                        // Limit to $maxMessages messages per type
                        if ($count >= $maxMessages) {
                            break;
                        }
                    }
                }
            }
        }
        ?>
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h2 class="modal-title fs-4">
                        <i class="bi bi-bell-fill me-2"></i>
                        Notification History
                    </h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="Filter notifications...">
                    <div class="notification-container">
                        <?php if (!empty($alerts)): ?>
                            <div class="notification-list">
                                <?= implode('', array_map(function ($alert) {
                                    return str_replace(
                                        'class=\'p-3 mb-2 bg-light shadow-sm\'',
                                        'class=\'notification-item p-3 mb-2 bg-white rounded shadow-sm border-start border-4 border-primary\'',
                                        $alert
                                    );
                                }, $alerts)) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <i class="bi bi-inbox-fill fs-1 text-muted"></i>
                                <p class="mt-3 text-muted">No notifications recorded.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="clearLogs()">
                        <i class="bi bi-trash-fill me-1"></i>
                        <?php echo __('clean_history', $lang); ?> <!-- Clean History -->
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        <?php echo __('close', $lang); ?> <!-- Close -->
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Admin Login -->
    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminLoginModalLabel"><?php echo __('admin_login', $lang); ?> <i class="fas fa-user-alt"></i></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="./Admin/token.php" method="POST">
                        <?php
                        // Generar un token CSRF si no existe
                        if (empty($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group mb-3">
                            <input type="password" id="passwordadmin" name="passwordadmin" placeholder="Enter your password" class="form-control shadow-lg border border-secondary" required autocomplete="off">
                            <div class="invalid-feedback">Please enter a valid password.</div>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 border border-secondary"><?php echo __('login', $lang); ?> <i class="fas fa-key"></i></button>
                        <div class="text-center mt-2">
                            <a href="./View/Int_forgot_pasword.php" class="text-decoration-none"><?php echo __('forgot_password', $lang); ?></a> <!-- Forgot Password? -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
        <!-- Modal Admin -->
        <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="adminModalLabel"><?php echo __('admin_tools', $lang); ?></h5> <!-- Admin Tools -->
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dynamicForms">
                            <!-- Los formularios se cargarán aquí dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
            <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 56px; height: 56px;" id="adminButton">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    <?php endif; ?>

     <!-- Load Bootstrap JS before other scripts -->
    <script src="./Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>

     <!-- Scripts -->
     <script src="./View/Js/dropdown-fix.js"></script>
    <script src="./View/Js/DatatableIndex.js" defer></script>
    <script src="./Configuration/DataTables/datatables.min.js" defer></script>
    <script src="./Configuration/JQuery/sweetalert2@11.js" defer></script>
    <script src="./Configuration/JQuery/qrcode.min.js" defer></script>
    <script src="./View/Js/ModalReport.js"></script>
    <script src="./View/Js/AdminModal.js"></script>
    <script src="./View/Js/Select_proccess.js"></script>
    <script src="./View/Js/Automatic_search.js"></script>
    <script src="./View/Js/Search_Selectors.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File upload handling
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('file-input');
            const fileName = document.getElementById('file-name');

            dropZone.addEventListener('click', () => fileInput.click());
            
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-primary');
                dropZone.style.backgroundColor = 'rgba(13, 110, 253, 0.05)';
            });
            
            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('border-primary');
                dropZone.style.backgroundColor = '';
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-primary');
                dropZone.style.backgroundColor = '';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    updateFileName(e.dataTransfer.files[0].name);
                }
            });
            
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length) {
                    updateFileName(fileInput.files[0].name);
                }
            });
            
            function updateFileName(name) {
                fileName.textContent = name;
                fileName.classList.add('text-primary');
                dropZone.querySelector('.drop-zone-title').textContent = 'File selected';
            }

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('passwordadmin');
            
            if (togglePassword && passwordField) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('bi-eye');
                    this.querySelector('i').classList.toggle('bi-eye-slash');
                });
            }

            // Form reset button
            document.getElementById('reset_btn').addEventListener('click', function() {
                const textInputs = document.querySelectorAll('input[type="text"]');
                const numberInputs = document.querySelectorAll('input[type="number"]');
                const selectElements = document.querySelectorAll('select');

                textInputs.forEach(input => input.value = '');
                numberInputs.forEach(input => input.value = '');
                selectElements.forEach(select => select.selectedIndex = 0);
                
                // Reset file input
                if (fileInput) {
                    fileInput.value = '';
                    fileName.textContent = 'No file selected';
                    fileName.classList.remove('text-primary');
                    dropZone.querySelector('.drop-zone-title').textContent = 'Drop your file here';
                }
            });

            // Refresh button
            document.getElementById('refresh_btn').addEventListener('click', function() {
                window.location.href = window.location.pathname;
            });

            // Admin modal
            const showAdminModal = document.getElementById('showAdminModal');
            const adminLoginModal = new bootstrap.Modal(document.getElementById('adminLoginModal'));

            showAdminModal.addEventListener('click', function(event) {
                event.preventDefault();
                adminLoginModal.show();
            });

            // Keyboard shortcut
            document.addEventListener('keydown', function(event) {
                if (event.ctrlKey && (event.key === 'q' || event.key === 'Q')) {
                    event.preventDefault();
                    adminLoginModal.show();
                }
            });

            // Delete all confirmation
            document.getElementById('deleteAllButton').addEventListener('click', function() {
                Swal.fire({
                    title: '<?php echo __('are_you_sure', $lang); ?>',
                    text: "<?php echo __('delete_all_confirm', $lang); ?>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt me-2"></i><?php echo __('yes_delete_all', $lang); ?>',
                    cancelButtonText: '<i class="fas fa-times me-2"></i><?php echo __('cancel', $lang); ?>',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = './Controller/delete_regist.php';
                    }
                });
            });

            // Collapsible sections
            const collapsibles = document.querySelectorAll('[data-bs-toggle="collapse"]');
            collapsibles.forEach(function(collapsible) {
                collapsible.addEventListener('click', function() {
                    const chevron = this.querySelector('.bi-chevron-down, .bi-chevron-up');
                    if (chevron) {
                        chevron.classList.toggle('bi-chevron-down');
                        chevron.classList.toggle('bi-chevron-up');
                    }
                });
            });

            // Clear logs function
            window.clearLogs = function() {
                Swal.fire({
                    title: '<?php echo __('are_you_sure', $lang); ?>',
                    text: "<?php echo __('delete_all_confirm', $lang); ?>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<?php echo __('yes_delete_all', $lang); ?>',
                    cancelButtonText: '<?php echo __('cancel', $lang); ?>'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('./Model/clear_logs.php', {
                            method: 'POST'
                        })
                        .then(response => response.text())
                        .then(data => {
                            Swal.fire({
                                title: 'Success!',
                                text: data,
                                icon: 'success',
                                confirmButtonColor: '#0d6efd'
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while clearing logs.',
                                icon: 'error',
                                confirmButtonColor: '#0d6efd'
                            });
                        });
                    }
                });
            };
        });
    </script>
    <script>
        // Manejar cambio de idioma
document.querySelectorAll('.language-selector').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const lang = this.getAttribute('data-lang');
        
        // Actualizar URL con parámetro de idioma
        const url = new URL(window.location.href);
        url.searchParams.set('lang', lang);
        window.location.href = url.toString();
    });
});

// Guardar preferencia de idioma
if (navigator.cookieEnabled) {
    const langParam = new URLSearchParams(window.location.search).get('lang');
    if (langParam) {
        document.cookie = `lang=${langParam}; path=/; max-age=31536000`; // 1 año
    }
}
    </script>

<script>
    // Dark mode toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        
        // Check for saved dark mode preference or respect OS preference
        if (localStorage.getItem('darkMode') === 'enabled' || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches && 
             localStorage.getItem('darkMode') !== 'disabled')) {
            document.body.classList.add('dark-mode');
            if (darkModeToggle) darkModeToggle.checked = true;
        }
        
        // Toggle dark mode when switch is clicked
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        }
    });
</script>
<script src="./View/Js/dark-mode-toggle.js"></script>
</body>
<?php include './View/Fragments/footer.php'; ?>
</html>
