
<?php
include "../Configuration/Connection.php";

try {
    // Validar entrada
    $cedula = trim(htmlspecialchars($_POST['cedula'] ?? ''));

    if (empty($cedula)) {
        echo "Please input correct data.";
        exit;
    }

    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare("SELECT cedula FROM vista_equipos_usuarios
        WHERE cedula LIKE :cedula LIMIT 5");
    $stmt->bindValue(':cedula', '%' . $cedula . '%');
    $stmt->execute();

    // Mostrar resultados
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($results)) {
        foreach ($results as $row) {
            echo "<div class='result-item' onclick=\"selectResult('" . htmlspecialchars($row['cedula']) . "')\">" . 
            htmlspecialchars($row['cedula']) . "</div>";
        }
    } else {
        echo "No Data found.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
