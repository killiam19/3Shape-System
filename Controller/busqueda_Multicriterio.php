<?php
// Verificar si la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
include_once "../Configuration/Connection.php";

// Verificar si la conexión está establecida
if (!isset($pdo)) {
    $_SESSION['error_message'] = "Error de conexión a la base de datos";
    header('Location: ../index.php');
    exit;
}

try {
    // Inicializar variables
    $resultados = [];
    $params = [];
    $debugInfo = [];
    
    // Verificar si hay una solicitud de búsqueda
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
        // Construir la consulta base usando la vista que ya tiene los joins
        $sql = "SELECT * FROM vista_equipos_usuarios WHERE 1=1";
        
        // Función para agregar condición de búsqueda de texto
        function addTextSearchCondition(&$sql, &$params, $field, $paramName, $value) {
            if (!empty($value)) {
                $value = trim($value);
                if ($value !== '') {
                    $sql .= " AND LOWER($field) LIKE LOWER(:$paramName)";
                    $params[":$paramName"] = '%' . $value . '%';
                    return true;
                }
            }
            return false;
        }
        
        // Procesar búsqueda por texto para campos comunes
        $textFields = [
            'search_assetname' => 'assetname',
            'search_serial' => 'serial_number',
            'search_cedula' => 'cedula',
            'search_user' => 'last_user',
            'search_job_title' => 'job_title',
            'search_status_change' => 'status_change'
        ];
        
        foreach ($textFields as $paramName => $fieldName) {
            if (isset($_GET[$paramName]) && trim($_GET[$paramName]) !== '') {
                addTextSearchCondition($sql, $params, $fieldName, $paramName, $_GET[$paramName]);
            }
        }
        
        // Procesar búsqueda por fecha de entrada
        if (isset($_GET['search_entry_date']) && !empty($_GET['search_entry_date'])) {
            $entryDate = $_GET['search_entry_date'];
            $sql .= " AND DATE(fecha_ingreso) = :fecha_ingreso";
            $params[':fecha_ingreso'] = $entryDate;
        }
        
        // Procesar búsqueda por fecha de salida
        if (isset($_GET['search_departure_date']) && !empty($_GET['search_departure_date'])) {
            $departureDate = $_GET['search_departure_date'];
            $sql .= " AND DATE(fecha_salida) = :fecha_salida";
            $params[':fecha_salida'] = $departureDate;
        }
        
        // Procesar selecciones múltiples (estado de usuario)
        if (isset($_GET['search_user_status']) && is_array($_GET['search_user_status'])) {
            $filteredStatuses = array_filter($_GET['search_user_status'], function($value) {
                return $value !== '0' && $value !== '';
            });
            
            if (!empty($filteredStatuses)) {
                $statusConditions = [];
                foreach ($filteredStatuses as $index => $status) {
                    $paramName = "status_$index";
                    $statusConditions[] = "user_status = :$paramName";
                    $params[":$paramName"] = $status;
                }
                $sql .= " AND (" . implode(" OR ", $statusConditions) . ")";
            }
        }
        
        // Agregar ordenamiento
        $sql .= " ORDER BY assetname ASC";
        
        // Guardar información de depuración
        $debugInfo = [
            'sql' => $sql,
            'params' => $params
        ];
        
        // Preparar y ejecutar la consulta
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Guardar resultados en la sesión para su uso en Main_Table.php
        $_SESSION['resultados_busqueda'] = $resultados;
        $_SESSION['search_debug_info'] = $debugInfo;
        
        // Redirigir de vuelta a index.php manteniendo la sección de assets
        header('Location: ../index.php#assets');
        exit;
        
    } else {
        // Si no hay parámetros de búsqueda, obtener todos los registros
        $stmt = $pdo->prepare("SELECT * FROM vista_equipos_usuarios ORDER BY assetname ASC");
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['resultados_busqueda'] = $resultados;
        
        // Redirigir de vuelta a index.php manteniendo la sección de assets
        header('Location: ../index.php#assets');
        exit;
    }
} catch (PDOException $e) {
    // Manejar errores de base de datos
    $_SESSION['error_message'] = "Error en la búsqueda: " . $e->getMessage();
    error_log("Error en busqueda_Multicriterio.php: " . $e->getMessage());
    
    // Inicializar resultados vacíos para evitar errores
    $resultados = [];
    $_SESSION['resultados_busqueda'] = [];
    
    // Redirigir de vuelta a index.php con mensaje de error manteniendo la sección de assets
    header('Location: ../index.php#assets');
    exit;
}

// Función para mostrar información de depuración si está habilitada
function showDebugInfo($debugInfo) {
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
        echo '<div class="card mt-3 mb-3">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Información de Depuración</h5>
                </div>
                <div class="card-body">
                    <h6>SQL Query:</h6>
                    <pre>' . htmlspecialchars($debugInfo['sql']) . '</pre>
                    <h6>Parámetros:</h6>
                    <pre>' . print_r($debugInfo['params'], true) . '</pre>
                </div>
              </div>';
    }
}

// Mostrar información de depuración si está habilitada
if (isset($debugInfo) && !empty($debugInfo)) {
    showDebugInfo($debugInfo);
}
?>