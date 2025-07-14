<?php
include "../Configuration/Connection.php";

try {
    // Validar y limpiar la entrada
    $serial = strtolower(trim($_POST['serial'] ?? '')); // Elimina espacios en blanco al inicio y al final

    // Validar si el serial está vacío después de eliminar espacios
    if (empty($serial)) {
        echo "Serial Number Not valid.";
        exit;
    }

    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare("SELECT serial_number FROM vista_equipos_usuarios WHERE serial_number LIKE :serial_number LIMIT 4");
    $stmt->bindValue(':serial_number', '%' . $serial . '%');
    $stmt->execute();

    // Mostrar resultados
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div class='result-item' onclick=\"selectResult('" . htmlspecialchars($row['serial_number']) . "', 'search_serial', 'resultados2')\">" .
            htmlspecialchars($row['serial_number']) . "</div>";
        }
    } else {
        echo "No data found.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>