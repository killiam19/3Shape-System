<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "CSRF token validation failed";
        header('Location: index_admin.php');
        exit();
    }

    // Validar existencia de datos
    if (empty($_POST['columns']) || empty($_POST['Log_name'])) {
        $_SESSION['error'] = "Faltan campos requeridos";
        header('Location: ../index.php');
        exit();
    }

    $selected = trim($_POST['columns'][0]); // Limpiar espacios
    $log_name = trim($_POST['Log_name']); // Limpiar espacios
    $prohibited_fields = [
        'assetname',
        'serial_number',
        'warranty_enddate',
        'expired',
        'new_laptop',
        'fk_id',
        'fk_assetname',
        'last_user',
        'job_title',
        'cedula',
    ];

    // Validar formato de selección
    if (substr_count($selected, '|') !== 1) {
        $_SESSION['error'] = "Invalid selection format. Expected 'table|column'";
        header('Location: index_admin.php');
        exit();
    }

    list($table, $column) = explode('|', $selected);
    $table = trim($table); // Limpiar espacios
    $column = trim($column); // Limpiar espacios

    // Validaciones múltiples
    $allowed_tables = ['equipos', 'usuarios_equipos'];
    $errors = [];

    if (!in_array($table, $allowed_tables)) {
        $errors[] = "Invalid table: '$table'. tables allowed: " . implode(', ', $allowed_tables);
    }
    if (!preg_match('/^[a-zA-Z0-9_ ]+$/', $log_name)) {
        $errors[] = "Invalid name log: '$log_name'. mya only allowed letters, númbers, udersocres and spaces.";
    }
    if (in_array($column, $prohibited_fields)) {
        $errors[] = "Invalid Column: '$column'.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode(' ', $errors);
        header('Location: index_admin.php');
        exit();
    }

    try {
        include '../Configuration/Connection.php';

        // Verificar si la columna existe en la tabla
        $check_sql = "SHOW COLUMNS FROM `$table` LIKE ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$column]);

        if ($stmt->rowCount() === 0) {
            throw new PDOException("The column '$column' does not exist in the table '$table'.");
        }

        // Insertar un nuevo registro
        $pdo->beginTransaction();
        $assetname = substr(uniqid('CO-LPT-'), 0, 10);
        if ($table === 'usuarios_equipos') {
            // First, insert into equipos table
            $sql = "INSERT INTO `equipos` (`assetname`) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$assetname]);

            // Then, insert into usuarios_equipos table
            $sql = "INSERT INTO `$table` (`$column`, `fk_assetname`) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$log_name, $assetname]);

        } else {
            $sql = "INSERT INTO `$table` (`$column`, `assetname`) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$log_name, $assetname]);
        }
        $pdo->commit();

        // Validacion de errores
        $_SESSION['success'] = "added successfully to the '$table' table.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    } finally {
        header('Location: index_admin.php');
        exit();
    }
}
