<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Configurar directorio de firmas
    $signatureDir = __DIR__ . '/../signatures/';
    if (!file_exists($signatureDir)) {
        mkdir($signatureDir, 0755, true);
    }

    // Procesar y guardar cada firma de colaborador
    $_SESSION['signature'] = [];
    foreach ($data['signatures'] as $index => $signature) {
        $cleanData = preg_replace('#^data:image/png;base64,#i', '', $signature);
        $filename = 'firma_' . $index . '_' . uniqid() . '.png';
        $filepath = $signatureDir . $filename;
        file_put_contents($filepath, base64_decode($cleanData));
        $_SESSION['signature'][] = $filepath;
    }
    
    // Procesar firmas de aprobación
    $voboData = preg_replace('#^data:image/png;base64,#i', '', $data['vobo_signature']);
    $itData = preg_replace('#^data:image/png;base64,#i', '', $data['it_signature']);
    $adminData = preg_replace('#^data:image/png;base64,#i', '', $data['admin_signature']);
    
    $voboFilename = 'vobo_' . uniqid() . '.png';
    $itFilename = 'it_' . uniqid() . '.png';
    $adminFilename = 'admin_' . uniqid() . '.png';
    
    $voboPath = $signatureDir . $voboFilename;
    $itPath = $signatureDir . $itFilename;
    $adminPath = $signatureDir . $adminFilename;

    // Guardar las imágenes (Temporalmente)
    file_put_contents($voboPath, base64_decode($voboData));
    file_put_contents($itPath, base64_decode($itData));
    file_put_contents($adminPath, base64_decode($adminData));

    // Guardar rutas en sesión
    $_SESSION['vobo_signature'] = $voboPath;
    $_SESSION['it_signature'] = $itPath;
    $_SESSION['admin_signature'] = $adminPath;

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>