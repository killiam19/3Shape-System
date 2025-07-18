<?php
session_start();

include "../Configuration/Connection.php";

// Verificar si el usuario es administrador
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Si estamos procesando el primer formulario, guardar datos en sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phase']) && $_POST['phase'] === '1') {
    $_SESSION['phase1_data'] = $_POST;
    header("Location: Estado_Assets_Register.php");
    exit;
}

// Configuración de idioma
include '../View/Fragments/idioma.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('register_asset', $lang); ?></title>
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
     <!--Font Awesome -->
     <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="./Css/FormsCRUD.css">
    <!--Archivo con la funcion de ser la Interfaz de el proceso de Registro de activos-->
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center">
            <div id="FORMregistform" class="shadow-lg p-5 rounded bg-light">
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <button id="toggleFieldPanel" class="btn btn-primary mb-3">Show/Hide Panel</button>
                    <script>
                        document.getElementById('toggleFieldPanel').addEventListener('click', function() {
                            var panel = document.querySelector('.admin-panel');

                            // Add transition styles if not already present
                            if (!panel.style.transition) {
                                panel.style.transition = 'all 0.3s ease-in-out';
                            }

                            // Set initial height/opacity for animation
                            if (panel.style.display === 'none' || !panel.style.display) {
                                panel.style.display = 'block';
                                panel.style.opacity = '0';
                                panel.style.maxHeight = '0';

                                // Force reflow
                                panel.offsetHeight;

                                // Animate in
                                panel.style.opacity = '1';
                                panel.style.maxHeight = panel.scrollHeight + 'px';
                            } else {
                                // Animate out
                                panel.style.opacity = '0';
                                panel.style.maxHeight = '0';

                                // Hide after animation
                                setTimeout(() => {
                                    panel.style.display = 'none';
                                }, 300);
                            }
                        });
                    </script>
                    <!-- Panel de Administrador para Campos Dinámicos -->
                    <div class="admin-panel mb-4 p-3 border rounded bg-light" style="display: none;">
                        <h4 class="mb-3"><i class="fa-solid fa-cog"></i> Field Management</h4>
                        <form id="dynamicFieldForm" action="../Admin/add_fieldTDO.php" method="POST" class="row g-3">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <?php
// Array de mapeo de nombres 
$fieldLabels = require '../View/Fragments/field_labels.php';
?>
                            <?php
                            try {
                                // Get columns from both tables
                                $prohibited_fields = ['assetname', 'serial_number', 'fk_id', 'fk_assetname', 'user_status', 'last_user', 'job_title', 'cedula','fecha_ingreso', 'Carnet', 'LLave', 'Tipo_ID'];

                                $sql = "SELECT COLUMN_NAME, DATA_TYPE 
                                   FROM INFORMATION_SCHEMA.COLUMNS 
                                   WHERE TABLE_SCHEMA = 'garantias' 
                                   AND TABLE_NAME IN ('equipos', 'usuarios_equipos')
                                   ORDER BY TABLE_NAME, COLUMN_NAME";

                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                echo '<div class="col-md-12 mb-3">';
                                echo '<label class="form-label">Available Fields</label>';
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-sm table-bordered">';
                                echo '<thead><tr><th>Field</th><th>Data Type</th><th>Select</th></tr></thead>';
                                echo '<tbody>';

                                foreach ($columns as $column) {
                                    if (!in_array($column['COLUMN_NAME'], $prohibited_fields)) {
                                        $fieldName = $column['COLUMN_NAME'];
                                        $displayName = isset($fieldLabels[$fieldName]) ? $fieldLabels[$fieldName] : $fieldName;
                                        
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($displayName) . '</td>';
                                        echo '<td>' . htmlspecialchars($column['DATA_TYPE']) . '</td>';
                                        echo '<td><input type="checkbox" name="columns[]" value="' . htmlspecialchars($column['COLUMN_NAME']) . '"></td>';
                                        echo '</tr>';
                                    }
                                }

                                echo '</tbody></table></div></div>';
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger">Error loading fields: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Update Fields</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <!--Formulario de registro de equipos con sus respectivas validaciones -->
                <form id="registform" method="post" action="">
                    <input type="hidden" name="phase" value="1">
                    <h1 id="totalC" class="text-center mb-5 fw-bold">
                        <i class="fas fa-laptop-medical me-2"></i><?php echo __('register_asset', $lang); ?>
                    </h1>
                    
                    <div class="row">
                        <!-- Primera fila: Usuario y Serial -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nombre_usuario"><i class="fas fa-user me-2"></i><?php echo __('user_name', $lang); ?>:</label>
                                <input class="shadow-sm form-control" type="text" id="nombre_usuario"
                                    name="nombre_usuario" autocomplete="off" required data-validate="true"
                                    value="<?php echo rand(10000, 99999); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="serial"><i class="fas fa-barcode me-2"></i><?php echo __('serial_number', $lang); ?>:</label>
                                <input class="shadow-sm form-control" type="text" id="serial" name="serial"
                                    autocomplete="off" required data-validate="true"
                                    oninput="this.value = this.value.toUpperCase()" />
                                <small id="serial-message" class="form-text"></small>
                                <script src="../View/Js/GetSerial.js"></script>
                            </div>
                        </div>

                        <!-- Segunda fila: Fecha de ingreso y Estado de usuario -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="fecha_ingreso"><i class="fas fa-calendar-plus me-2"></i><?php echo __('entry_date', $lang); ?>:</label>
                                <input class="shadow-sm form-control" type="date" id="fecha_ingreso" name="fecha_ingreso" 
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="usrStts"><i class="fas fa-user-shield me-2"></i><?php echo __('user_status', $lang); ?>:</label>
                                <select id="usrStts" class="shadow-sm form-select" name="usrStts">
                                    <option value="0"><?php echo __('select_user_status', $lang); ?></option>
                                    <option value="Stock"><?php echo __('stock', $lang); ?></option>
                                    <option value="Active User" selected><?php echo __('active_user', $lang); ?></option>
                                    <option value="Old User"><?php echo __('old_user', $lang); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Tercera fila: Puesto de trabajo y Cédula -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="job_title"><i class="fas fa-briefcase me-2"></i><?php echo __('job_title', $lang); ?>:</label>
                                <input class="form-control" type="text" id="job_title" name="job_title"
                                    list="job_titles" data-validate="true" autocomplete="off" value="Unknown">
                                <datalist id="job_titles">
                                    <?php
                                    $job_titles = $pdo->prepare("SELECT DISTINCT job_title FROM usuarios_equipos ORDER BY job_title ASC");
                                    $job_titles->execute();
                                    $job_titles = $job_titles->fetchAll(PDO::FETCH_COLUMN);
                                    foreach ($job_titles as $job_title) {
                                        echo "<option value='$job_title'>$job_title</option>";
                                    }
                                    ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id"><i class="fas fa-id-card me-2"></i>Id:</label>
                                <input class="shadow-sm form-control" type="number" id="id" name="cedula" 
                                    autocomplete="off" required value="0">
                                <p id="mensaje" class="text-danger"></p>
                            </div>
                        </div>

                        <!-- Campos dinámicos -->
                        <?php if (isset($_SESSION['new_reg_fields'])): ?>
                            <?php foreach ($_SESSION['new_reg_fields'] as $field_name): ?>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="<?php echo htmlspecialchars($field_name); ?>">
                                            <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($field_name); ?>:
                                        </label>
                                        <?php
                                        $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                        $field_type = $_SESSION['new_fieldR_types'][$field_name];
                                        ?>
                                        <?php if ($field_type === 'date'): ?>
                                            <input class="form-control shadow-sm" type="date"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true">
                                        <?php elseif ($field_type === 'tinyint'): ?>
                                            <select class="form-select shadow-sm" id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off"
                                                data-validate="true">
                                                <option value="0" <?php echo (empty($field_value) || $field_value == 0) ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                            <input class="form-control shadow-sm" type="number"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value ? $field_value : 0; ?>" autocomplete="off" min="0"
                                                oninput="this.value = Math.abs(this.value)">
                                        <?php else: ?>
                                            <input class="form-control shadow-sm" type="text"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value; ?>" autocomplete="off" data-validate="true">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Fila: Carnet, Llave, Tipo ID -->
                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="selectCard"><i class="fas fa-id-card-alt me-2"></i><?php echo __('card', $lang); ?>:</label>
                                        <select class="shadow-sm form-select" name="selectCard" id="selectCard">
                                            <option value="No"><?php echo __('no', $lang); ?></option>
                                            <option value="Si"><?php echo __('yes', $lang); ?></option>
                                            <option value="Pendiente" selected><?php echo __('pending', $lang); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="selectKEY"><i class="fas fa-key me-2"></i> <?php echo __('key', $lang); ?>:</label>
                                        <select class="shadow-sm form-select" name="selectKEY" id="selectKEY">
                                            <option value="No"><?php echo __('no', $lang); ?></option>
                                            <option value="Si"><?php echo __('yes', $lang); ?></option>
                                            <option value="Pendiente" selected><?php echo __('pending', $lang); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="typeId"><i class="fa fa-id-badge me-2"></i> <?php echo __('type_id', $lang); ?>:</label>
                                        <select class="shadow-sm form-select" name="type_id" id="type_id">
                                            <option value="CC" selected>CC</option>
                                            <option value="CE">CE</option>
                                            <option value="PP">PP</option>
                                            <option value="TI">TI</option>
                                            <option value="RC">RC</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila: Asset name -->
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="assetname"><i class="fas fa-tag me-2"></i><?php echo __('asset_name', $lang); ?>:</label>
                                <input class="shadow-sm form-control" type="text" id="assetname" name="assetname"
                                    autocomplete="off" required data-validate="true" value="CO-LPT"
                                    oninput="this.value = this.value.toUpperCase()">
                                <small id="asset-message" class="form-text"></small>
                                <script src="../View/Js/GetAsset.js"></script>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-center gap-3">
                            <button id="confirmAllButton" type="submit" class="btn btn-secondary">
                                <i class="fas fa-paper-plane me-2"></i><?php echo __('next', $lang); ?>: <?php echo __('asset_status', $lang); ?>
                            </button>
                            <a href="../../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i><?php echo __('return', $lang); ?>
                            </a>
                        </div>
                    </div>
                </form>
                <!-- Script modo oscuro -->
                      <script src="../View/Js/dark-mode-toggle-new.js"></script>
                
                        <!-- Script para la validacion de caracteres especiales exceptuando el "/" y "-" -->
                        <script>
                            // Función general para validar que no se ingresen caracteres especiales excepto "/" y "-"
                            function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
                                // Ajustar la expresión regular para incluir "/" y "-" y Ñ
                           // Permitir letras, números, espacios, "/", "-","_","." y "@"
    const regex = /^[a-zA-Z0-9\s\/\-\@Ññ]+$/;
    inputElement.addEventListener('input', function() {
        const inputValue = this.value;

        if (!regex.test(inputValue)) {
            mensajeElement.textContent = 'Characters are not allowed except  "/", "-","_","." y "@".';
            // Remover caracteres no válidos
            this.value = this.value.replace(/[^a-zA-Z0-9\s\/\-_.@Ññ]/g, '');
        } else {
            mensajeElement.textContent = '';
        }

        // Validar longitud máxima
        const maxLength = inputElement.dataset.maxLength ? parseInt(inputElement.dataset.maxLength) : 100;
        const currentValue = this.value;

        if (currentValue.length > maxLength) {
            mensajeElement.textContent = `Maximum ${maxLength} characters allowed.`;
            this.value = currentValue.slice(0, maxLength);
        }
    });
}
                            document.addEventListener('DOMContentLoaded', function() {
                                const fieldsToValidate = document.querySelectorAll('[data-validate="true"]');
                                fieldsToValidate.forEach(inputElement => {
                                    const mensajeElement = document.createElement('p');
                                    mensajeElement.classList.add('text-danger');
                                    inputElement.parentNode.appendChild(mensajeElement);

                                    validarSinCaracteresEspeciales(inputElement, mensajeElement);
                                });
                            });
                        </script>
                        <script>
                            // validacion reciclada para el impedimento del envio del formulario sus lescel no estan seleccionados
                            document.getElementById('registform').addEventListener('submit', function(event) {
                                const select1 = document.getElementById('usrStts');

                                let errorMessage = "Please fix the following errors: \n";
                                let hasError = false;

                                if (select1.value === '0') {
                                    errorMessage += "- Select a User Status.\n";
                                    hasError = true;
                                }
                                if (hasError) {
                                    event.preventDefault();
                                    alert(errorMessage);
                                } else {
                                    if (!confirm('Are you sure you want to update this data?')) {
                                        event.preventDefault(); // Cancelar el envío si el usuario no confirma
                                    }
                                }
                            });
                        </script>
                </form>
            </div>
        </div>
    </div>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    
    <!-- Scripts de validación -->
    <script>
        // Validación de Cédula/ID
        const inputElement = document.getElementById('id');
        const mensaje = document.getElementById('mensaje');

        inputElement.addEventListener('input', function() {
            const inputValue = this.value;
            const regex = /^[0-9]+$/;

            // Validar que solo sean números
            if (!regex.test(inputValue)) {
                mensaje.textContent = 'Por favor ingrese solo números.';
                this.value = this.value.replace(/[^0-9]/g, '');
                return;
            }

            // Validar longitud
            if (inputValue.length > 10) {
                mensaje.textContent = 'Solo se permiten 10 dígitos.';
                this.value = inputValue.slice(0, 10);
                return;
            }
            // Si pasa todas las validaciones, limpiar mensaje
            mensaje.textContent = '';
        });

        // Validación de Nombre de Activo
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById("nombre_usuario");
            const assetnameInput = document.getElementById("assetname");

            const updateAssetName = function() {
                const username = usernameInput.value.trim();

                if (username) {
                    try {
                        const names = username.split(" ");
                        const initials = names.length > 2 ?
                            names[0][0].toUpperCase() + names[0][1].toUpperCase() +
                            names[2][0].toUpperCase() + names[2][1].toUpperCase() :
                            names.length > 1 ?
                            names[0][0].toUpperCase() + names[0][1].toUpperCase() +
                            names[1][0].toUpperCase() + names[1][1].toUpperCase() :
                            names[0].slice(0, 4).toUpperCase();

                        const newAssetValue = `CO-LPT-${initials}`;
                        assetnameInput.value = newAssetValue;
                    } catch (error) {
                        console.error("Error al obtener iniciales del nombre:", error);
                    }
                } else {
                    assetnameInput.value = 'CO-LPT';
                }
            };

            // Actualizar cuando cambie el nombre de usuario
            usernameInput.addEventListener("input", updateAssetName);

            // Actualizar al cargar la página
            updateAssetName();
        });

        // Validación de Fecha de Garantía
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha_ingreso');
            
            // Establecer fecha actual como valor predeterminado
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            fechaInput.value = `${yyyy}-${mm}-${dd}`;

            // Validación de fecha mínima (ejemplo: no antes de 2000)
            fechaInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const minDate = new Date('2000-01-01');
                
                if (selectedDate < minDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fecha Inválida',
                        text: 'Por favor ingrese una fecha posterior al 1 de enero de 2000.',
                        confirmButtonColor: '#3085d6'
                    });
                    this.value = '';
                }
            });
        });

        // Validación General de Caracteres
        function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
            const regex = /^[a-zA-Z0-9\s\/\-\@Ññ]+$/;
            
            inputElement.addEventListener('input', function() {
                const inputValue = this.value;

                if (!regex.test(inputValue)) {
                    mensajeElement.textContent = 'No se permiten caracteres especiales excepto "/", "-", "_", "." y "@".';
                    this.value = this.value.replace(/[^a-zA-Z0-9\s\/\-_.@Ññ]/g, '');
                } else {
                    mensajeElement.textContent = '';
                }

                // Validar longitud máxima
                const maxLength = inputElement.dataset.maxLength ? parseInt(inputElement.dataset.maxLength) : 100;
                if (inputValue.length > maxLength) {
                    mensajeElement.textContent = `Máximo ${maxLength} caracteres permitidos.`;
                    this.value = inputValue.slice(0, maxLength);
                }
            });
        }

        // Aplicar validación a todos los campos que requieren validación
        document.addEventListener('DOMContentLoaded', function() {
            const fieldsToValidate = document.querySelectorAll('[data-validate="true"]');
            fieldsToValidate.forEach(inputElement => {
                const mensajeElement = document.createElement('p');
                mensajeElement.classList.add('text-danger');
                inputElement.parentNode.appendChild(mensajeElement);
                validarSinCaracteresEspeciales(inputElement, mensajeElement);
            });
        });

        // Validación del Formulario
        document.getElementById('registform').addEventListener('submit', function(event) {
            const select1 = document.getElementById('usrStts');
            let errorMessage = "Por favor corrija los siguientes errores: \n";
            let hasError = false;

            if (select1.value === '0') {
                errorMessage += "- Seleccione un Estado de Usuario.\n";
                hasError = true;
            }

            if (hasError) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Validación',
                    html: errorMessage.replace(/\n/g, '<br>'),
                    confirmButtonColor: '#3085d6'
                });
            } else {
                event.preventDefault();
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea guardar estos datos?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>