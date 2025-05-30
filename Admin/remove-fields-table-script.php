<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'CSRF token validation failed';
        header('Location: index_admin.php');
        exit();
    }

    // 2. Validar campos seleccionados
    if (empty($_POST['removemfields'])) {
        $_SESSION['error'] = 'No fields selected for hiding';
        header('Location: index_admin.php');
        exit();
    }

    $fieldsToHide = $_POST['removemfields'];
    
    // 3. Validar que los campos seleccionados son válidos
    include '../Configuration/Connection.php';
    
    // Lista de campos prohibidos (que nunca se pueden ocultar)
    $prohibitedFields = ['assetname', 'serial_number', 'purchase_country','warranty_enddate','expired','fk_id','fk_assetname','user_status',
    'last_user','job_title','status_change','cedula'];
    
    // Verificar que ningún campo seleccionado esté en la lista prohibida
    $invalidFields = array_intersect($fieldsToHide, $prohibitedFields);
    if (!empty($invalidFields)) {
        $_SESSION['error'] = 'Cannot hide protected fields: ' . implode(', ', $invalidFields);
        header('Location: index_admin.php');
        exit();
    }
    
    // 4. Verificar que los campos existen en la base de datos
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
            AND COLUMN_NAME IN (" . str_repeat('?,', count($fieldsToHide) - 1) . "?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($fieldsToHide);
    $validFields = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $invalidFields = array_diff($fieldsToHide, $validFields);
    if (!empty($invalidFields)) {
        $_SESSION['error'] = 'Invalid fields selected: ' . implode(', ', $invalidFields);
        header('Location: index_admin.php');
        exit();
    }

    // 5. Actualizar la sesión con los campos ocultos
    if (!isset($_SESSION['hidden_fields'])) {
        $_SESSION['hidden_fields'] = [];
    }
    
    // Agregar los nuevos campos a la lista de ocultos (evitar duplicados)
    $_SESSION['hidden_fields'] = array_unique(array_merge($_SESSION['hidden_fields'], $fieldsToHide));
    
    // 6. Si existe selected_fields, remover los campos ocultos de ahí
    if (isset($_SESSION['selected_fields'])) {
        $_SESSION['selected_fields'] = array_diff($_SESSION['selected_fields'], $fieldsToHide);
    }
    
    // 7. Registrar en el log
    $logMessage = 'Fields hidden from table view: ' . implode(', ', $fieldsToHide);
    $logFile = '../Model/Logs/session_messages.json';
    
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true);
    } else {
        $logs = ['success' => [], 'error' => []];
    }
    
    $logs['success'][] = [
        'message' => $logMessage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
    
    // 8. Mensaje de éxito
    $_SESSION['success'] = 'Fields hidden successfully from table view: ' . implode(', ', $fieldsToHide);
    header('Location: index_admin.php');
    exit();
}
?>