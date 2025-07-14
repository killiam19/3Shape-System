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
<?php
// Consultar los estados únicos de cada tipo de activo desde la base de datos
try {
    // Estados predefinidos
    $estados_predefinidos = ["", "Bueno", "Regular", "Dañado", "Malo", "Perdido", "No aplica"];
    
    // Estados de computador
    $sql_asset_status = "SELECT DISTINCT asset_status FROM equipos WHERE asset_status IS NOT NULL AND asset_status != ''";
    $stmt_asset = $pdo->prepare($sql_asset_status);
    $stmt_asset->execute();
    $asset_status_options = array_unique(array_merge($estados_predefinidos, $stmt_asset->fetchAll(PDO::FETCH_COLUMN)));

    // Estados de headset
    $sql_headset_status = "SELECT DISTINCT headset_status FROM equipos WHERE headset_status IS NOT NULL AND headset_status != ''";
    $stmt_headset = $pdo->prepare($sql_headset_status);
    $stmt_headset->execute();
    $headset_status_options = array_unique(array_merge($estados_predefinidos, $stmt_headset->fetchAll(PDO::FETCH_COLUMN)));

    // Estados de dongle
    $sql_dongle_status = "SELECT DISTINCT dongle_status FROM equipos WHERE dongle_status IS NOT NULL AND dongle_status != ''";
    $stmt_dongle = $pdo->prepare($sql_dongle_status);
    $stmt_dongle->execute();
    $dongle_status_options = array_unique(array_merge($estados_predefinidos, $stmt_dongle->fetchAll(PDO::FETCH_COLUMN)));

    // Estados de celular
    $sql_celular_status = "SELECT DISTINCT celular_status FROM equipos WHERE celular_status IS NOT NULL AND celular_status != ''";
    $stmt_celular = $pdo->prepare($sql_celular_status);
    $stmt_celular->execute();
    $celular_status_options = array_unique(array_merge($estados_predefinidos, $stmt_celular->fetchAll(PDO::FETCH_COLUMN)));

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading status options: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Configuración de idioma
include '../View/Fragments/idioma.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
     <!--Font Awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel=" stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="./Css/FormsCRUD.css">
       <link rel="stylesheet" href="./Configuration/JQuery/sweetalert2.min.css">
    <!-- Carga diferida de scripts no críticos -->
    <script src="./Configuration/JQuery/sweetalert2.all.min.js" defer></script>
    <!--Archivo con la funcion para la interfaz de  actualizacion del activo-->
    <title><?php echo __('asset_status_and_observations', $lang); ?></title>
</head>

<body>
<div class="container">
    <div class="row d-flex justify-content-center align-items-center vh-100">
        <div id="FORMupdateform" class="shadow-lg p-5 rounded bg-light">
          

                <!--Formulario de Actualizacion donde trae los datos del activo seleccionado-->
                <form id="updateform" action="../Controller/procesar_estados.php" method="post" enctype="multipart/form-data">
                <h2 id="totalC" class="text-center"><?php echo __('asset_status_and_observations', $lang); ?></h2>
                <br>
                
                <!-- Sección de información básica del usuario -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_usuario"><?php echo __('user_name', $lang); ?>: </label>
                            <input class="form-control shadow-sm" type="text" id="nombre_usuario" name="nombre_usuario"
                                value="<?php echo htmlspecialchars($asset_data['last_user']); ?>"
                                autocomplete="off" readonly><br>
                        </div>
                        <div class="form-group">
                            <label for="serial"><?php echo __('serial_number', $lang); ?> (<?php echo __('computer', $lang); ?>): </label>
                            <input class="form-control shadow-sm" type="text" id="serial" name="serial"
                                value="<?php echo htmlspecialchars($asset_data['serial_number']); ?>" required
                                autocomplete="off" readonly><br>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- [Los campos de país de compra, garantía, etc. permanecen igual] -->
                    </div>
                </div>

                <!-- Sección de activos en 3 columnas -->
                <div class="row mt-4">
                    <h4 class="mb-3"><?php echo __('asset_information', $lang); ?></h4>
                    
                <!-- Computador -->
<div class="col-md-12 mb-4 border-bottom pb-3">
    <h5><i class="fas fa-laptop me-2"></i>Laptop</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="serial_number">Serial</label>
                <input class="form-control" type="text" id="serial_number" name="serial_number"
                    value="<?php echo htmlspecialchars($asset_data['serial_number']); ?>" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="asset_status"><?php echo __('status_asset', $lang); ?></label>
                <select class="form-control" id="asset_status" name="asset_status" >
                    <option value=""><?php echo __('select_status', $lang); ?></option>
                    <option value="Bueno" <?php echo ($asset_data['asset_status'] == 'Bueno') ? 'selected' : ''; ?>>Bueno</option>
                    <option value="Regular" <?php echo ($asset_data['asset_status'] == 'Regular') ? 'selected' : ''; ?>>Regular</option>
                    <option value="Dañado" <?php echo ($asset_data['asset_status'] == 'Dañado') ? 'selected' : ''; ?>>Dañado</option>
                    <option value="Malo" <?php echo ($asset_data['asset_status'] == 'Malo') ? 'selected' : ''; ?>>Malo</option>
                    <option value="Perdido" <?php echo ($asset_data['asset_status'] == 'Perdido') ? 'selected' : ''; ?>>Perdido</option>
                    <option value="No aplica" <?php echo ($asset_data['asset_status'] == 'No aplica') ? 'selected' : ''; ?>>No aplica</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="asset_observations"><?php echo __('observations', $lang); ?></label>
                <textarea class="form-control" id="asset_observations" name="asset_observations"
                    rows="2"><?php echo htmlspecialchars($asset_data['asset_observations'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    <!-- Sección de subir foto -->
    <div class="row mt-3">
    <h5><i class="fas fa-image"></i><?php echo __('upload_asset_photo', $lang); ?></h5>
    <div class="col-md-6">
        <div class="form-group">
            <label for="asset_photo"><?php echo __('photo', $lang); ?> (<?php echo __('optional', $lang); ?>)</label>
            <input class="form-control" type="file" id="asset_photo" name="asset_photo" accept="image/*">
        </div>
    </div>
    <div class="col-md-6">
        <?php if (!empty($asset_data['asset_photo'])): ?>
            <div class="d-flex align-items-center">
                <!-- Imagen como botón para abrir modal -->
                <button type="button" class="btn p-0 border-0 bg-transparent" id="viewPhotoBtn">
                    <img src="../uploads/<?php echo htmlspecialchars($asset_data['asset_photo']); ?>" 
                         alt="Asset Photo" 
                         class="img-thumbnail me-2" 
                         width="150"
                         style="cursor: pointer;">
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="deletePhotoBtn">
                    <i class="fas fa-trash"></i> Delete Photo
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- HeadSet -->
<div class="col-md-12 mb-4 border-bottom pb-3">
    <h5><i class="fas fa-headset me-2"></i>HeadSet</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="HeadSet">Serial</label>
                <input class="form-control" type="text" id="HeadSet" name="HeadSet"
                    value="<?php echo htmlspecialchars($asset_data['HeadSet'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="headset_status"><?php echo __('status_asset', $lang); ?></label>
                <select class="form-control" id="headset_status" name="headset_status">
                    <option value=""><?php echo __('select_status', $lang); ?></option>
                    <?php foreach ($headset_status_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo ($asset_data['headset_status'] == $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (!in_array($asset_data['headset_status'], $headset_status_options) && !empty($asset_data['headset_status'])): ?>
                        <option value="<?php echo htmlspecialchars($asset_data['headset_status']); ?>" selected>
                            <?php echo htmlspecialchars($asset_data['headset_status']); ?> (Current)
                        </option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="headset_observations"><?php echo __('observations', $lang); ?></label>
                <textarea class="form-control" id="headset_observations" name="headset_observations"
                    rows="2"><?php echo htmlspecialchars($asset_data['headset_observations'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Dongle -->
<div class="col-md-12 mb-4 border-bottom pb-3">
    <h5><i class="fab fa-usb me-2"></i>Dongle</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="Dongle">Serial</label>
                <input class="form-control" type="text" id="Dongle" name="Dongle"
                    value="<?php echo htmlspecialchars($asset_data['Dongle'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="dongle_status"><?php echo __('status_asset', $lang); ?></label>
                <select class="form-control" id="dongle_status" name="dongle_status">
                    <option value=""><?php echo __('select_status', $lang); ?></option>
                    <?php foreach ($dongle_status_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo ($asset_data['dongle_status'] == $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (!in_array($asset_data['dongle_status'], $dongle_status_options) && !empty($asset_data['dongle_status'])): ?>
                        <option value="<?php echo htmlspecialchars($asset_data['dongle_status']); ?>" selected>
                            <?php echo htmlspecialchars($asset_data['dongle_status']); ?> (Current)
                        </option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="dongle_observations"><?php echo __('observations', $lang); ?></label>
                <textarea class="form-control" id="dongle_observations" name="dongle_observations"
                    rows="2"><?php echo htmlspecialchars($asset_data['dongle_observations'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Celular -->
<div class="col-md-12 mb-4">
    <h5><i class="fas fa-mobile-alt me-2"></i><?php echo __('cell_phone', $lang); ?></h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="Celular">Serial</label>
                <input class="form-control" type="text" id="Celular" name="Celular"
                    value="<?php echo htmlspecialchars($asset_data['Celular'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="celular_status"><?php echo __('status_asset', $lang); ?></label>
                <select class="form-control" id="celular_status" name="celular_status">
                    <option value=""><?php echo __('select_status', $lang); ?></option>
                    <?php foreach ($celular_status_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo ($asset_data['celular_status'] == $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (!in_array($asset_data['celular_status'], $celular_status_options) && !empty($asset_data['celular_status'])): ?>
                        <option value="<?php echo htmlspecialchars($asset_data['celular_status']); ?>" selected>
                            <?php echo htmlspecialchars($asset_data['celular_status']); ?> (Current)
                        </option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="celular_observations"><?php echo __('observations', $lang); ?></label>
                <textarea class="form-control" id="celular_observations" name="celular_observations"
                    rows="2"><?php echo htmlspecialchars($asset_data['celular_observations'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>

                    <br>

                    <div class="col-md-6">
    <button id="confirmAllButton" type="submit" class="btn btn-secondary my-2">
        <i class="fas fa-paper-plane me-2"></i><?php echo __('send', $lang); ?>
    </button>
    <a href="../../index.php" class="btn my-2">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('return', $lang); ?>
    </a>
</div>
                </form>

<!-- Modal para visualizar la imagen -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asset Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Asset Photo" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
     <script src="../View/Js/dark-mode-toggle-new.js"></script>
                <script>
                    // Función general para validar que no se ingresen caracteres especiales
                    function validarSinCaracteresEspeciales(inputElement, mensajeElement) {
                        const regex = /^[a-zA-Z0-9\s\/\-Ññ]+$/; // Solo permite letras, números y espacios
                        inputElement.addEventListener('input', function() {
                            const inputValue = this.value;

                            if (!regex.test(inputValue)) {
                                mensajeElement.textContent = 'characteres are not allowed.';
                                this.value = this.value.replace(/[^a-zA-Z0-9\s]/g,
                                    ''); // Remover caracteres no válidos
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

                    document.getElementById('updateform').addEventListener('submit', function(event) {
                        const select1 = document.getElementById('compra');
                        const select2 = document.getElementById('usrStts');

                        const requiredSelects = [select1, select2, select3];
                        const errorMessages = [];

                        requiredSelects.forEach((select, index) => {
                            if (select.value === '0') {
                                let message;
                                switch (index) {
                                    case 0:
                                        message = "- Select a Purchase Country.";
                                        break;
                                    case 1:
                                        message = "- Select a User Status.";
                                        break;
                                    case 2:
                                        message = "- Select a Status Change.";
                                        break;
                                }
                                errorMessages.push(message);
                            }
                        });

                        if (errorMessages.length > 0) {
                            event.preventDefault();
                            alert("Please fix the following errors: \n" + errorMessages.join("\n"));
                        } else {
                            if (!confirm('Are you sure you want to update this data?')) {
                                event.preventDefault();
                            }
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const deletePhotoBtn = document.getElementById('deletePhotoBtn');
    
    if (deletePhotoBtn) {
        deletePhotoBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Delete photo?',
                text: "Are you sure you want to delete the asset photo?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear un campo oculto en el formulario para indicar que se debe eliminar la foto
                    const deletePhotoInput = document.createElement('input');
                    deletePhotoInput.type = 'hidden';
                    deletePhotoInput.name = 'delete_photo';
                    deletePhotoInput.value = '1';
                    document.getElementById('updateform').appendChild(deletePhotoInput);
                    
                    // Ocultar la imagen y el botón inmediatamente para mejor experiencia de usuario
                    const photoContainer = deletePhotoBtn.closest('.d-flex');
                    if (photoContainer) {
                        photoContainer.style.display = 'none';
                    }
                    
                    Swal.fire(
                        'Deleted!',
                        'The photo will be removed when you submit the form.',
                        'success'
                    );
                }
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el modal para ver la imagen
    const viewPhotoBtn = document.getElementById('viewPhotoBtn');
    if (viewPhotoBtn) {
        viewPhotoBtn.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').src;
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imgSrc;
            
            // Mostrar el modal
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        });
    }

    // Configurar el botón de eliminar foto (versión mejorada)
    const deletePhotoBtn = document.getElementById('deletePhotoBtn');
    if (deletePhotoBtn) {
        deletePhotoBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Delete photo?',
                text: "Are you sure you want to delete the asset photo?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear un campo oculto en el formulario
                    const deletePhotoInput = document.createElement('input');
                    deletePhotoInput.type = 'hidden';
                    deletePhotoInput.name = 'delete_photo';
                    deletePhotoInput.value = '1';
                    document.getElementById('updateform').appendChild(deletePhotoInput);
                    
                    // Ocultar el contenedor de la foto
                    const photoContainer = deletePhotoBtn.closest('.d-flex');
                    if (photoContainer) {
                        photoContainer.style.display = 'none';
                    }
                    
                    Swal.fire(
                        'Deleted!',
                        'The photo will be removed when you submit the form.',
                        'success'
                    );
                }
            });
        });
    }
});
</script>

    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>