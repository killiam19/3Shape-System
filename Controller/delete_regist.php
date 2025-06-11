<?php
include '../Configuration/Connection.php';
session_start();

// Asegurarnos de que no haya salida antes del header
ob_start();

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

try {
    // Start a transaction
    $pdo->beginTransaction();

    // Consultar todos los registros antes de eliminarlos
    $query = "SELECT * FROM vista_equipos_usuarios;";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Guardar todos los registros en un archivo de log
    $logFile = '../Model/Logs/deleting_log_' . date('Ymd_His') . '.txt';
    $logData = json_encode($results, JSON_PRETTY_PRINT);
    file_put_contents($logFile, $logData);

    // Eliminar los registros de la tabla 'usuarios_equipos'
    $sql1 = "DELETE FROM usuarios_equipos WHERE fk_assetname IN (SELECT assetname FROM equipos)";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();
    $stmt1->closeCursor();

    // Eliminar los registros de la tabla 'equipos'
    $sql2 = "DELETE FROM equipos";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $stmt2->closeCursor();

    // Commit the transaction
    $pdo->commit();

    // Limpiar cualquier salida anterior
    ob_clean();
    
    // Enviar respuesta de éxito
    echo json_encode([
        'status' => 'success',
        'message' => 'Todos los registros han sido eliminados exitosamente.'
    ]);
} catch (PDOException $e) {
    // Rollback the transaction in case of error
    $pdo->rollBack();
    
    // Limpiar cualquier salida anterior
    ob_clean();
    
    // Enviar respuesta de error
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al eliminar los registros: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Limpiar cualquier salida anterior
    ob_clean();
    
    // Enviar respuesta de error genérico
    echo json_encode([
        'status' => 'error',
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
}

// Asegurarnos de que la salida se envíe
ob_end_flush();
?>