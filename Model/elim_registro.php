<?php

include "../Configuration/Connection.php";
if (isset($_GET['assetname'])) {
    $assetname = $_GET['assetname'];
    
      // Consulta usando JOIN para obtener datos de ambas tablas
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

    $asset_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asset_data) {
        session_start();
        // guarda los datos de la sesion para mayor seguridad
        $_SESSION['asset_data'] = $asset_data;
        header('location: ../Controller/eliminar_datos.php');
        exit;
        
    } else {
        $_SESSION['error'] = 'The record was not found';
    }

} else {
    $_SESSION['error'] = "id not provided";
}


