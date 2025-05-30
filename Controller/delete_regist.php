<?php
include '../Configuration/Connection.php';
session_start();

try {
    // Start a transaction
    $pdo->beginTransaction();

    // Consultar todos los registros antes de eliminarlos
    $query = "SELECT * FROM vista_equipos_usuarios;";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor(); // Close the cursor to free the connection for the next query

    // Guardar todos los registros en un solo archivo de log
    $logFile = '../Model/Logs/deleting_log_' . date('Ymd_His') . '.txt';
    $logData = json_encode($results, JSON_PRETTY_PRINT);
    file_put_contents($logFile, $logData);

    // Eliminar los registros de la tabla 'usuarios_equipos'
    $sql1 = "DELETE FROM usuarios_equipos WHERE fk_assetname IN (SELECT assetname FROM equipos)";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();
    $stmt1->closeCursor(); // Close the cursor to free the connection for the next query

    // Eliminar los registros de la tabla 'equipos'
    $sql2 = "DELETE FROM equipos";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $stmt2->closeCursor(); // Close the cursor to free the connection for the next query

    // Commit the transaction
    $pdo->commit();

    $_SESSION['success'] = "All rows have been deleted.";
    header("Location: ../index.php"); // Redirecciona a la página principal
    exit;
} catch (PDOException $e) {
    // Rollback the transaction in case of error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting rows: " . $e->getMessage();
    header("Location: ../index.php"); // Redirecciona a la página principal
    exit;
}
?>