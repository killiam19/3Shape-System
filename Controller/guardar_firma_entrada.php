<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Crear directorio si no existe
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

    // Procesar firma VoBo
    $voboData = preg_replace('#^data:image/png;base64,#i', '', $data['vobo_signature']);
    $voboFilename = 'vobo_' . uniqid() . '.png';
    $voboPath = $signatureDir . $voboFilename;
    file_put_contents($voboPath, base64_decode($voboData));
    $_SESSION['vobo_signature'] = $voboPath;

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>