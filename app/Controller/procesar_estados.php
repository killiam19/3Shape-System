<?php
session_start();

include "../Configuration/Connection.php";
include "../Controller/sanitization.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar una transacción
        $pdo->beginTransaction();
        
        // Obtener el serial number (identificador principal)
        $serial_number = sanitize_input($_POST['serial'] ?? null);
        
        if (!$serial_number) {
            throw new Exception("Serial number is required");
        }
        
        // Obtener el nombre de usuario (último usuario)
        $nombre_usuario = sanitize_input($_POST['nombre_usuario'] ?? null);
        
        // Arrays para almacenar campos y valores
        $equipos_updates = [];
        $equipos_params = [];
        $usuarios_equipos_updates = [];
        $usuarios_equipos_params = [];
        
        // Procesar campos de la tabla equipos
        $equipos_fields = [
            // Campo => [nombre_post, validación_requerida]
            'asset_status' => ['asset_status', false],
            'asset_observations' => ['asset_observations', false],
            'HeadSet' => ['HeadSet', false],
            'headset_status' => ['headset_status', false],
            'headset_observations' => ['headset_observations', false],
            'Dongle' => ['Dongle', false],
            'dongle_status' => ['dongle_status', false],
            'dongle_observations' => ['dongle_observations', false],
            'Celular' => ['Celular', false],
            'celular_status' => ['celular_status', false],
            'celular_observations' => ['celular_observations', false]
        ];
        
        foreach ($equipos_fields as $db_field => $config) {
            $post_field = $config[0];
            $is_required = $config[1];
            
            // Obtener y sanitizar el valor
            $value = sanitize_input($_POST[$post_field] ?? null);
            
            // Verificar si es requerido
            if ($is_required && empty($value)) {
                throw new Exception("El campo $post_field es requerido");
            }
            
            // Si existe el valor, agregarlo a la actualización
            if ($value !== null) {
                $equipos_updates[] = "$db_field = :$db_field";
                $equipos_params[$db_field] = $value;
            }
        }
        
        // Procesar campos de la tabla usuarios_equipos
        $usuarios_equipos_fields = [
            // Campo => [nombre_post, validación_requerida]
            'last_user' => ['nombre_usuario', false]
        ];
        
        foreach ($usuarios_equipos_fields as $db_field => $config) {
            $post_field = $config[0];
            $is_required = $config[1];
            
            // Obtener y sanitizar el valor
            $value = sanitize_input($_POST[$post_field] ?? null);
            
            // Verificar si es requerido
            if ($is_required && empty($value)) {
                throw new Exception("El campo $post_field es requerido");
            }
            
            // Si existe el valor, agregarlo a la actualización
            if ($value !== null) {
                $usuarios_equipos_updates[] = "$db_field = :$db_field";
                $usuarios_equipos_params[$db_field] = $value;
            }
        }
        
        // Manejar eliminación de foto si fue solicitada
        if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
            // Primero obtener el nombre del archivo actual para eliminarlo del servidor
            $stmt_get_photo = $pdo->prepare("SELECT asset_photo FROM equipos WHERE serial_number = :serial_number");
            $stmt_get_photo->bindValue(":serial_number", $serial_number);
            $stmt_get_photo->execute();
            $current_photo = $stmt_get_photo->fetchColumn();
            
            if ($current_photo) {
                $file_path = '../uploads/' . $current_photo;
                if (file_exists($file_path)) {
                    unlink($file_path); // Eliminar el archivo físico
                }
            }
            
            // Agregar actualización para eliminar la referencia en la base de datos
            $equipos_updates[] = "asset_photo = NULL";
        }
        
        // Subir nueva foto si fue enviada
        $asset_photo = null;
        if (isset($_FILES['asset_photo']) && $_FILES['asset_photo']['error'] === UPLOAD_ERR_OK) {
            // Primero eliminar la foto anterior si existe
            $stmt_get_photo = $pdo->prepare("SELECT asset_photo FROM equipos WHERE serial_number = :serial_number");
            $stmt_get_photo->bindValue(":serial_number", $serial_number);
            $stmt_get_photo->execute();
            $current_photo = $stmt_get_photo->fetchColumn();
            
            if ($current_photo) {
                $file_path = '../uploads/' . $current_photo;
                if (file_exists($file_path)) {
                    unlink($file_path); // Eliminar el archivo físico
                }
            }
            
            $targetDir = '../uploads/';
            $asset_photo = basename($_FILES['asset_photo']['name']);
            // Generar un nombre único para evitar colisiones
            $fileExtension = pathinfo($asset_photo, PATHINFO_EXTENSION);
            $asset_photo = 'asset_' . $serial_number . '_' . time() . '.' . $fileExtension;
            $targetFilePath = $targetDir . $asset_photo;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            // Comprobaciones necesarias (tipo de archivo, tamaño, etc.)
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['asset_photo']['tmp_name'], $targetFilePath)) {
                    // Foto subida exitosamente
                    $equipos_updates[] = "asset_photo = :asset_photo";
                    $equipos_params['asset_photo'] = $asset_photo;
                } else {
                    throw new Exception('Error moving the uploaded file. Please check the upload directory permissions.');
                }
            } else {
                throw new Exception('Invalid file type. Only JPG, PNG, and GIF files are allowed.');
            }
        }

        // Actualizar la tabla equipos si hay cambios
        if (!empty($equipos_updates)) {
            $sql_update_equipos = "UPDATE equipos SET " . implode(", ", $equipos_updates) . " WHERE serial_number = :serial_number";
            $stmt_equipos = $pdo->prepare($sql_update_equipos);
            
            // Vincular parámetros
            foreach ($equipos_params as $param => $value) {
                $stmt_equipos->bindValue(":$param", $value);
            }
            $stmt_equipos->bindValue(":serial_number", $serial_number);
            
            // Ejecutar la consulta
            $stmt_equipos->execute();
        }
        
        // Actualizar la tabla usuarios_equipos si hay cambios
        if (!empty($usuarios_equipos_updates)) {
            // Obtener el asset_name correspondiente al serial_number
            $stmt_get_assetname = $pdo->prepare("SELECT assetname FROM equipos WHERE serial_number = :serial_number");
            $stmt_get_assetname->bindValue(":serial_number", $serial_number);
            $stmt_get_assetname->execute();
            $asset_name = $stmt_get_assetname->fetchColumn();
            
            if (!$asset_name) {
                throw new Exception("No se encontró el asset_name para este serial");
            }
            
            $sql_update_usuarios_equipos = "UPDATE usuarios_equipos SET " . implode(", ", $usuarios_equipos_updates) . " WHERE fk_assetname = :asset_name";
            $stmt_usuarios_equipos = $pdo->prepare($sql_update_usuarios_equipos);
            
            // Vincular parámetros
            foreach ($usuarios_equipos_params as $param => $value) {
                $stmt_usuarios_equipos->bindValue(":$param", $value);
            }
            $stmt_usuarios_equipos->bindValue(":asset_name", $asset_name);
            
            // Ejecutar la consulta
            $stmt_usuarios_equipos->execute();
        }
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Mensaje de éxito
        $_SESSION["success"] = "Asset statuses updated successfully.";
        
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        $_SESSION["error"] = "Error updating statuses: " . $e->getMessage();
    }
    
    // Redirigir al usuario
    header("Location: ../../index.php");
    exit();
} else {
    $_SESSION["error"] = "Método de acceso no válido";
    header("Location: ../../index.php");
    exit();
}