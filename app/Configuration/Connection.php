<?php
// Datos de conexión (ajusta según tu configuración)
$dsn = "mysql:host=localhost;dbname=garantias";
$usuario = "root";
$contrasena = "1213123Shape"; //clave usuario 'root' dependiendo del servidor

try {
    // Crear una instancia de PDO
    $pdo = new PDO($dsn, $usuario, $contrasena);

    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

?>