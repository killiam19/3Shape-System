<?php
include "../Configuration/Connection.php";

try {
    $user = strtolower(trim($_POST['user'] ?? ''));

    if(empty($user)){
        echo "Last user not valid.";
        exit;
    }

    // Prepare and execute query
    $stmt = $pdo->prepare("SELECT last_user FROM vista_equipos_usuarios WHERE last_user LIKE :last_user LIMIT 4"); 
    $stmt->bindValue(':last_user', '%'. $user . '%');
    $stmt->execute();

    // Display results
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div class='result-item' onclick=\"selectResult('" . htmlspecialchars($row['last_user']) . "', 'search_user', 'resultados3')\">" .
            htmlspecialchars($row['last_user']) . "</div>";
        }
    } else {
        echo "No data found.";
    }
}
catch (PDOException $e){
    echo "Error: " . $e->getMessage();
}
?>