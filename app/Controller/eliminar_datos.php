<!--Proceso de eliminacion de registro y en caso de ser necesario mostrar validaciones en pantalla-->

<?php
session_start();

include "../Configuration/Connection.php";

if (isset($_SESSION['asset_data']['assetname'])) {
    $assetname = $_SESSION['asset_data']['assetname'];

    try {
        // Obtener el registro que se va a eliminar de la tabla 'equipos'
        $sqlSelect = "SELECT * FROM vista_equipos_usuarios WHERE assetname = :assetname";
        $stmtSelect = $pdo->prepare($sqlSelect);
        $stmtSelect->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $stmtSelect->execute();
        $record = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        // Guardar el registro en log.txt
        /**
         * Logs the details of a record being deleted to a log file.
         *
         * If the record exists, this function appends the record's details to a log file
         * located at '../Model/Logs/deleting_logIndv.txt'. If the log file does not exist,
         * it will be created.
         *
         * @param array $record The record data to be logged.
         */
        if ($record) {
            $logFile = '../Model/Logs/deleting_logIndv.txt';
            $existingLogData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
            if (!is_array($existingLogData)) {
                $existingLogData = [];
            }
            $existingLogData[] = $record;
            file_put_contents($logFile, json_encode($existingLogData, JSON_PRETTY_PRINT));
        }

        // Eliminar registro de la tabla 'usuarios_equipos'
        $sql1 = "DELETE FROM usuarios_equipos WHERE fk_assetname = :assetname";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $stmt1->execute();

        // Eliminar registro de la tabla 'equipos'
        $sql2 = "DELETE FROM equipos WHERE assetname = :assetname";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $stmt2->execute();

        // Confirmación de eliminación
        if ($stmt1->rowCount() > 0 || $stmt2->rowCount() > 0) {
            $_SESSION['success'] = "Record successfully deleted.";
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['error'] =  "No records found to delete.";
            header("Location: ../index.php");
        }

        // Limpiar la sesión
        unset($_SESSION['asset_data']);
    } catch (PDOException $e) {
        $_SESSION['error'] =  "Error: " . $e->getMessage();
        header("Location: ../index.php");
        exit();
    }
} else {
    echo "No data available in the session.";
}
?>