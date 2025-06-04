<?php
// Configuración para Docker y Railway
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'garantias';
$usuario = $_ENV['DB_USER'] ?? 'root';
$contrasena = $_ENV['DB_PASSWORD'] ?? '1213123Shape';

// Para Railway, detectar si estamos en producción
if (isset($_ENV['RAILWAY_ENVIRONMENT'])) {
    // Railway proporciona estas variables automáticamente
    $host = $_ENV['MYSQLHOST'] ?? $host;
    $dbname = $_ENV['MYSQLDATABASE'] ?? $dbname;
    $usuario = $_ENV['MYSQLUSER'] ?? $usuario;
    $contrasena = $_ENV['MYSQLPASSWORD'] ?? $contrasena;
    $port = $_ENV['MYSQLPORT'] ?? 3306;
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
} else {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
}

try {
    // Crear una instancia de PDO
    $pdo = new PDO($dsn, $usuario, $contrasena);

    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}
?>