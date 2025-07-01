<?php
session_start();
include '../Configuration/Connection.php';
// Toma los datos del modal 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // // Desactiva las restricciones de las llaves foraneas para permitir la eliminación de filas con dependencias
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['delete']) && $input['delete'] === true) {
        try {
            $pdo->beginTransaction();
            // Desactiva las restriccciones de las llaves foraneas
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

            // Get repeated serials
            //Toma seriales repetidos
            $sql = "SELECT serial_number FROM equipos GROUP BY serial_number HAVING COUNT(*) > 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $repeatedSerials = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($repeatedSerials)) {
                foreach ($repeatedSerials as $serial) {
                    // Delete repeated rows in usuarios_equipos except those with 'Active_user'
                    // Elimina filas repetidas en la tabla usuarios_equipos
                    // excepto las que tienen 'Active_user' en la columna user status
                    $sql = "DELETE ue1 FROM usuarios_equipos ue1
                            INNER JOIN usuarios_equipos ue2 
                            ON ue1.fk_assetname = ue2.fk_assetname 
                            AND ue1.user_status = 'Old User'
                            AND ue2.user_status = 'Active User'
                            AND ue1.fk_assetname IN (SELECT assetname FROM equipos WHERE serial_number = ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$serial]);

                    // Delete repeated rows in equipos except those with 'Active_user' in usuarios_equipos
                    // elimina filas in la tabla equipos exepto donde son usuarios Actibos en la tabla usuarios equipos  
                    $sql = "DELETE e FROM equipos e
                            WHERE e.serial_number = ?
                            AND EXISTS (
                            SELECT 1 FROM usuarios_equipos ue 
                            WHERE ue.fk_assetname = e.assetname
                            AND ue.user_status = 'Old User'
                            AND NOT EXISTS (
                            SELECT 1 FROM usuarios_equipos ue_active 
                            WHERE ue_active.fk_assetname = ue.fk_assetname
                            AND ue_active.user_status = 'Active User'
                                )
                            )";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$serial]);
                }
            }

            // Enable foreign key checks
            // habilita la validacion de laas llaves foraneas
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            $pdo->rollBack();

            // Re-enable foreign key checks in case of an error
            // Vuelve a a habilitar las restrcciones de las llaves foraneas
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'error' => 'Cannot delete or update a parent row: a foreign key constraint fails.']);
            } else {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
}
?>
