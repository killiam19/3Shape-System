<?php
session_start();

include '../Configuration/Connection.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    // Recuperar datos del formulario
    $dataType = $_POST['data_type'] ?? '';
    $fieldName = $_POST['field_name'] ?? '';
    $tableName = $_POST['table_name'] ?? '';

    // Validar los datos recibidos
    if (empty($dataType) || empty($fieldName) || empty($tableName)) {
        throw new Exception("All fields are required.");
    }
    
    // Asegurar que el nombre del campo sea válido (alfanumérico y sin espacios)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $fieldName)) {
        throw new Exception("Invalid field name.");
    }
    
    // Construir y ejecutar la consulta para agregar la columna
    $query = $pdo->prepare("ALTER TABLE `$tableName` ADD `$fieldName` $dataType");
    $query->execute();

    // Redirigir a la página de inicio con un mensaje de éxito
    $_SESSION['success'] = "Field '$fieldName' added successfully to table '$tableName'.";
    header('Location: ../index.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('Location: ../index.php');
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: ../index.php');
    exit();
}
?>