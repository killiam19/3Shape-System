<?php
// Script para restablecer la contraseña del administrador
include_once './Configuration/Connection.php';

try {
    // Crear un nuevo hash para la contraseña "admin123"
    $password = "admin123";
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar si el usuario admin existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Actualizar la contraseña del usuario existente
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
        $stmt->execute(['password' => $hash]);
        echo "La contraseña del administrador ha sido actualizada correctamente.<br>";
        echo "Usuario: admin<br>";
        echo "Contraseña: admin123<br>";
    } else {
        // Crear un nuevo usuario administrador
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES ('admin', :password, 'admin@example.com', 'System Administrator', 'admin')");
        $stmt->execute(['password' => $hash]);
        echo "Se ha creado un nuevo usuario administrador.<br>";
        echo "Usuario: admin<br>";
        echo "Contraseña: admin123<br>";
    }
    
    echo "<br>Hash generado: " . $hash;
    echo "<br><a href='login.php'>Ir a la página de inicio de sesión</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
