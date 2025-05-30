<?php
session_start();
include '../Configuration/Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $fields = $_POST['removefields'] ?? [];

    if (!empty($fields)) {
        try {
            foreach ($fields as $field) {
                // Validar el nombre de la columna
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                    $_SESSION['error'] = 'Invalid column name format: ' . htmlspecialchars($field);
                    header('Location: index_admin.php');
                    exit();
                }
            }

            // Guardar datos antes de eliminar la columna
            function backupColumnData($pdo, $table, $column) {
                $sql = "SELECT `$column` FROM `$table`";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($data)) {
                    $logFile = '../Model/Logs/deleting_columns.txt'; // Ruta del archivo de log
                
                    // Verificar si el directorio existe, si no, crearlo
                    if (!file_exists(dirname($logFile))) {
                        mkdir(dirname($logFile), 0777, true);
                    }
                
                    // Verificar si el archivo ya existe y tiene contenido
                    if (file_exists($logFile)) {
                        $logData = json_decode(file_get_contents($logFile), true);
                    } else {
                        $logData = [];
                    }
                
                    // Agregar nuevos datos al log
                    $logData[] = [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'table' => $table,
                        'column' => $column,
                        'data' => $data
                    ];
                
                    // Guardar en el archivo JSON
                    file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT));
                }
   
            }
            // Tablas donde se buscará y eliminará la columna
            $tables = ['equipos', 'usuarios_equipos'];

            foreach ($fields as $field) {
                foreach ($tables as $table) {
                    // Verificar si la columna existe en la tabla
                    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table AND COLUMN_NAME = :field";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['table' => $table, 'field' => $field]);
                    $exists = $stmt->fetchColumn();

                    if ($exists) {
                        // Guardar los datos antes de eliminar la columna
                        backupColumnData($pdo, $table, $field);

                        // Eliminar la columna
                        $sql = "ALTER TABLE `$table` DROP COLUMN `$field`";
                        $pdo->exec($sql);
                    }
                }
            }

            $_SESSION['success'] = 'Field removed successfully and logged.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error removing field: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Invalid field selected';
    }

    header('Location: index_admin.php');
    exit();
}
?>
