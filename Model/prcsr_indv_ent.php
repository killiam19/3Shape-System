<?php
include "../Configuration/Connection.php"; // Archivo para traer los ids seleccionados
session_start();

if (isset($_GET['ids']) && is_array($_GET['ids'])) {
    $assetnames = $_GET['ids'];
    $asset_data = []; // Array para almacenar los datos de todos los registros

    foreach ($assetnames as $assetname) {
        $sql = "
        SELECT *
            FROM equipos
            INNER JOIN usuarios_equipos
            ON equipos.assetname = usuarios_equipos.fk_assetname
        WHERE assetname = :assetname
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $asset_data[] = $result; // Agrega el resultado al array
        }
    }

    if (!empty($asset_data)) {
        $_SESSION['asset_data'] = $asset_data; // Guarda todos los datos en la sesión
        header('Location: ../View/firma_act_indv_ent.php'); // Redirige al script del PDF
        exit;
    } else {
        echo 'No logs found for the selected asset names';
    }
}
