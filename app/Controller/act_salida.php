<?php
/**
 * Asset Check-Out Controller
 * 
 * Handles the check-out of assets in the system. Processes form submissions,
 * updates asset status to 'Stock', and records check-out dates.
 * 
 * Features:
 * - Validates input data
 * - Updates user status and check-out date
 * - Manages session messages for success/error
 * 
 * Database Tables:
 * - usuarios_equipos: Updates user status and check-out date
 * 
 * @package Controller
 */

session_start();
include "../Configuration/Connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assetname = $_POST['equipo'];
    $fecha_salida = $_POST['fechaSalida'];

    if (!empty($assetname)) {
        try {
            // Update both user_status and fecha_salida in a single query
            $sql = "UPDATE usuarios_equipos 
                    SET user_status = 'Stock', 
                        fecha_salida = :fecha_salida 
                    WHERE fk_assetname = :assetname";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_salida', $fecha_salida, PDO::PARAM_STR);

            if ($stmt->execute()) {
                header('Location: ../../index.php');
                $_SESSION['success'] = "process complete";
            } else {
                header('Location: ../../index.php');
                $_SESSION['success'] = "process complete";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        header('Location: ../../index.php');
        $_SESSION['error'] = "Please fill in all fields";
    }
} else {
    header('Location: ../../index.php');
    $_SESSION['error'] = "Invalid request method";
}
