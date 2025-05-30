<?php
session_start();
include "../Configuration/Connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    try {
        // Obtener los datos del formulario
        $assetname = $_POST['assetname'];
        $last_user = $_POST['last_user'];
        // ... otros campos del formulario ...

        // Actualizar los datos en la base de datos
        $sql = "UPDATE usuarios_equipos SET 
                last_user = :last_user,
                fecha_salida = :fecha_salida
                WHERE fk_assetname = :assetname";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':last_user' => $last_user,
            ':fecha_salida' => $_POST['fecha_salida'],
            ':assetname' => $assetname
        ]);

        if (isset($_POST['generate_pdf'])) {
            $response['success'] = true;
            $response['message'] = 'Datos guardados correctamente';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['success'] = "Datos actualizados correctamente";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } catch (Exception $e) {
        if (isset($_POST['generate_pdf'])) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['error'] = "Error al actualizar los datos: " . $e->getMessage();
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
}
?> 