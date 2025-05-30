<?php
// Asegurarse de que no haya salida antes de los headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Evita que los errores PHP se mezclen con el JSON

if (isset($_GET['serial'])) {
    $serial = $_GET['serial'];

    try {
        include '../Configuration/Connection.php'; // incluye el archivo de conexion
        global $pdo; // Usar la instancia existente

        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM equipos WHERE UPPER(TRIM(serial_number)) = UPPER(TRIM(:serial))");

        $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "exists" => $row['count'] > 0,
            "debug_count" => $row['count'],
            "input_serial" => $serial
        ]);
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        echo json_encode(["error" => "Error de conexión a la base de datos"]);
    }
} else {
    echo json_encode(["error" => "No serial provided"]);
}
// Asegurarse de que no haya salida después del JSON
exit;
