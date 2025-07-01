<?php
// Set header before any output
header('Content-Type: application/json');

require('fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Crear el PDF en memoria
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Agregar logo
    $pdf->Image('3shape-logo.png', 10, 10, 30);
    
    // Título
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INFORMACION DEL ACTIVO', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Información del activo
    $pdf->SetFont('Arial', '', 12);
    
    // Datos del activo
    $pdf->Cell(60, 10, 'ID del Activo:', 0, 0);
    $pdf->Cell(0, 10, $data['asset']['id'], 0, 1);
    
    $pdf->Cell(60, 10, 'Numero de Serie:', 0, 0);
    $pdf->Cell(0, 10, $data['asset']['serial_number'], 0, 1);
    
    $pdf->Cell(60, 10, 'Pais de Compra:', 0, 0);
    $pdf->Cell(0, 10, $data['asset']['purchase_country'], 0, 1);
    
    $pdf->Cell(60, 10, 'Fin de Garantia:', 0, 0);
    $pdf->Cell(0, 10, $data['asset']['warranty_enddate'], 0, 1);
    
    $pdf->Ln(5);
    
    // Información del usuario
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Informacion del Usuario', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    $pdf->Cell(60, 10, 'Nombre:', 0, 0);
    $pdf->Cell(0, 10, $data['user']['name'], 0, 1);
    
    $pdf->Cell(60, 10, 'Ubicacion:', 0, 0);
    $pdf->Cell(0, 10, $data['user']['location'], 0, 1);
    
    $pdf->Cell(60, 10, 'Cargo:', 0, 0);
    $pdf->Cell(0, 10, $data['user']['job_title'], 0, 1);
    
    $pdf->Cell(60, 10, 'Estado:', 0, 0);
    $pdf->Cell(0, 10, $data['user']['status'], 0, 1);
    
    $pdf->Ln(5);
    
    // Metadatos
    $pdf->SetFont('Arial', 'I', 10);
    // Check if metadata exists before accessing it
    $generatedDate = isset($data['metadata']['generated_date']) ? $data['metadata']['generated_date'] : 'N/A';
    $qrVersion = isset($data['metadata']['qr_version']) ? $data['metadata']['qr_version'] : 'N/A';
    $pdf->Cell(0, 10, 'Fecha de Generacion: ' . $generatedDate, 0, 1);
    $pdf->Cell(0, 10, 'Version QR: ' . $qrVersion, 0, 1);
    
    // Capturar el PDF en una variable
    $pdfContent = $pdf->Output('', 'S');
    
    // Convertir el PDF a base64
    $pdfBase64 = base64_encode($pdfContent);
    
    // Crear un array con los datos del activo y el PDF
    $qrData = [
        'asset' => $data['asset'],
        'user' => $data['user'],
        'metadata' => $data['metadata'],
        'pdf' => $pdfBase64
    ];
    
    // Devolver los datos para el código QR (sin doble codificación)
    echo json_encode(['success' => true, 'qr_data' => $qrData]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error generating PDF/QR: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    // Ensure JSON output even for errors
    echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
}