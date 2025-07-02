<?php
// Este archivo debe colocarse en la raíz del proyecto
// Acceder a él directamente desde el navegador para ejecutar diagnósticos

// Iniciar sesión
session_start();

// Función para mostrar resultados formateados
function show_result($title, $result, $is_error = false) {
    $class = $is_error ? 'danger' : 'success';
    $icon = $is_error ? 'x-circle' : 'check-circle';
    
    echo "<div class='card mb-3'>
            <div class='card-header bg-$class text-white'>
                <h5 class='mb-0'><i class='bi bi-$icon me-2'></i>$title</h5>
            </div>
            <div class='card-body'>
                <pre>" . htmlspecialchars(print_r($result, true)) . "</pre>
            </div>
          </div>";
}

// Cabecera HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema</title>
    <link href="./app/Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./app/Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        body { padding: 20px; }
        .diagnostic-header { margin-bottom: 30px; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="diagnostic-header text-center">
            <h1><i class="bi bi-tools me-2"></i>Diagnóstico del Sistema</h1>
            <p class="lead">Esta herramienta verifica la configuración y conexión de la base de datos</p>
        </div>';

// Verificar si el archivo de conexión existe
if (file_exists('./app/Configuration/Connection.php')) {
    show_result('Archivo de conexión', 'El archivo Connection.php existe');
    
    // Intentar incluir el archivo de conexión
    try {
        include_once './app/Configuration/Connection.php';
        show_result('Inclusión del archivo de conexión', 'El archivo Connection.php se incluyó correctamente');
    } catch (Exception $e) {
        show_result('Error al incluir el archivo de conexión', $e->getMessage(), true);
    }
    
    // Verificar si la variable $pdo está definida
    if (isset($pdo)) {
        show_result('Variable de conexión', 'La variable $pdo está definida');
        
        // Verificar la conexión a la base de datos
        try {
            $pdo->query('SELECT 1');
            show_result('Conexión a la base de datos', 'La conexión a la base de datos funciona correctamente');
            
            // Verificar la existencia de las tablas
            $tables = ['equipos', 'usuarios_equipos', 'vista_equipos_usuarios'];
            $missing_tables = [];
            
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE :table");
                    $stmt->execute(['table' => $table]);
                    $result = $stmt->fetch();
                    
                    if (!$result) {
                        $missing_tables[] = $table;
                    }
                } catch (Exception $e) {
                    $missing_tables[] = "$table (Error: " . $e->getMessage() . ")";
                }
            }
            
            if (empty($missing_tables)) {
                show_result('Tablas de la base de datos', 'Todas las tablas necesarias existen');
                
                // Verificar la estructura de las tablas
                try {
                    $table_info = [];
                    
                    foreach ($tables as $table) {
                        if ($table === 'vista_equipos_usuarios') {
                            // Para vistas, verificamos si existe
                            $stmt = $pdo->prepare("SHOW CREATE VIEW $table");
                            $stmt->execute();
                            $view_def = $stmt->fetch(PDO::FETCH_ASSOC);
                            $table_info[$table] = isset($view_def['Create View']) ? 'Vista existente' : 'Vista no encontrada';
                        } else {
                            // Para tablas, obtenemos la estructura
                            $stmt = $pdo->prepare("DESCRIBE $table");
                            $stmt->execute();
                            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $table_info[$table] = $columns;
                        }
                    }
                    
                    show_result('Estructura de las tablas', $table_info);
                    
                    // Verificar datos de muestra
                    $sample_data = [];
                    
                    foreach ($tables as $table) {
                        if ($table !== 'vista_equipos_usuarios') { // Excluimos la vista
                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
                            $stmt->execute();
                            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            if ($count > 0) {
                                $stmt = $pdo->prepare("SELECT * FROM $table LIMIT 1");
                                $stmt->execute();
                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $sample_data[$table] = [
                                    'count' => $count,
                                    'sample' => $row
                                ];
                            } else {
                                $sample_data[$table] = [
                                    'count' => 0,
                                    'sample' => 'No hay datos'
                                ];
                            }
                        }
                    }
                    
                    show_result('Datos de muestra', $sample_data);
                    
                    // Probar la consulta de búsqueda
                    $sql = "SELECT e.*, u.* 
                            FROM equipos e 
                            LEFT JOIN usuarios_equipos u ON e.assetname = u.fk_assetname 
                            LIMIT 5";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($resultados)) {
                        show_result('Consulta de prueba', [
                            'query' => $sql,
                            'results' => $resultados
                        ]);
                    } else {
                        show_result('Consulta de prueba', [
                            'query' => $sql,
                            'results' => 'No se encontraron resultados'
                        ], true);
                    }
                    
                } catch (Exception $e) {
                    show_result('Error al verificar la estructura de las tablas', $e->getMessage(), true);
                }
            } else {
                show_result('Tablas faltantes', $missing_tables, true);
            }
        } catch (Exception $e) {
            show_result('Error de conexión a la base de datos', $e->getMessage(), true);
        }
    } else {
        show_result('Variable de conexión', 'La variable $pdo no está definida', true);
    }
} else {
    show_result('Archivo de conexión', 'El archivo Connection.php no existe', true);
}

// Verificar la configuración de PHP
$php_info = [
    'version' => phpversion(),
    'pdo_mysql' => extension_loaded('pdo_mysql') ? 'Instalado' : 'No instalado',
    'session' => extension_loaded('session') ? 'Instalado' : 'No instalado',
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => ini_get('error_reporting'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit')
];

show_result('Configuración de PHP', $php_info);

// Verificar la configuración de sesiones
$session_info = [
    'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva',
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_save_path' => session_save_path(),
    'session_cookie_params' => session_get_cookie_params()
];

show_result('Configuración de sesiones', $session_info);

// Pie de página HTML
echo '
        <div class="text-center mt-4">
            <a href="./" class="btn btn-primary">Volver al inicio</a>
        </div>
    </div>
    <script src="./Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?>
