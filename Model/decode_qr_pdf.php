<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Evita que los errores PHP se mezclen con el JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['qr_content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No se proporcionó contenido QR']);
        exit;
    }
    
    try {
        // Decodificar el contenido del QR
        $qrData = json_decode($data['qr_content'], true);
        
        if (!isset($qrData['pdf'])) {
            throw new Exception('El contenido QR no contiene un PDF');
        }
        
        // Decodificar el PDF de base64
        $pdfContent = base64_decode($qrData['pdf']);
        
        if ($pdfContent === false) {
            throw new Exception('Error al decodificar el PDF');
        }
        
        // Generar un nombre de archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'qr_pdf_');
        file_put_contents($tempFile, $pdfContent);
        
        // Devolver la URL temporal del PDF
        echo json_encode([
            'success' => true,
            'pdf_url' => '/3Shape_project/Model/serve_pdf.php?file=' . basename($tempFile)
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}

exit;