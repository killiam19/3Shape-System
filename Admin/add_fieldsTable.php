<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $fields = $_POST['addmfields'];
    
    // Validación básica de campos
    if (empty($fields)) {
        $_SESSION['error'] = 'No fields selected';
        header('Location: index_admin.php');
        exit();
    }

    if (!is_array($fields)) {
        $_SESSION['error'] = 'Fields should be an array';
        header('Location: index_admin.php');
        exit();
    }
    
    // Lista de campos prohibidos (no se pueden añadir nunca)
    $prohibitedFields = ['assetname', 'serial_number', 'purchase_country','warranty_enddate','expired','fk_id','fk_assetname','user_status',
    'last_user','job_title','status_change','cedula'];
    
    // Obtener campos actualmente visibles en la tabla principal (si existen)
    $currentlyVisibleFields = isset($_SESSION['selected_fields']) ? $_SESSION['selected_fields'] : [];

    // Validar cada campo seleccionado
    foreach ($fields as $field) {
        // 1. Validar campos prohibidos
        if (in_array($field, $prohibitedFields)) {
            $_SESSION['error'] = 'Field ' . htmlspecialchars($field) . ' is not allowed to be added';
            header('Location: index_admin.php');
            exit();
        }
        
        // 2. Validar si el campo YA está siendo mostrado en la tabla principal
        if (in_array($field, $currentlyVisibleFields)) {
            $_SESSION['error'] = 'Field "' . htmlspecialchars($field) . '" is already visible in the main table. Please remove it first if you want to add it again.';
            header('Location: index_admin.php');
            exit();
        }
    }
    
    // Obtener los tipos de datos de las columnas seleccionadas
    $fieldTypes = [];
    include '../Configuration/Connection.php'; // Conexión a la base de datos
    
    foreach ($fields as $field) {
        $sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE COLUMN_NAME = :field 
                AND TABLE_NAME IN ('equipos', 'usuarios_equipos')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['field' => $field]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $fieldTypes[$field] = $row['DATA_TYPE'];
        } else {
            $_SESSION['error'] = 'Field type not found for ' . htmlspecialchars($field);
            header('Location: index_admin.php');
            exit();
        }
    }

    // Guardar los campos seleccionados en la sesión (ahora serán visibles)
    $_SESSION['selected_fields'] = $fields;
    $_SESSION['selected_field_types'] = $fieldTypes;

    // Mostrar un mensaje de éxito
    $_SESSION['success'] = 'Fields added to the table successfully';
    header('Location: ../index.php');
    exit();
}
?>