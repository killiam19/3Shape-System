<?php
/**
 * Log Management Interface
 * 
 * Provides administrative interface for managing system log files.
 * Allows viewing, downloading, and deletion of log files with proper access control.
 * 
 * Features:
 * - Secure session management with timeout
 * - Admin access control enforcement
 * - Log file listing with metadata
 * - Download/view/delete functionality
 * - Responsive DataTables interface
 * 
 * Security:
 * - CSRF protection
 * - Admin-only access
 * - Session timeout
 * 
 * @package View
 * @subpackage Admin
 */

ob_start();
session_start(); // Iniciar sesión

// Configuración de idioma
include '../View/Fragments/idioma.php';

// Tiempo de expiración de la sesión (en segundos)
$tiempo_inactivo = 4600;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $tiempo_inactivo) {
    setcookie('error_message', 'Sesión expirada. Vuelve a iniciar sesión.', time() + 30, '/');
    include_once '../Controller/Cerrar_sesion.php';
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    include_once '../Controller/Cerrar_sesion.php';
    exit("Acceso denegado.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups</title>
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <!-- Carga de jQuery primero para evitar errores de dependencia -->
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Configuration/DataTables/datatables.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/button-styles.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/fontawesome.min.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <script src="../Configuration/JQuery/sweetalert2@11.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/sweetalert2.min.css">
</head>
<body>

    <div class="row d-flex justify-content-center align-items-center">
        <div class="row">
            <br>
            <br>
            <br>
            <div class="col-md-12">
                <nav class="navbar d-flex navbar-expand-lg navbar-light bg-light rounded shadow">
                    <div class="container-fluid">
                    <a href="../Admin/index_admin.php" class="navbar-brand d-flex align-items-center">
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
                                <script src="../View/Js/confirm_home_redirect.js"></script>

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
                                    <a title="Notifications" type="button" class="btn btn-secondary" onclick="showModal()">
                                        <i class="fa-solid fa-bell" style='color:aqua'></i>
                                    </a>
                                    <a title="Log Files" type="button" class="btn btn-secondary" href="../View/Int_LogsDelete.php">
                                        <i class="fa-solid fa-info-circle" style="color:aqua"></i>
                                    </a>
                                    <a title="Settings" type="button" class="btn btn-secondary" href="../View/Int_changePsw.php">
                                        <i class="fa-solid fa-gear"></i>
                                    </a>
                                    <button id="darkModeToggle" class="btn btn-dark" title="<?php echo __('dark_mode', $lang); ?>">
                                        <i class="fa-solid fa-moon"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="container mt-5">
                <br>
                <center>
                    <h2 id="totalC" class="mb-4 my-2 text-center"><?php echo __('log_files', $lang); ?> <i class="fas fa-file-alt"></i></h2>
                </center>
                <div id="FORMeditingLog" class="table-responsive">
                    <table id="mainTable" class="shadow-lg table table-bordered table-striped fade-in">
                        <thead>
                            <tr>
                                <th><?php echo __('file_name', $lang); ?></th>
                                <th><?php echo __('total_size', $lang); ?>:
                                    <?php
                                    // Formato de tamaño de archivo
                                    $logDir = __DIR__ . '/../Model/Logs';
                                    $folderSize = 0;
                                    $files = scandir($logDir);
                                    foreach ($files as $file) {
                                        if ($file !== '.' && $file !== '..') {
                                            $filePath = $logDir . '/' . $file;
                                            $folderSize += filesize($filePath);
                                        }
                                    }
                                    // Convert bytes to human-readable format 
                                    function formatFileSize($size)
                                    {
                                        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                        $index = 0;
                                        while ($size >= 1024 && $index < count($units) - 1) {
                                            $size /= 1024;
                                            $index++;
                                        }
                                        return round($size, 2) . ' ' . $units[$index];
                                    }
                                    $formattedSize = formatFileSize($folderSize);
                                    echo '(' . $formattedSize . ')';
                                    ?>
                                </th>
                                <th><?php echo __('last_modified', $lang); ?></th>
                                <th><?php echo __('download', $lang); ?></th>
                                <th><?php echo __('view', $lang); ?></th>
                                <th><?php echo __('delete', $lang); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            date_default_timezone_set('America/Bogota');
                            // Listar archivos
                            $logDir = __DIR__ . '/../Model/Logs';
                            if (is_dir($logDir)) {
                                $files = scandir($logDir);
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..') {
                                        $filePath = $logDir . '/' . $file;
                                        echo '<tr>';
                                        // mostrar tamaño del archivo por registro
                                        echo '<td>' . htmlspecialchars($file) . '</td>';
                                        echo '<td>' . round(filesize($filePath) / 1024, 2) . ' KB' . '</td>';
                                        echo '<td>' . date("F d Y H:i:s.", filemtime($filePath)) . '</td>';
                                        // Funcion para descargar archivos
                                        echo '<td title="Download"><a href="../Model/Logs/' . htmlspecialchars($file) . '" download class="btn btn-success btn-sm"><i class="fas fa-download"></i></a></td>';
                                        // Funcion para visualizar archivos
                                        echo '<td title="View"><a href="Int_LogsDelete.php?view=' . urlencode($file) . '" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a></td>';
                                        // Funcion para eliminar archivos
                                        echo '<td title="Delete"><a href="Int_LogsDelete.php?delete=' . urlencode($file) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this file?\')"><i class="fas fa-trash-alt"></i></a></td>';
                                        echo '</tr>';
                                    }
                                }
                            } else {
                                echo '<tr><td colspan="6">Log directory not found.</td></tr>';
                            }
                            // Handle file deletion
                            if (isset($_GET['delete'])) {
                                $fileToDelete = $logDir . '/' . $_GET['delete'];
                                if (file_exists($fileToDelete)) {
                                    unlink($fileToDelete);
                                    header("Location: Int_LogsDelete.php");
                                    exit();
                                } else {
                                    echo '<tr><td colspan="6">File not found.</td></tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <br>
                    <center><a href="../Admin/index_admin.php" class="btn btn-secondary mb-5"><?php echo __('back_to_menu', $lang); ?></a></center>
                    <?php
                    // Handle file viewing // Manejo de vista del arhivo
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['view'])) {
                        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
                            die('Unauthorized access');
                        }
                        $fileToEdit = $logDir . '/' . $_GET['view'];
                        if (file_exists($fileToEdit)) {
                            $logContent = $_POST['log_content'] ?? '';
                            $logContent = trim($logContent);
                            if (!empty($logContent)) {
                                file_put_contents($fileToEdit, $logContent);
                            }
                            header("Location: Int_LogsDelete.php");
                            exit();
                        } else {
                            setcookie('error_message', 'File not found.', time() + 30, '/');
                            header("Location: Int_LogsDelete.php");
                            exit();
                        }
                    }
                    // logica relacionada a la vista e edicion de los logs mostrados en la tabla
                    if (isset($_GET['view'])) {
                        $fileToView = $logDir . '/' . $_GET['view'];
                        if (file_exists($fileToView)) {
                            echo '<h3 class="mt-5">Editing: ' . htmlspecialchars($_GET['view']) . '</h3>';
                            echo '<br>';
                            echo '<form method="POST" class="shadow-lg"  action="Int_LogsDelete.php?view=' . urlencode($_GET['view']) . '">';
                            echo '<textarea name="log_content" class="form-control" rows="10">' . htmlspecialchars(file_get_contents($fileToView)) . '</textarea>';
                            echo '<br></br>';
                            echo '<button type="submit" class="btn btn-secondary mt-2 me-2">Save Changes</button>';
                            echo '<a href="Int_LogsDelete.php" class="btn btn-danger mt-2 ml-2">Cancel</a>';
                            echo '</form>';
                        } else {
                            echo '<div class="alert alert-danger mt-3">File not found.</div>';
                        }
                    }
                    ?>
                </div>

                <!-- Modal para mostrar las alertas -->
                <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                    <?php
                    include_once '../Controller/Notify.php'
                    ?>
                    <script src="../View/Js/ModalReportGeneral.js"></script>
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
                                    Clean History
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-1"></i>
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carga de scripts en el orden correcto -->
                <script src="../Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script src="../Configuration/DataTables/datatables.min.js"></script>
                    <script src="../View/Js/dark-mode-toggle-new.js"></script>
                <script>
                    // Inicializar DataTables y configurar eventos
                    $(document).ready(function() {
                        // Inicializar DataTables
                        $('#mainTable').DataTable({
                            paging: true,
                            ordering: true,
                            scrollX: true,
                            scrollY: '60vh',
                            scrollCollapse: true,
                            stateSave: true,
                            fixedHeader: {
                                header: true,
                                footer: true
                            },
                            responsive: true,
                            lengthMenu: [
                                [5, 10, 25, 50, -1],
                                [5, 10, 25, 50, 'Todos']
                            ],
                            pageLength: 25,
                            autoWidth: false,
                            initComplete: function() {
                                // Búsqueda por columnas en el footer
                                this.api().columns().every(function() {
                                    let column = this;
                                    let title = $(column.header()).text();

                                    $('<div class="form-group"/>').append(
                                        $('<input type="text" class="form-control form-control-sm" placeholder="Filtrar ' + title + '" />')
                                        .on('keyup change clear', function() {
                                            if (column.search() !== this.value) {
                                                column.search(this.value).draw();
                                            }
                                        })
                                    ).appendTo($(column.footer()).empty());
                                });

                                // Añadir clase al search principal
                                $('.dataTables_filter input').addClass('form-control form-control-sm');
                            }
                        });
                        
                        // Mejorar estilo del length menu
                        $('.dataTables_length select').addClass('form-control form-control-sm');

                        // Filter notifications
                        $('#notificationFilter').on('keyup', function() {
                            const filterValue = $(this).val().toLowerCase();
                            $('.modal-body div').filter(function() {
                                $(this).toggle($(this).text().toLowerCase().indexOf(filterValue) > -1);
                            });
                        });
                    });
                    
                    // Función para mostrar el modal de notificaciones
                    function showModal() {
                        new bootstrap.Modal(document.getElementById('notificationModal')).show();
                    }

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

                    // Inicializar los dropdowns de Bootstrap
                    document.addEventListener('DOMContentLoaded', function() {
                        // Inicializar todos los dropdowns
                        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
                        dropdownElementList.forEach(function(dropdownToggleEl) {
                            new bootstrap.Dropdown(dropdownToggleEl);
                        });
                        
                        // Asegurarse de que el dropdown de idioma funcione
                        var languageDropdown = document.getElementById('languageDropdown');
                        if (languageDropdown) {
                            languageDropdown.addEventListener('click', function(e) {
                                e.stopPropagation();
                            });
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</body>
<?php include './Fragments/footer.php'; ?>
</html>
