<?php
/**
 * Asset Check-In Controller
 * 
 * Handles the check-in/entry of assets in the system. Processes form submissions,
 * validates input data, and manages asset status changes between 'Active' and 'Stock'.
 * 
 * Features:
 * - Validates input data and checks for duplicates
 * - Handles asset data swapping between statuses
 * - Updates equipment and user status records
 * - Uses database transactions for data integrity
 * 
 * Database Tables/Views:
 * - equipos: Stores asset technical details
 * - usuarios_equipos: Stores user-related asset information
 * - vista_equipos_usuarios: View for asset-user relationships
 * 
 * @package Controller
 */

session_start();
include "../Configuration/Connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Capturar datos enviados desde el formulario
        $serial_number = $_POST['serial_number'];
        $assetname = $_POST['assetname'];

        // Validar que los campos no estén vacíos
        if (empty($assetname)) {
            throw new Exception("All field are required.");

        }

        // Iniciar una transacción
        $pdo->beginTransaction();

        // Comprobar si el serial_number ya existe en otro assetname
        $checkSql = "SELECT e.assetname FROM equipos e WHERE e.serial_number = :serial_number AND e.assetname != :assetname";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':serial_number', $serial_number, PDO::PARAM_STR);
        $checkStmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $checkStmt->execute();
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            try {
                // Recuperar todos los datos del equipo seleccionado
                $currentDataSql = "SELECT e.serial_number, e.purchase_country, e.warranty_enddate, e.expired, e.new_laptop 
                                   FROM equipos AS e 
                                   WHERE e.assetname = :assetname";
                $currentDataStmt = $pdo->prepare($currentDataSql);
                $currentDataStmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
                $currentDataStmt->execute();
                $currentData = $currentDataStmt->fetch(PDO::FETCH_ASSOC);

                if (!$currentData) {
                    throw new Exception("No se pudieron recuperar los datos del equipo seleccionado.");
                }

                // Buscar equipo en "Stock"
                $stockCheckSql = "SELECT * FROM vista_equipos_usuarios
                                  WHERE serial_number = :serial_number";
                $stockCheckStmt = $pdo->prepare($stockCheckSql);
                $stockCheckStmt->bindParam(':serial_number', $serial_number, PDO::PARAM_STR);
                $stockCheckStmt->execute();
                $stockRecord = $stockCheckStmt->fetch(PDO::FETCH_ASSOC);

                if (!$stockRecord) {
                    throw new Exception("No se encontró un equipo en estado 'Stock' para realizar el intercambio.");
                }

                $stockAssetname = $stockRecord['assetname'];

                // Función para actualizar información de equipos
                function updateEquipment($pdo, $data, $assetname)
                {
                    $updateSql = "UPDATE equipos AS e 
                                  SET e.serial_number = :serial_number, 
                                    e.purchase_country = :purchase_country, 
                                    e.warranty_enddate = :warranty_enddate, 
                                    e.expired = :expired, 
                                    e.new_laptop = :new_laptop 
                                  WHERE e.assetname = :assetname";
                    $stmt = $pdo->prepare($updateSql);
                    $stmt->bindParam(':serial_number', $data['serial_number'], PDO::PARAM_STR);
                    $stmt->bindParam(':purchase_country', $data['purchase_country'], PDO::PARAM_STR);
                    $stmt->bindParam(':warranty_enddate', $data['warranty_enddate'], PDO::PARAM_STR);
                    $stmt->bindParam(':expired', $data['expired'], PDO::PARAM_STR);
                    $stmt->bindParam(':new_laptop', $data['new_laptop'], PDO::PARAM_STR);
                    $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
                    return $stmt->execute();
                }

                //Actualizar equipos seleccionados con datos de stock
                updateEquipment($pdo, $stockRecord, $assetname);

                // Actualizar el stock de equipos con los datos seleccionados
                updateEquipment($pdo, $currentData, $stockAssetname);

                // Función para actualizar el estado del usuario
                function updateUserStatus($pdo, $assetname, $status)
                {
                    $updateStatusSql = "UPDATE usuarios_equipos AS ue 
                                        SET ue.user_status = :user_status 
                                        WHERE ue.fk_assetname = :assetname";
                    $stmt = $pdo->prepare($updateStatusSql);
                    $stmt->bindParam(':user_status', $status, PDO::PARAM_STR);
                    $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
                    return $stmt->execute();
                }

                // Update user statuses
                updateUserStatus($pdo, $assetname, 'Active User');
                updateUserStatus($pdo, $stockAssetname, 'Stock');


            } catch (Exception $e) {
                throw $e;
            }
        }

        // Actualizar el número de serie asociado al assetname
        $updateSql = "UPDATE equipos AS e 
        SET e.serial_number = :serial_number 
        WHERE e.assetname = :assetname";

        $stmt = $pdo->prepare($updateSql);
        $stmt->bindParam(':serial_number', $serial_number, PDO::PARAM_STR);
        $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);

        // Ejecutar la consulta de actualización
        if ($stmt->execute()) {
            // Confirmar la transacción
            $_SESSION['success'] = 'Asset updated successfully';
            $pdo->commit();
            header('Location: ../index.php');
        } else {
            throw new Exception("Error by registering the serial number.");
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // Duplicate entry error code
            // Log the error or ignore it
        } else {
            $pdo->rollBack();
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        header('Location: ../index.php');
    }
} else {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    header('Location: ../index.php');
}
