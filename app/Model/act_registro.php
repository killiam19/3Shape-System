<?php
//COnexion a la base de datos
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
        // Guardar los datos como sesión para mayor seguridad
        session_start();
        $_SESSION['asset_data'] = $asset_data;

        // Redirigir al formulario
        header("Location: ../View/Int_Edicion_equipo.php");
        exit;
    } else {
        //Validacion de errores
        $_SESSION['error'] = "The record was not found.";
    }
} else {
    $_SESSION['error'] = "id not provided";
}
