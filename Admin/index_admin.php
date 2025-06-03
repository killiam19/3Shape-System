<?php
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

// Cargar archivo de idioma
$lang_file = '../lang/' . $_SESSION['lang'] . '.json';
if (file_exists($lang_file)) {
    $lang = json_decode(file_get_contents($lang_file), true);
} else {
    $lang = json_decode(file_get_contents('../lang/en.json'), true);
}

// Función de traducción
function __($key, $lang) {
    return $lang[$key] ?? $key;
}

$tiempo_inactivo = 5000; // Tiempo de expiración de la sesión (en segundos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    // Establecer una cookie con el mensaje de Co por sesión expirada
    setcookie('error_message', 'Session expired, please try again.', time() + 30, '/');
    // Redirigir y cerrar sesión
    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    // Si no existe el token, se crea en la sesión de CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$_SESSION['last_activity'] = time(); // Actualizar la última actividad

// Verificar si el usuario es admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirigir y cerrar sesión si no es admin

    include_once '../Controller/Cerrar_sesion.php';
    header("Location: ../index.php");
    exit("Acceso denegado.");
}

include_once "../Controller/validation_message.php";
include '../Configuration/Connection.php';
?>
<?php
// Array de mapeo de nombres 
$fieldLabels = require '../View/Fragments/field_labels.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <link rel="stylesheet" href="../View/Css/admin.css">
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/Form.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <script src="../Configuration/JQuery/sweetalert2@11.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/sweetalert2.min.css">
    <link rel="stylesheet" href="../View/Css/button-styles.css">
    <link rel="stylesheet" href="../View/Css/checkbox-styles.css">

<body>
    <script>
        // Funcion para mostrar advertencias 
        function showWarnings() {
            const hiddenWarnings = document.getElementById('hidden-warnings');
            hiddenWarnings.style.display = 'block'; // Muestra las advertencias ocultas
            event.target.style.display = 'none'; // Oculta el botón
        }

        function toggleVisibility(links, forms) {
            function saveAllFormsState() {
                forms.forEach(formId => {
                    const form = document.getElementById(formId);
                    localStorage.setItem(`form-${formId}`, form.style.display);
                });
            }

            links.forEach((linkId, index) => {
                document.getElementById(linkId).addEventListener('click', function(event) {
                    event.preventDefault();

                    // Ocultar todos los formularios
                    forms.forEach(formId => {
                        document.getElementById(formId).style.display = 'none';
                    });

                    // Mostrar y mover el formulario seleccionado
                    const selectedForm = document.getElementById(forms[index]);
                    const firstContainer = document.getElementById('tool');

                    if (firstContainer && selectedForm) {
                        // Mover el formulario después de first_container
                        selectedForm.style.display = 'block';
                        firstContainer.insertAdjacentElement('beforebegin', selectedForm);

                        selectedForm.style.display = 'block';
                    }

                    saveAllFormsState();
                });
            });

            // Cargar estados y posicionar inicialmente los formularios visibles
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                const storedDisplay = localStorage.getItem(`form-${formId}`);

                if (storedDisplay) {
                    form.style.display = storedDisplay;
                    // Si el formulario está visible al cargar, moverlo
                    if (storedDisplay === 'block') {
                        const firstContainer = document.getElementById('tool');
                        if (firstContainer) {
                            firstContainer.insertAdjacentElement('beforebegin', form);
                        }
                    }
                } else {
                    form.style.display = 'none';
                }
            });
        }
        // Relacion entre formulario y nav-links
        document.addEventListener('DOMContentLoaded', function() {
            toggleVisibility(
                ['showFormLink', 'showFormLink1', 'showFormLink2', 'showFormLink3', 'showFormLink5', 'showFormLink6', 'showFormLink7'],
                ['FormAddForm', 'RemoveForm', 'FormAddTable', 'RemoveFieldsTable', 'addBDCSV', 'addFormTodo', 'addLgColumn']
            );
        });
    </script>

    <div class="row d-flex justify-content-center align-items-center">
        <div class="row">
            <br>
            <br>
            <br>
           <!--Navbar-->
           <div class="col-md-12">
                <nav class="navbar d-flex navbar-expand-lg navbar-light bg-light rounded shadow sticky-top">
                    <div class="container-fluid">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="navbar-brand d-flex align-items-center">
                            <img src="../Admin/3shape-logo.png" alt="Logo" width="150" height="31" id="navbarLogo">
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav d-flex w-100">
                                <li class="nav-item">
                                    <a class="nav-link active" id="homeindex" href="#" onclick="confirmHomeRedirect(); return false;"><i class="fa-solid fa-home"></i> <?php echo __('home', $lang); ?></a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="fieldsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-database"></i> <?php echo __('admin_operations', $lang); ?>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="fieldsDropdown">
                                        <li><a class="dropdown-item" id="showFormLink" href="#"><i class="fa-solid fa-plus"></i> <?php echo __('add_fields', $lang); ?></a></li>
                                        <li><a class="dropdown-item" id="showFormLink1" href="#"><i class="fa-solid fa-trash"></i> <?php echo __('remove_fields', $lang); ?></a></li>
                                        <li><a class="dropdown-item" id="showFormLink2" href="#"><i class="fa-solid fa-table"></i> <?php echo __('add_columns_to_table', $lang); ?></a></li>
                                        <li><a class="dropdown-item" id="showFormLink3" href="#"><i class="fa-solid fa-minus"></i> <?php echo __('remove_columns_from_table', $lang); ?></a></li>
                                        <li><a class="dropdown-item" id="showFormLink5" href="#"><i class="fa-solid fa-file-export"></i> <?php echo __('export_database', $lang); ?></a></li>
                                        <li><a class="dropdown-item" id="showFormLink6" href="#"><i class="fa-solid fa-list"></i> <?php echo __('adding_fields_to_all_forms', $lang); ?></a></li>
                                        <li><a class="dropdown-item" id="showFormLink7" href="#"><i class="fa-solid fa-history"></i> <?php echo __('adding_logs_in_columns', $lang); ?></a></li>
                                        <li><a class="dropdown-item" href="add_user.php"><i class="fa-solid fa-user-plus"></i> <?php echo __('manage_users', $lang); ?></a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="chartsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-chart-line"></i> <?php echo __('charts', $lang); ?>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="chartsDropdown">
                                        <li><button class="dropdown-item chart-switch" data-chart="statusChart"><i class="fa-solid fa-laptop"></i> <?php echo __('equipment_status', $lang); ?></button></li>
                                        <li><button class="dropdown-item chart-switch" data-chart="jobTitleChart"><i class="fa-solid fa-briefcase"></i> <?php echo __('job_titles', $lang); ?></button></li>
                                        <li><button class="dropdown-item chart-switch" data-chart="userRolesChart"><i class="fa-solid fa-users"></i> <?php echo __('registered_users', $lang); ?></button></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown ms-auto">
                                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-language"></i> <?php echo __('language', $lang); ?>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                        <li><a class="dropdown-item language-selector" href="#" data-lang="en">English</a></li>
                                        <li><a class="dropdown-item language-selector" href="#" data-lang="es">Español</a></li>
                                        <li><a class="dropdown-item language-selector" href="#" data-lang="pl">Polski</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item ms-auto">
                                    <div class="btn-group">
                                    <div class="notification">
    <li class="nav-item ms-auto bell-container">
        <div class="bell-wrapper">
            <a type="button" class="btn btn-outline-warning bell-button" onclick="showModal()">
                <i class="fa-solid fa-bell" style="color:black"></i>
                <span class="notification-badge">0</span>
            </a>
        </div>
    </li>
</div>
                                        <a title="<?php echo __('admin_tools', $lang); ?>" type="button" class="btn btn-info" href="../View/Int_LogsDelete.php">
                                            <i class="fa-solid fa-info-circle"></i>
                                        </a>
                                        <a title="<?php echo __('user_profile', $lang); ?>" type="button" class="btn btn-secondary" href="../View/Int_changePsw.php">
                                            <i class="fa-solid fa-gear"></i>
                                        </a>
                                        <button id="darkModeToggle" class="btn btn-dark" title="<?php echo __('dark_mode', $lang); ?>">
                                            <i class="fa-solid fa-moon"></i>
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <br>
            <br>
        </div>
        <div>
            <center>
                <h1 id="totalC" class="text-center my-5" style="font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;"><?php echo __('admin_dashboard', $lang); ?> <i class="fas fa-chart-bar"></i></h1>
            </center>
        </div>
        <div class="row my-5" style="display: flex; gap: 20px;">
            <script src="../View/Js/diagram.js"> </script>
            <div class="col-md-5 ms-4">
                <div id="tool" style="display: none;"></div>
            </div>

            <!-- Modificar solo la sección de gráficos en index_admin.php -->

<div class="col-md-6" id="first_container">
    <div id="chartContainer" class="chart-container" style="position: relative; min-height: 380px; display: flex;">
        <?php
        // Obtener todos los datos en una sola consulta
        try {
            // Datos para el primer gráfico
            $statusData = $pdo->query("SELECT user_status FROM vista_equipos_usuarios")->fetchAll(PDO::FETCH_ASSOC);

            // Datos para el segundo gráfico
            $jobTitleData = $pdo->query("SELECT job_title, COUNT(*) AS total_employees FROM usuarios_equipos WHERE job_title IS NOT NULL AND job_title != '' GROUP BY job_title ORDER BY total_employees DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

            // Datos para el tercer gráfico - Usuarios por rol
            $userRolesData = $pdo->query("SELECT role, COUNT(*) AS total FROM users GROUP BY role ORDER BY FIELD(role, 'admin', 'manager', 'user')")->fetchAll(PDO::FETCH_ASSOC);

            // Imprimir los datos para depuración
            echo "<!-- Datos de gráficos: ";
            echo "Status: " . json_encode($statusData) . ", ";
            echo "JobTitle: " . json_encode($jobTitleData) . ", ";
            echo "UserRoles: " . json_encode($userRolesData);
            echo " -->";

            echo "<script>
            var chartData = {
                status: " . json_encode($statusData) . ",
                jobTitle: " . json_encode($jobTitleData) . ",
                userRoles: " . json_encode($userRolesData) . "
            };
            console.log('Datos de gráficos cargados:', chartData);
            </script>";
        } catch (PDOException $e) {
            echo '<p class="text-danger">Error al cargar datos para gráficos: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo "<script>console.error('Error al cargar datos para gráficos:', '" . htmlspecialchars($e->getMessage()) . "');</script>";
        }
        ?>

        <!-- Los gráficos se crearán dinámicamente aquí -->
        <canvas id="statusChart" class="chart-canvas" width="400" height="300"></canvas>
        <canvas id="jobTitleChart" class="chart-canvas d-none" width="400" height="300"></canvas>
        <canvas id="userRolesChart" class="chart-canvas d-none" width="400" height="300"></canvas>
    </div>
</div>
            <!--Formulario para agregar columnas en la base de datos-->
            <div class="col-md-6">
                <form action="add_fields.php" id="FormAddForm" style="display: block;" method="POST" class="shadow border border-secondary rounded p-4 bg-white">
                    <h1 class="text-center mb-4 fw-bold"><?php echo __('add_fields', $lang); ?></h1>
                    <div class="mb-3">
                        <h4 class="form-label"><?php echo __('data_type', $lang); ?> <span class="text-danger">*</span></h4>
                        <select name="data_type" class="form-select form-select-lg mb-3" required>
                            <option value=""><?php echo __('select_data_type', $lang); ?></option>
                            <option value="Date"><?php echo __('date_type', $lang); ?></option>
                            <option value="INTEGER"><?php echo __('integer_type', $lang); ?></option>
                            <option value="VARCHAR(255)"><?php echo __('varchar_type', $lang); ?></option>
                            <option value="BOOLEAN"><?php echo __('boolean_type', $lang); ?></option>
                            <option value="BIGINT"><?php echo __('bigint_type', $lang); ?></option>
                        </select>
                    </div>
                    <div id="boolean" class="alert alert-warning mt-2" style="display: none;">
                        <?php echo __('boolean_warning', $lang); ?>
                    </div>
                    <script>
                        document.querySelector('select[name="data_type"]').addEventListener('change', function() {
                            if (this.value === 'BOOLEAN') {
                                document.getElementById('boolean').style.display = 'block';
                            } else {
                                document.getElementById('boolean').style.display = 'none';
                            }
                        });
                    </script>
                    <div class="mb-3">
                        <h4 class="form-label"><?php echo __('field_name', $lang); ?> <span class="text-danger">*</span></h4>
                        <input type="text" id="field_name" name="field_name" class="form-control form-control-lg" required
                            onfocus="showNetworkAddressInfo()" oninput="validateInput()" placeholder="<?php echo __('enter_field_name', $lang); ?>" />
                    </div>
                    <div id="networkAddressInfo" class="alert alert-info mt-2" style="display: none;">
                        <?php echo __('field_name_info', $lang); ?>
                    </div>
                    <div id="errorMessage" class="alert alert-danger mt-2" style="display: none;">
                        <?php echo __('field_number_error', $lang); ?>
                    </div>
                    <script>
                        function showNetworkAddressInfo() {
                            document.getElementById("networkAddressInfo").style.display = "block";
                        }

                        function validateInput() {
                            const field = document.getElementById("field_name");
                            const errorMessage = document.getElementById("errorMessage");
                            if (/^\d+$/.test(field.value)) {
                                errorMessage.style.display = "block";
                            } else {
                                errorMessage.style.display = "none";
                            }
                        }

                        function validateForm() {
                            const field = document.getElementById("field_name").value;
                            if (/^\d+$/.test(field)) {
                                alert("<?php echo __('field_number_error', $lang); ?>");
                                return false; // Evita el envío del formulario
                            }
                            return true; // Permite el envío si la validación es correcta
                        }
                    </script>
                    <div class="mb-4">
                        <h4 class="form-label"><?php echo __('select_table', $lang); ?> <span class="text-danger">*</span></h4>
                        <select name="table_name" class="form-select form-select-lg" required>
                            <option value=""><?php echo __('select_table', $lang); ?></option>
                            <option value="usuarios_equipos"><?php echo __('users', $lang); ?></option>
                            <option value="equipos"><?php echo __('assets', $lang); ?></option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-primary nav-arrow btn-lg" onclick="navigateTo('prev')">
                            <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
                        </button>

                        <div class="btn-block">
                            <button id="Addfield" type="submit" onclick="return validateForm()"
                                class="btn btn-primary btn-lg px-4"><?php echo __('add_field', $lang); ?></button>
                        </div>

                        <button type="button" class="btn btn-outline-primary nav-arrow btn-lg" onclick="navigateTo('next')">
                            <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
                <br>
            </div>
            <!--Formulario pra eliminar columnas de la base de datos-->
            <div class="col-md-6">
                <form action="remove_fields.php" id="RemoveForm" style="display: none;" method="post" class="shadow border border-secondary rounded p-4 bg-white">
                    <h1 class="text-center mb-4 fw-bold"><?php echo __('remove_fields', $lang); ?></h1>
                    <div class="mb-3">
                        <h4 class="form-label"><?php echo __('field_name', $lang); ?> <span class="text-danger">*</span></h4>
                        <?php
                        try {
                            $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                    AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'fk_id', 'fk_assetname',
                    'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida','user_status','status_change') 
                    ORDER BY TABLE_NAME, ORDINAL_POSITION";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">';
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<div class="col">';
                                    echo '<div class="form-check hover-effect p-3 rounded d-flex align-items-center">';
                                    echo '<input class="form-check-input" type="checkbox" name="removefields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '" id="field_' . htmlspecialchars($row["Field"]) . '">';
                                    echo '<label class="form-check-label text-truncate ms-2" for="field_' . htmlspecialchars($row["Field"]) . '">' . htmlspecialchars($row["Field"]) . '</label>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p>' . __('no_data_found', $lang) . '</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p>' . __('error', $lang) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                        <br>
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                                <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
                            </button>

                            <div class="btn-block">
                                <button id="Removefield" type="submit" class="btn btn-danger btn-lg px-4"><?php echo __('remove_field', $lang); ?></button>
                            </div>

                            <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                                <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>

                        <div id="networkInfo" class="alert alert-warning mt-4" style="display: none;">
                            <strong><?php echo __('warning', $lang); ?>:</strong> <?php echo __('remove_field_warning', $lang); ?>
                        </div>
                        <script>
                            function showNetworkInfo() {
                                document.getElementById('networkInfo').style.display = 'block';
                            }
                        </script>
                        <script>
                            document.getElementById('RemoveForm').addEventListener('submit', function(event) {
                                const checkboxes = document.querySelectorAll('input[name="removefields[]"]');
                                let selectedCount = 0;
                                checkboxes.forEach(checkbox => {
                                    if (checkbox.checked) selectedCount++;
                                });

                                if (selectedCount === 0) {
                                    event.preventDefault();
                                    alert('<?php echo __('select_at_least_one_field', $lang); ?>');
                                } else {
                                    if (!confirm('<?php echo __('confirm_remove_fields', $lang); ?>')) {
                                        event.preventDefault();
                                    }
                                }
                            });
                        </script>
                    </div>
                    <br>
                </form>
            </div>
            <!--Formulario para agregar campos dinamicos en la tabla principal de index-->
            <div class="col-md-6">
                <form action="add_fieldsTable.php" method="post" id="FormAddTable" style="display: none;"
                    class="shadow border border-secondary p-4 rounded bg-white">
                    <h1 class="text-center mb-4 fw-bold"><?php echo __('add_columns_to_table', $lang); ?></h1>
                    <h4><?php echo __('field_name', $lang); ?> <span class="text-danger">*</span></h4>
                    <?php
                    try {
                        $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
            AND COLUMN_NAME NOT IN ('assetname', 'serial_number','fk_id', 'fk_assetname',
            'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida','user_status','status_change') 
            ORDER BY TABLE_NAME, ORDINAL_POSITION";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                            echo '<div class="row">';
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="col-md-4 col-sm-6 mb-2">';
                                echo '<div class="form-check">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row["Field"]) . '" data-type="' . htmlspecialchars($row["Type"]) . '" id="check_' . htmlspecialchars($row["Field"]) . '">';
                                echo '<label class="form-check-label ms-2" for="check_' . htmlspecialchars($row["Field"]) . '">';
                                echo htmlspecialchars($row["Field"]);
                                echo '</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p>' . __('no_data_found', $lang) . '</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>' . __('error', $lang) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                    <br>
                    <div id="dynamicInput"></div>
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                            <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
                        </button>

                        <div class="btn-block">
                            <button id="AddfieldTable" type="submit" class="btn btn-secondary"><?php echo __('add_field', $lang); ?></button>
                            <br>
                        </div>

                        <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                            <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                </form>
            </div>
            <!-- Add this within the existing column in index_admin.php, similar to the "Add Fields to the Main Table" form -->
          <div class="col-md-6">
    <form action="remove-fields-table-script.php" method="post" id="RemoveFieldsTable" style="display: none;"
        class="shadow border border-secondary p-4 rounded bg-white">
        <h1 class="text-center mb-4 fw-bold"><?php echo __('remove_columns_from_table', $lang); ?></h1>
        <h4><?php echo __('select_fields_to_remove', $lang); ?> <span class="text-danger">*</span></h4>
        <?php
        try {
            // Mostrar todos los campos disponibles EXCEPTO los prohibidos
            // El usuario puede seleccionar cuáles quiere ocultar de la vista
            $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                    AND COLUMN_NAME NOT IN ('assetname', 'serial_number','fk_id','fk_assetname','user_status',
                    'last_user','job_title','status_change','cedula') 
                    ORDER BY TABLE_NAME, ORDINAL_POSITION";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo '<div class="row">';
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $fieldName = $row["Field"];
                    $fieldType = $row["Type"];
                    
                    // Marcar como checked si el campo ya está oculto
                    $isHidden = isset($_SESSION['hidden_fields']) && in_array($fieldName, $_SESSION['hidden_fields']);
                    $checkedAttr = $isHidden ? 'checked' : '';
                    
                    echo '<div class="col-md-4 col-sm-6 mb-2">';
                    echo '<div class="form-check custom-checkbox mb-2">';
                    echo '<div class="form-check d-flex align-items-center">';
                    echo '<input class="form-check-input me-2" type="checkbox" name="removemfields[]" value="' . htmlspecialchars($fieldName) . '" data-type="' . htmlspecialchars($fieldType) . '" id="remove_check_' . htmlspecialchars($fieldName) . '" ' . $checkedAttr . '>';
                    echo '<label class="form-check-label text-truncate" for="remove_check_' . htmlspecialchars($fieldName) . '">' . htmlspecialchars($fieldName);
                    
                    // Mostrar estado actual del campo
                    if ($isHidden) {
                        echo ' <small class="text-muted">(oculto)</small>';
                    } else {
                        echo ' <small class="text-success">(visible)</small>';
                    }
                    
                    echo '</label>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>' . __('no_removable_fields_found', $lang) . '</p>';
            }
        } catch (PDOException $e) {
            echo '<p>' . __('error', $lang) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
        <br>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
            </button>

            <div class="btn-block">
                <button id="RemoveFieldTable" type="submit" class="btn btn-danger"><?php echo __('hide_selected_fields', $lang); ?></button>
            </div>

            <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
            </button>
        </div>
        <div class="alert alert-info mt-2">
            <strong><?php echo __('info', $lang); ?>:</strong> 
            Los campos seleccionados se ocultarán de la tabla de datos. Los campos ya ocultos aparecen marcados.
        </div>
    </form>
</div>
        
        <!--Exportar datos de ,a base de datos a formato CSV y TXT-->
        <div class="col-md-6 my-2">
            <form action="../Controller/Exportar_BD.php" style="display: none;" method="post" id="addBDCSV"
                class="shadow border border-secondary p-4 rounded bg-white">
                <h1 class="text-center mb-4 fw-bold"><?php echo __('export_database', $lang); ?></h1>
                <h4><?php echo __('select_columns', $lang); ?> <span class="text-danger">*</span></h4>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll"><?php echo __('select_all', $lang); ?></label>
                </div>

                <div class="row">
                    <?php
                    try {
                        $sql = "SELECT COLUMN_NAME 
                            FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                            AND COLUMN_NAME != 'fk_id'
                            ORDER BY TABLE_NAME, ORDINAL_POSITION";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="col-md-4 col-sm-6 mb-2">';
                                echo '<div class="form-check">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="columns[]" value="' . htmlspecialchars($row["COLUMN_NAME"]) . '" id="col_' . htmlspecialchars($row["COLUMN_NAME"]) . '">';
                                echo '<label class="form-check-label ms-2" for="col_' . htmlspecialchars($row["COLUMN_NAME"]) . '">' . htmlspecialchars($row["COLUMN_NAME"]) . '</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>' . __('no_data_found', $lang) . '</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>' . __('error', $lang) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                    ?>
                </div>

                <br>
                <h4><?php echo __('format', $lang); ?> <span class="text-danger">*</span></h4>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="format" value="csv" id="format_csv" checked>
                    <label class="form-check-label" for="format_csv">CSV</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="format" value="txt" id="format_txt">
                    <label class="form-check-label" for="format_txt">TXT</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="format" value="excel" id="format_txt">
                    <label class="form-check-label" for="format_txt">Excel</label>
                </div>


                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                        <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
                    </button>

                    <div class="btn-block text-center">
                        <button type="submit" class="btn btn-secondary"><?php echo __('export', $lang); ?></button>
                    </div>


                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                        <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <script>
                    document.getElementById('addBDCSV').addEventListener('submit', function(event) {
                        if (!confirm('<?php echo __('confirm_export_data', $lang); ?>')) {
                            event.preventDefault(); // Cancelar el envío si el usuario no confirma
                        }
                    });

                    // Función para seleccionar/deseleccionar todos los checkboxes
                    document.getElementById('selectAll').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('input[name="columns[]"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });

                    // Actualizar el estado del checkbox "Select All" cuando se cambian los checkboxes individuales
                    document.querySelectorAll('input[name="columns[]"]').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const allCheckboxes = document.querySelectorAll('input[name="columns[]"]');
                            const selectAllCheckbox = document.getElementById('selectAll');
                            selectAllCheckbox.checked = Array.from(allCheckboxes).every(cb => cb.checked);
                        });
                    });
                </script>
            </form>
        </div>
        <!--Formulario para agregar registros en la base de datos en los campos de seleccion-->
        <div class="col-md-6 my-2">
            <form action="add_Logs.php" style="display: none" method="POST" id="addLgColumn" class="shadow border border-secondary p-4 rounded bg-white">
                <h1 class="text-center mb-4 fw-bold"><?php echo __('adding_logs_in_columns', $lang); ?></h1>
                <h4><?php echo __('select_columns', $lang); ?> <span class="text-danger">*</span></h4>
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <?php
                try {

                    $sql = "SELECT TABLE_NAME, COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                    AND COLUMN_NAME NOT IN ('assetname', 'serial_number','fk_id', 'fk_assetname',
                    'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida') 
                    ORDER BY TABLE_NAME, ORDINAL_POSITION";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        echo '<div class="row">';
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<div class="col-md-4 col-sm-6 mb-2">';
                            echo '<div class="form-check">';
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input" type="checkbox" name="columns[]" value="' . htmlspecialchars($row["TABLE_NAME"]) . '|' . htmlspecialchars($row["COLUMN_NAME"]) . '" id="log_' . htmlspecialchars($row["COLUMN_NAME"]) . '">';
                            echo '<label class="form-check-label ms-2" for="log_' . htmlspecialchars($row["COLUMN_NAME"]) . '">' .
                                htmlspecialchars($row["COLUMN_NAME"]) . ' (' . htmlspecialchars($row["TABLE_NAME"]) . ')</label>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>' . __('no_data_found', $lang) . '</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>' . __('error', $lang) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
                <br>
                <h4><?php echo __('log_name', $lang); ?> <span class="text-danger">*</span></h4>
                <input class="form-control mb-3" type="text" id="Log_name" name="Log_name" required
                    pattern="[a-zA-Z0-9_ ]+" title="<?php echo __('log_name_pattern', $lang); ?>" />
                <br>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                        <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
                    </button>

                    <button id="AddLogfield" type="submit" class="btn btn-secondary mb-3"
                        onclick="return confirm('<?php echo __('confirm_add_log', $lang); ?>')"><?php echo __('add_log', $lang); ?></button>

                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                        <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        <!--Formulario para agregar campos dinamicos en todos los formularios-->
        <div class="col-md-6">
            <form action="add_fieldTDO.php" method="post" style="display: none;" id="addFormTodo" class="shadow border border-secondary p-4 rounded bg-white">
                <h1 class="text-center mb-4 fw-bold"><?php echo __('adding_fields_to_all_forms', $lang); ?></h1>
                <h4><?php echo __('select_columns', $lang); ?> <span class="text-danger">*</span></h4>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row">
                    <?php
                    try {
                        // Get columns from both tables
                        $prohibited_fields = ['assetname', 'serial_number','fk_id', 'fk_assetname', 'user_status', 'last_user', 'job_title', 'cedula', 'Carnet', 'LLave', 'Tipo_ID'];

                        $sql = "SELECT COLUMN_NAME, DATA_TYPE 
                                   FROM INFORMATION_SCHEMA.COLUMNS 
                                   WHERE TABLE_SCHEMA = 'garantias' 
                                   AND TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                   ORDER BY TABLE_NAME, COLUMN_NAME";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        echo '<div class="col-md-12 mb-3">';
                        echo '<label class="form-label">' . __('available_fields', $lang) . '</label>';
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-sm table-bordered">';
                        echo '<thead><tr><th>' . __('field', $lang) . '</th><th>' . __('data_type', $lang) . '</th><th><input type="checkbox" id="selectAll" class="form-check-input"> ' . __('select_all', $lang) . '</th></tr></thead>';
                        echo '<tbody>';

                        foreach ($columns as $column) {
                            if (!in_array($column['COLUMN_NAME'], $prohibited_fields)) {
                                $fieldName = $column['COLUMN_NAME'];
                                $displayName = isset($fieldLabels[$fieldName]) ? $fieldLabels[$fieldName] : $fieldName;

                                // Esta línea debe eliminarse completamente, ya que es redundante con la definición de $displayName

                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($displayName) . '</td>';
                                echo '<td>' . htmlspecialchars($column['DATA_TYPE']) . '</td>';
                                echo '<td><input class="form-check-input field-checkbox" type="checkbox" name="columns[]" value="' . htmlspecialchars($column['COLUMN_NAME']) . '"></td>';
                                echo '</tr>';
                            }
                        }

                        echo '</tbody></table></div></div>';
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">' . __('error_loading_fields', $lang) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>

                <br>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('prev')">
                        <i class="fas fa-arrow-left"></i> <?php echo __('previous', $lang); ?>
                    </button>

                    <div class="btn-block text-center">
                        <button type="submit" class="btn btn-secondary"><?php echo __('add_field', $lang); ?></button>
                    </div>

                    <button type="button" class="btn btn-outline-primary nav-arrow" onclick="navigateTo('next')">
                        <?php echo __('next', $lang); ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        <!-- Modal para mostrar las alertas -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <?php
            include_once '../Controller/Notify.php'
            ?>
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h2 class="modal-title fs-4">
                            <i class="bi bi-bell-fill me-2"></i>
                            <?php echo __('notification_history', $lang); ?>
                        </h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="<?php echo __('filter_notifications', $lang); ?>">
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
                                    <p class="mt-3 text-muted"><?php echo __('no_notifications', $lang); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="clearLogs()">
                            <i class="bi bi-trash-fill me-1"></i>
                            <?php echo __('clean_history', $lang); ?>
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>
                            <?php echo __('close', $lang); ?>
                        </button>
                    </div>
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

        <script src="../View/Js/ModalReportGeneral.js"></script>
        <div class="modal fade" id="repeatedSerialsModal" tabindex="-1" aria-labelledby="repeatedSerialsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h2 class="modal-title" id="repeatedSerialsModalLabel"><i
                                class="fas fa-exclamation-triangle"></i>
                            <?php echo __('repeated_serials_found', $lang); ?></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <h4><strong><?php echo __('warning', $lang); ?>!</strong> <?php echo __('following_serials_repeated', $lang); ?>:</h4>
                            <br>
                            <ul id="repeatedSerialsList"></ul>
                        </div>
                        <div class="alert alert-danger " role="alert">
                            <strong><?php echo __('action_required', $lang); ?>:</strong> <?php echo __('want_to_delete_them', $lang); ?>?
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                class="fas fa-times"></i>
                            <?php echo __('no', $lang); ?></button>
                        <button type="button" class="btn btn-danger" id="deleteSerialsButton"><i
                                class="fas fa-trash-alt"></i> <?php echo __('yes_delete', $lang); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Hover dropdown functionality
            document.addEventListener('DOMContentLoaded', function() {
                const dropdowns = document.querySelectorAll('.dropdown');

                dropdowns.forEach(dropdown => {
                    // Show on hover
                    dropdown.addEventListener('mouseenter', function() {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'block';
                        }
                    });

                    // Hide when mouse leaves
                    dropdown.addEventListener('mouseleave', function() {
                        const menu = this.querySelector('.dropdown-menu');
                        if (menu) {
                            menu.style.display = 'none';
                        }
                    });
                });

                // Keep dropdown open when hovering over menu
                const dropdownMenus = document.querySelectorAll('.dropdown-menu');
                dropdownMenus.forEach(menu => {
                    menu.addEventListener('mouseenter', function() {
                        this.style.display = 'block';
                    });

                    menu.addEventListener('mouseleave', function() {
                        this.style.display = 'none';
                    });
                });
            });
        </script>

        <!--Navegabilidad-->
        <script>
            // Mapeo de formularios en orden
            const formOrder = [
                'FormAddForm',
                'RemoveForm',
                'FormAddTable',
                'RemoveFieldsTable',
                'addBDCSV',
                'addFormTodo',
                'addLgColumn'
            ];

            function navigateTo(direction) {
                const currentFormId = document.querySelector('form:not([style*="none"])')?.id;
                if (!currentFormId) return;

                const currentIndex = formOrder.indexOf(currentFormId);
                if (currentIndex === -1) return;

                let nextIndex;
                if (direction === 'next') {
                    nextIndex = (currentIndex + 1) % formOrder.length;
                } else {
                    nextIndex = (currentIndex - 1 + formOrder.length) % formOrder.length;
                }

                // Ocultar todos los formularios
                formOrder.forEach(id => {
                    document.getElementById(id).style.display = 'none';
                });

                // Mostrar el formulario seleccionado
                const nextForm = document.getElementById(formOrder[nextIndex]);
                if (nextForm) {
                    nextForm.style.display = 'block';
                    // Mover al principio del contenedor
                    const firstContainer = document.getElementById('tool');
                    if (firstContainer) {
                        firstContainer.insertAdjacentElement('beforebegin', nextForm);
                    }

                    // Scroll suave al formulario
                    nextForm.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
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

    </div>
    </div>
    <!-- Load jQuery first -->
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <!-- Then load Bootstrap JS -->
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <!-- Then load other scripts -->
    <script src="../Configuration/JQuery/Chart.js"></script>
    <!-- Remove the duplicate jQuery loading at the bottom -->
    <script src="../View/Js/confirm_home_redirect.js"></script>
    <script src="../View/Js/dark-mode-toggle-new.js"></script>
</body>
<?php include '../View/Fragments/footer.php'; ?>

</html>
<script>
    // Función para manejar la selección de todos los campos
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const fieldCheckboxes = document.getElementsByClassName('field-checkbox');

        selectAllCheckbox.addEventListener('change', function() {
            Array.from(fieldCheckboxes).forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Actualizar el estado del checkbox principal cuando se cambian los individuales
        Array.from(fieldCheckboxes).forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(fieldCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    });
</script>
