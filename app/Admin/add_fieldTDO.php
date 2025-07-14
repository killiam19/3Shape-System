<?php
session_start();

// Tiempo de inactividad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $fields = $_POST['columns'];
    // Lista de campos prohibidos
    $prohibited_fields = ['assetname', 'serial_number', 'purchase_country','warranty_enddate','expired','new_laptop','fk_id','fk_assetname','user_status',
    'last_user','job_title','status_change','cedula','Carnet','LLave','Tipo_ID']; // Añade aquí los campos que quieres prohibir

    // Validar si los campos seleccionados son válidos y no están en la lista de campos prohibidos
    if ($fields && is_array($fields)) {
        include '../Configuration/Connection.php';

        $valid_fields = [];
        $field_types = [];
        // Iterar sobre los campos seleccionados
        foreach ($fields as $field) {
            // Verificar si el campo no es '0' y no está en la lista de campos prohibidos
            if ($field !== '0' && !in_array($field, $prohibited_fields)) {
                // Obtener el tipo de dato de la columna seleccionada
                $sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = :field AND TABLE_NAME IN ('equipos', 'usuarios_equipos')";
                $stmt = $pdo->prepare($sql);
                // Ejecutar la consulta
                $stmt->execute(['field' => $field]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Si se encontró el campo
                if ($row) {
                    // Guardar el campo y su tipo de dato
                    $valid_fields[] = $field;
                    $field_types[$field] = $row['DATA_TYPE'];
                }
            }
        }

        if (!empty($valid_fields)) {
            // Guardar los campos seleccionados en las sesiones de ambos formularios
            $_SESSION['new_reg_fields'] = $valid_fields;
            $_SESSION['new_fieldR_types'] = $field_types;
            // Guardar los campos seleccionados en las sesiones de ambos formularios
            $_SESSION['new_upd_fields'] = $valid_fields;
            $_SESSION['new_fieldU_types'] = $field_types;
            // Guardar los campos seleccionados en las sesiones de ambos formularios
            $_SESSION['add_table_fields'] = $valid_fields;
            $_SESSION['add_table_field_types'] = $field_types;
            $_SESSION['success'] = 'Fields added to both forms and table successfully';
        } else {
            $_SESSION['error'] = 'No valid fields selected or all fields are prohibited';
        }
    } else {
        $_SESSION['error'] = 'Invalid fields selected';
    }

    header('Location: ../index.php');
    exit();
}
?>
