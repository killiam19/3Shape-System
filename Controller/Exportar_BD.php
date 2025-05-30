<?php
// Start output buffering to prevent any accidental output
ob_start();
session_start();
include "../Configuration/Connection.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $selectedColumns = $_POST['columns'] ?? [];
    $format = $_POST['format'] ?? 'csv'; // Default to CSV if not specified

    if (empty($selectedColumns)) {
        $_SESSION['error'] = 'No columns selected';
        header('Location: ../Admin/index_admin.php');
        exit();
    }

    $columns = implode(", ", $selectedColumns);
    $sql = "SELECT $columns FROM equipos LEFT JOIN usuarios_equipos ON equipos.assetname = usuarios_equipos.fk_assetname";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica si existen datos
    if ($data) {
        // Determine file extension based on selected format
        $file_extension = ($format === 'txt') ? 'txt' : 'csv';

        // Generacion del nombre del archivo
        $filename = "exportData_" . date('Y-m-d') . "." . $file_extension;

        // Set appropriate content type
        $content_type = ($format === 'txt') ? 'text/plain' : 'text/csv';

        // Encabezados HTTP
        header('Content-Type: ' . $content_type . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Apertura del flujo de salida con manejo de errores
        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new RuntimeException('Unable to open output stream');
        }

        // Get column headers
        $headers = array_keys($data[0]);

        if ($format === 'txt') {
            // Escribir el inicio del array JSON
            fwrite($output, "[");
            
            $totalRows = count($data);
            foreach ($data as $index => $row) {
                // Agregar sangría y formato JSON pretty print
                fwrite($output, ($index === 0 ? "\n" : "") . "    " . json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                
                // Agregar coma y nueva línea si no es el último elemento
                if ($index < $totalRows - 1) {
                    fwrite($output, ",\n");
                }
            }
            
            // Cerrar el array JSON
            fwrite($output, "\n]");
        } else {
            // For CSV format (default)
            fputcsv($output, $headers);

            // Write data rows with default delimiter (comma)
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        // Se cierra el flujo de salida
        fclose($output);

        // Limpiar buffer de salida y terminar ejecución
        ob_end_flush();
        exit();
    } else {
        $_SESSION['error'] = 'No data found';
        header('Location: ../Admin/index_admin.php');
        exit();
    }

} else {
    $_SESSION['error'] = 'Invalid request';
    header('Location: ../Admin/index_admin.php');
    exit();
}
