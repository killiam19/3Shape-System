<?php
session_start(); //Inicio de la sesion para que la funcion pueda funcionar
include "../Configuration/Connection.php";
// Verificar si hay datos disponibles

if (!isset($_SESSION['asset_data'])) {
    echo "No se han encontrado datos para mostrar.";
    exit;
}

// Obtener los datos desde la sesión
$asset_data = $_SESSION['asset_data'];

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
?>
<?php include '../View/Fragments/idioma.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
     <!-- Añade Font Awesome -->
     <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
     <link rel="stylesheet" href="./Css/FormsCRUD.css">
     <link rel="stylesheet" href="./Css/dark-mode.css">
     <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <!--Archivo con la funcion para la interfaz de  actualizacion del activo-->
    <script src="./Configuration/JQuery/sweetalert2.all.min.js" defer></script>
    <title><?php echo __('update_register', $lang); ?></title>
</head>
<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div id="FORMupdateform" class="shadow-lg p-5 rounded bg-light">

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
                
                <!-- Formulario de Actualización donde trae los datos del activo seleccionado -->
                <form id="updateform" action="../Controller/procesar_datos.php" method="post">
                    <h2 id="totalC" class="text-center"><?php echo __('update_register', $lang); ?></h2>
                    <br>
                    
                    <div class="row">
                        <!-- Primera fila: Usuario y Serial -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nombre_usuario"><i class="fas fa-user me-2"></i><?php echo __('user_name', $lang); ?>:</label>
                                <input class="form-control shadow-sm" type="text" id="nombre_usuario" name="nombre_usuario"
                                    value="<?php echo htmlspecialchars($asset_data['last_user']); ?>"
                                    autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="serial"><i class="fas fa-barcode me-2"></i><?php echo __('serial_number', $lang); ?>:</label>
                                <input class="form-control shadow-sm" type="text" id="serial" name="serial"
                                    value="<?php echo htmlspecialchars($asset_data['serial_number']); ?>" required
                                    autocomplete="off" readonly>
                            </div>
                        </div>
                        
                        <!-- Segunda fila: Fecha de ingreso y Estado de usuario -->
                 <div class="col-md-6">
    <div class="form-group mb-3">
        <label for="fecha_ingreso"><i class="fas fa-calendar-plus me-2"></i><?php echo __('entry_date', $lang); ?>:</label>
<input class="form-control shadow-sm" type="date" id="fecha_ingreso" name="fecha_ingreso"
       value="<?php echo !empty($asset_data['fecha_ingreso']) ? htmlspecialchars($asset_data['fecha_ingreso']) : date('Y-m-d'); ?>"
       required>
                                <script>
                                   document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('ingreso');
        
        // Validar fecha mínima (ejemplo: no antes de 2000)
        const minDate = new Date('2000-01-01');
        if (selectedDate < minDate) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'Please enter a date after January 1, 2000.',
                confirmButtonColor: '#3085d6'
            });
            this.value = ''; // Limpiar el campo
        }
    });
    
    // Validación al enviar el formulario
    document.getElementById('updateform').addEventListener('submit', function(e) {
        var fechaInput = document.getElementById('fecha_ingreso');
        if (!fechaInput.value) {
            // Si está vacío, poner la fecha actual
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            fechaInput.value = yyyy + '-' + mm + '-' + dd;
        }
    });
                                </script>
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
                                <input class="form-control shadow-sm" type="text" id="job_title" name="job_title"
                                    value="<?php echo htmlspecialchars($asset_data['job_title']); ?>" autocomplete="off"
                                    list="job_titles">
                                <datalist id="job_titles">
                                    <?php
                                    $job_titles = $pdo->query("SELECT DISTINCT job_title FROM usuarios_equipos ORDER BY job_title ASC")->fetchAll(PDO::FETCH_COLUMN);
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
                                <input class="form-control shadow-sm" type="text" id="id" name="id"
                                    value="<?php echo htmlspecialchars($asset_data['cedula']); ?>" required
                                    autocomplete="off">
                                <p id="mensaje" class="text-danger"></p>
                                <script>
                                    const inputElement = document.getElementById('id');
                                    const mensaje = document.getElementById('mensaje');

                                    inputElement.addEventListener('input', function() {
                                        const inputValue = this.value;
                                        const regex = /^[0-9]+$/;

                                        // Validar que solo sean números
                                        if (!regex.test(inputValue)) {
                                            mensaje.textContent = 'Please enter only numbers.';
                                            this.value = this.value.replace(/[^0-9]/g, '');
                                            return;
                                        }

                                        // Validar longitud
                                        if (inputValue.length > 10) {
                                            mensaje.textContent = 'Only 10 digits are allowed.';
                                            this.value = inputValue.slice(0, 10);
                                            return;
                                        }
                                        // Si pasa todas las validaciones, limpiar mensaje
                                        mensaje.textContent = '';
                                    });

                                    // Prevenir entrada de valores negativos o caracteres especiales
                                    inputElement.addEventListener('keydown', function(e) {
                                        if (e.key === '-' || e.key === '+' || e.key === 'e' || e.key === 'E') {
                                            e.preventDefault();
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                        
                        <!-- Campos dinámicos -->
                        <?php if (isset($_SESSION['new_upd_fields'])): ?>
                            <?php foreach ($_SESSION['new_upd_fields'] as $field_name): ?>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="<?php echo htmlspecialchars($field_name); ?>">
                                            <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($field_name); ?>:
                                        </label>
                                        <?php
                                        // Retrieve the value of the field from the asset data, ensuring it is safe for HTML output
                                        // Recupera el valor de la columna del dato del activo, asegurando asi la segurdad de la salida por html
                                        $field_value = isset($asset_data[$field_name]) ? htmlspecialchars($asset_data[$field_name]) : '';
                                        // Determine the type of the field from the session data
                                        // Determina el tipo de la columna the la sesion 
                                        $field_type = $_SESSION['new_fieldU_types'][$field_name];
                                        ?>
                                        <?php if ($field_type === 'date'): ?>
                                            <input class="form-control shadow-sm" type="date"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value; ?>" autocomplete="off">
                                        <?php elseif ($field_type === 'tinyint'): ?>
                                            <select class="form-control shadow-sm" id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>" autocomplete="off"
                                                data-validate="true">
                                                <option value="0" <?php echo ($field_value == 0) ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?php echo ($field_value == 1) ? 'selected' : ''; ?>>Yes</option>
                                            </select>
                                        <?php elseif (in_array($field_type, ['int', 'bigint', 'smallint'])): ?>
                                            <!-- Render a number input field for integer types, ensuring non-negative values -->
                                            <!-- Renderiza un numero en base al tipo de entra de al columan entero, entrogrande..etc para evitar numero negativos -->
                                            <input class="form-control shadow-sm" type="number"
                                                id="<?php echo htmlspecialchars($field_name); ?>"
                                                name="<?php echo htmlspecialchars($field_name); ?>"
                                                value="<?php echo $field_value ?: 0; ?>" autocomplete="off" min="0"
                                                oninput="this.value = Math.abs(this.value)">
                                        <?php else: ?>
                                            <!-- Render a text input field for other types -->
                                            <!-- Renderiza  un tipo de entrada de texto para el campo u otros tipos-->
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
                                            <option value="Pendiente" <?php echo ($asset_data['Carnet'] == 'Pendiente') ? 'selected' : ''; ?>>
                                               <?php echo __('pending', $lang); ?></option>
                                            <option value="No" <?php echo ($asset_data['Carnet'] == 'No') ? 'selected' : ''; ?>>
                                                <?php echo __('no ', $lang); ?></option>
                                            <option value="Si" <?php echo ($asset_data['Carnet'] == 'Si') ? 'selected' : ''; ?>>
                                                <?php echo __('yes', $lang); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="selectKEY"><i class="fas fa-key me-2"></i> <?php echo __('key', $lang); ?>:</label>
                                        <select class="shadow-sm form-select" name="selectKEY" id="selectKEY">
                                            <option value="Pendiente" <?php echo ($asset_data['LLave'] == 'Pendiente') ? 'Selected' : ''; ?>>
                                                <?php echo __('pending', $lang); ?></option>
                                            <option value="No" <?php echo ($asset_data['LLave'] == 'No') ? 'selected' : ''; ?>>
                                                <?php echo __('no ', $lang); ?></option>
                                            <option value="Si" <?php echo ($asset_data['LLave'] == 'Si') ? 'selected' : ''; ?>>
                                                <?php echo __('yes', $lang); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="selectID"><i class="fa fa-id-badge me-2"></i> <?php echo __('type_id', $lang); ?>:</label>
                                        <?php
                                        $typeIds = ['CC', 'CE', 'PP', 'TI', 'RC'];
                                        ?>
                                        <select class="shadow-sm form-select" name="selectID" id="selectID">
                                            <?php foreach ($typeIds as $typeId): ?>
                                                <option value="<?php echo $typeId; ?>" <?php echo ($asset_data['Tipo_ID'] == $typeId) ? 'selected' : ''; ?>>
                                                    <?php echo $typeId; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fila: Asset name -->
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="assetname"><i class="fas fa-tag me-2"></i><?php echo __('asset_name', $lang); ?>:</label>
                                <input class="form-control shadow-sm" type="text" id="assetname" name="assetname"
                                    value="<?php echo htmlspecialchars($asset_data['assetname']); ?>" required
                                    oninput="this.value = this.value.toUpperCase()">
                                <small id="asset-message" class="form-text"></small>
                                <script>
                                    document.addEventListener('DOMContentLoaded', async function() {
                                        const usernameInput = document.getElementById("nombre_usuario");
                                        const assetnameInput = document.getElementById("assetname");
                                        const originalAssetName = assetnameInput.value; // Store original value

                                        const updateAssetName = async function() {
                                            const username = usernameInput.value.trim();

                                            if (username) {
                                                try {
                                                    const names = username.split(" ");
                                                    const initials = names.length > 2 ?
                                                        names[0][0].toUpperCase() + names[0][1]
                                                        .toUpperCase() +
                                                        names[2][0].toUpperCase() + names[2][1]
                                                        .toUpperCase() :
                                                        names.length > 1 ?
                                                        names[0][0].toUpperCase() + names[0][1]
                                                        .toUpperCase() +
                                                        names[1][0].toUpperCase() + names[1][1]
                                                        .toUpperCase() :
                                                        names[0].slice(0, 4).toUpperCase();

                                                    const newAssetValue = `CO-LPT-${initials}`;
                                                    assetnameInput.value = newAssetValue;
                                                } catch (error) {
                                                    console.error("Error getting user name initials:", error);
                                                    assetnameInput.value = originalAssetName; // Fallback to original
                                                }
                                            } else {
                                                assetnameInput.value = originalAssetName; // Restore original if empty
                                            }
                                        };

                                        // Only update when username changes, not on initial load
                                        usernameInput.addEventListener("input", updateAssetName);
                                    });
                                </script>
                                <script src="../View/Js/GetAsset.js"></script>
                                <input class="form-control" type="hidden" id="fk_Assetname" name="fk_Assetname"
                                    value="<?php echo $asset_data['assetname']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <button id="confirmAllButton" type="submit" class="btn btn-secondary"><?php echo __('send', $lang); ?></button>
                            <a href="../../index.php" class="btn btn-outline-secondary"><?php echo __('return', $lang); ?></a>
                        </div>
                    </div>
                </form>
                
                 <script src="../View/Js/dark-mode-toggle-new.js"></script>
                <script>
                    // Función general para validar que no se ingresen caracteres especiales
function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
    // Actualizar regex para incluir "@"
    const regex = /^[a-zA-Z0-9\s\/\-\@Ññ]+$/;
    inputElement.addEventListener('input', function() {
        const inputValue = this.value;

        if (!regex.test(inputValue)) {
            mensajeElement.textContent = 'Characters are not allowed except "/", "-", and "@".';
            // Remover caracteres no válidos, incluyendo "@"
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
                    function validarAlgunosCaracteresEspeciales(inputElement, mensajeElement) {
                        const regex = /^[a-zA-Z0-9\s\/\-Ññ]+$/;

                        inputElement.addEventListener('input', function() {
                            const originalValue = this.value;
                            // Limpiar caracteres no permitidos (incluyendo Ññ)
                            const cleanedValue = originalValue.replace(/[^a-zA-Z0-9\s\/\-Ññ]/g, '');

                            // Verificar si se eliminaron caracteres no válidos
                            if (cleanedValue !== originalValue) {
                                mensajeElement.textContent = 'Characters are not allowed.';
                                this.value = cleanedValue;
                            } else {
                                mensajeElement.textContent = '';
                            }

                            // Validar longitud máxima (usar data-max-length o 50 por defecto)
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

                            validarAlgunosCaracteresEspeciales(inputElement, mensajeElement);
                        });
                    });

                    // Aplicar validación al campo `nombre_usuario`
                    const nombreUsuarioInput = document.getElementById('nombre_usuario');
                    const mensajeNombreUsuario = document.createElement('p');
                    mensajeNombreUsuario.classList.add('text-danger');
                    nombreUsuarioInput.parentNode.appendChild(mensajeNombreUsuario);

                    validarSinCaracteresEspeciales(nombreUsuarioInput, mensajeNombreUsuario);

                    // Aplicar validación al campo `newl`
                    const newLaptopInput = document.getElementById('newl');
                    const mensajeNewLaptop = document.createElement('p');
                    mensajeNewLaptop.classList.add('text-danger');
                    newLaptopInput.parentNode.appendChild(mensajeNewLaptop);

                    validarSinCaracteresEspeciales(newLaptopInput, mensajeNewLaptop);
                </script>
            </div>
        </div>
    </div>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script>
        //Alerta SweetAlert
        // Load SweetAlert2 script
document.addEventListener('DOMContentLoaded', function() {
    // Add SweetAlert2 JS if not already included
    if (typeof Swal === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.body.appendChild(script);
    }
    
    // Replace the current form submit handler with SweetAlert confirmation
    document.getElementById('updateform').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        
        const select1 = document.getElementById('usrStts');

        const requiredSelects = [select1];
        const errorMessages = [];

        requiredSelects.forEach((select, index) => {
            if (select.value === '0') {
                let message;
                switch (index) {
                    case 0:
                        message = "- Select a User Status.";
                        break;
                }
                errorMessages.push(message);
            }
        });

        if (errorMessages.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: "Please fix the following errors: <br>" + errorMessages.join("<br>"),
                confirmButtonColor: '#3085d6'
            });
        } else {
            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Update',
                text: "Are you sure you want to update this data?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form
                    const form = document.getElementById('updateform');
                    
                    // Use fetch API to submit the form
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(response => response.text())
                    .then(data => {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'The register has been updated successfully.',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            // Redirect to index page after success
                            window.location.href = '../index.php';
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'There was an error updating the data.',
                            confirmButtonColor: '#3085d6'
                        });
                    });
                }
            });
        }
    });
});
    </script>
</body>
</html>