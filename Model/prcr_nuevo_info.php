<?php
include "../Configuration/Connection.php"; // Archivo para traer los ids seleccionados
session_start();

if (isset($_GET['ids']) && is_array($_GET['ids'])) {
    $assetnames = $_GET['ids'];
    $asset_data = []; // Array para almacenar los datos de todos los registros
    $additional_data = []; // Array para almacenar datos adicionales si es necesario

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
            $additional_data[] = $result; // Agrega datos adicionales al array
        }
    }

    if (!empty($asset_data)) {
        $_SESSION['asset_data'] = $asset_data; // Guarda todos los datos en la sesión
        $_SESSION['additional_data'] = $additional_data; // Guarda datos adicionales en la sesión

        header('Location: ../Controller/information.php'); // Redirige al script del PDF
        exit;
    } else {
        echo 'No se encontraron registros para los IDs seleccionados.';
    }
}
