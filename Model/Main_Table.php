<?php
// Incluir la conexión a la base de datos
include_once './Configuration/Connection.php';

// Mostrar mensajes de error o éxito si existen
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger mt-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ' . htmlspecialchars($_SESSION['error_message']) . '
          </div>';
    unset($_SESSION['error_message']);
}

// Precalcula el colspan una vez
$colspan = count($selectedColumns) + 12;

// Inicializar $resultados
$resultados = [];

// Verificar si hay resultados de búsqueda en la sesión
if (isset($_SESSION['resultados_busqueda'])) {
   $resultados = $_SESSION['resultados_busqueda'];
   
   // Mostrar mensaje de resultados
   if (empty($resultados)) {
       echo '<div class="alert alert-info mt-3" role="alert">
               <i class="bi bi-info-circle me-2"></i>
               No se encontraron resultados para los criterios de búsqueda especificados.
             </div>';
   } else {
       $count = count($resultados);
       echo '<div class="alert alert-success mt-3" role="alert">
               <i class="bi bi-check-circle me-2"></i>
               Se encontraron ' . $count . ' resultado(s).
             </div>';
   }
} else {
    // Si no hay resultados en la sesión, obtener todos los registros
    try {
        $stmt = $pdo->prepare("SELECT * FROM vista_equipos_usuarios");
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener datos: " . $e->getMessage());
        $resultados = [];
    }
}

if ($resultados && is_array($resultados) && count($resultados) > 0) {
   $currentTimestamp = time(); // Tiempo actual fuera del bucle
   $htmlRows = []; // Almacenar todas las filas

   foreach ($resultados as $row) {
       // Verificar que $row sea un array antes de procesar
       if (!is_array($row)) {
           continue;
       }

       // Optimización 1: Validación de fecha más eficiente con verificación de existencia de clave
       $warranty_class = 'status-pending';
       $dateStr = isset($row['warranty_enddate']) ? $row['warranty_enddate'] : '';

       // Parseo manual más rápido que DateTime
       if (!empty($dateStr) && sscanf($dateStr, "%d/%d/%d", $month, $day, $year) === 3 && checkdate($month, $day, $year)) {
           $warrantyTimestamp = mktime(0, 0, 0, $month, $day, $year);
           $warranty_class = $warrantyTimestamp < $currentTimestamp
               ? 'status-inactive'
               : 'status-active';
       }

       // Determinar la clase para el estado del usuario
       $userStatusClass = 'status-pending';
       if (isset($row['user_status'])) {
           switch(strtolower(trim($row['user_status']))) {
               case 'active':
                   $userStatusClass = 'status-active';
                   break;
               case 'inactive':
                   $userStatusClass = 'status-inactive';
                   break;
           }
       }

       // Optimización 2: Escapar solo cuando se usa y construir HTML como array
       $assetname = isset($row['assetname']) ? htmlspecialchars($row['assetname']) : '';
       $rowHtml = [
           "<tr class='table-row'>",
           "<td><div class='form-check d-flex justify-content-center'><input class='form-check-input' type='checkbox' name='selected_assets[]' value='$assetname'></div></td>",
           "<td><span class='fw-medium'>" . $assetname . "</span></td>",
           "<td><span class='text-secondary'>" . (isset($row['serial_number']) ? htmlspecialchars($row['serial_number']) : '') . "</span></td>",
           "<td><span class='$userStatusClass'>" . (isset($row['user_status']) ? htmlspecialchars($row['user_status']) : '') . "</span></td>",
           "<td>" . (isset($row['last_user']) ? htmlspecialchars($row['last_user']) : '') . "</td>",
           "<td><span class='text-muted'>" . (isset($row['job_title']) ? htmlspecialchars($row['job_title']) : '') . "</span></td>",
           "<td>" . (isset($row['cedula']) ? htmlspecialchars($row['cedula']) : '') . "</td>",
           "<td>" . (isset($row['fecha_ingreso']) ? htmlspecialchars($row['fecha_ingreso']) : '') . "</td>",
           "<td>" . (isset($row['fecha_salida']) ? htmlspecialchars($row['fecha_salida']) : '') . "</td>"
       ];

       // Optimización 3: Procesamiento de columnas dinámicas
       foreach ($selectedColumns as $column) {
           $value = isset($row[$column]) ? $row[$column] : '';
           $processed = (is_numeric($value) && in_array($value, ['0', '1'], true))
               ? ($value == '1' ? 'Yes' : 'No')
               : $value;
           $rowHtml[] = "<td>" . htmlspecialchars($processed) . "</td>";
       }

       // Botones con valor ya escapado y estilos mejorados
       if (!empty($assetname)) {
           $rowHtml[] = "<td>
               <a href='./Model/act_registro.php?assetname=$assetname' class='action-btn action-btn-edit' title='Update'>
                   <i class='fas fa-sync-alt sync-icon'></i>
               </a>
           </td>";
           
           // Improved dropdown implementation
           $uniqueId = 'dropdown_' . preg_replace('/[^a-zA-Z0-9]/', '_', $assetname);
           $rowHtml[] = "<td>
               <div class='dropdown'>
                   <button class='action-btn action-btn-view dropdown-toggle' type='button' id='$uniqueId' data-bs-toggle='dropdown' aria-expanded='false'>
                       <i class='fas fa-eye'></i>
                   </button>
                   <ul class='dropdown-menu' aria-labelledby='$uniqueId'>
                       <li><a class='dropdown-item' href='./View/preview-salida.php?assetname=$assetname'>
                           <i class='fas fa-file-export me-2'></i>Departure Preview
                       </a></li>
                       <li><a class='dropdown-item' href='./View/preview-entrada.php?assetname=$assetname'>
                           <i class='fas fa-file-import me-2'></i>Entry Preview
                       </a></li>
                   </ul>
               </div>
           </td>";
           
           $rowHtml[] = "<td>
               <a href='./Model/act_registro_status.php?assetname=$assetname' class='action-btn action-btn-view' title='Status and Observations'>
                   <i class='far fa-check-square'></i>
               </a>
           </td>";
           
           $rowHtml[] = "<td>
               <a href='javascript:void(0);' class='action-btn action-btn-delete delete-item' data-assetname='$assetname' title='Delete'>
                   <i class='fa fa-times'></i>
               </a>
           </td>";
       } else {
           // Si no hay assetname, agregar celdas vacías para mantener la estructura de la tabla
           $rowHtml[] = "<td></td><td></td><td></td><td></td>";
       }
       
       $rowHtml[] = "</tr>";

       // Optimización 4: Unir partes de la fila
       $htmlRows[] = implode('', $rowHtml);
   }

   echo implode('', $htmlRows);
} else {
   echo "<tr><td colspan='$colspan' class='text-center py-4'>
           <div class='d-flex flex-column align-items-center'>
               <i class='bi bi-inbox-fill fs-1 text-muted mb-3'></i>
               <p class='text-muted'>No data found</p>
           </div>
         </td></tr>";
}
?>
<script>
   document.addEventListener('DOMContentLoaded', function() {
       // Initialize dropdowns
       if (typeof bootstrap !== 'undefined') {
           var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
           var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
               return new bootstrap.Dropdown(dropdownToggleEl);
           });
       }
       
       document.querySelectorAll('.delete-item').forEach(button => {
           button.addEventListener('click', function(event) {
               event.preventDefault();
               const assetname = this.getAttribute('data-assetname');
               Swal.fire({
                   title: 'Confirm Deletion',
                   text: `Are you sure you want to delete the log for ${assetname}? This action cannot be undone.`,
                   icon: 'warning',
                   showCancelButton: true,
                   confirmButtonColor: '#dc3545',
                   cancelButtonColor: '#6c757d',
                   confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>Yes, delete it!',
                   cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
                   customClass: {
                       confirmButton: 'btn btn-danger',
                       cancelButton: 'btn btn-secondary'
                   },
                   buttonsStyling: false,
                   showClass: {
                       popup: 'animate__animated animate__fadeInDown'
                   },
                   hideClass: {
                       popup: 'animate__animated animate__fadeOutUp'
                   }
               }).then((result) => {
                   if (result.isConfirmed) {
                       // Add loading state
                       Swal.fire({
                           title: 'Deleting...',
                           html: 'Please wait while we delete the record.',
                           allowOutsideClick: false,
                           didOpen: () => {
                               Swal.showLoading();
                           }
                       });
                       
                       // Redirect after a short delay to show loading state
                       setTimeout(() => {
                           window.location.href = './Model/elim_registro.php?assetname=' + assetname;
                       }, 500);
                   }
               });
           });
       });
       
       // Add hover effect to table rows
       document.querySelectorAll('#mainTable tbody tr').forEach(row => {
           row.addEventListener('mouseenter', function() {
               this.classList.add('highlight');
           });
           row.addEventListener('mouseleave', function() {
               this.classList.remove('highlight');
           });
       });
   });
</script>