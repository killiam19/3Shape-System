<?php
require('fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    
    // Encabezado
    $pdf->Cell(0,10,'ACTA DE ENTREGA DE ACTIVO',0,1,'C');
    $pdf->Ln(10);
    
    // Datos del activo
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(40,10,'ID del Activo:',0,0);
    $pdf->Cell(0,10,$data['id'],0,1);
    $pdf->Cell(40,10,'Serial:',0,0);
    $pdf->Cell(0,10,$data['serial'],0,1);
    $pdf->Cell(40,10,'Usuario:',0,0);
    $pdf->Cell(0,10,$data['usuario'],0,1);
    $pdf->Cell(40,10,'Ubicacion:',0,0);
    $pdf->Cell(0,10,$data['ubicacion'],0,1);
    $pdf->Cell(40,10,'Fecha:',0,0);
    $pdf->Cell(0,10,$data['fecha'],0,1);
    
    // Guardar PDF temporalmente
    $filename = 'acta_entrega_'.$data['id'].'_'.time().'.pdf';
    $filepath = $_SERVER['DOCUMENT_ROOT'].'/3Shape_project/temp/'.$filename;
    $pdf->Output($filepath, 'F');
    
    // Devolver URL del PDF
    echo json_encode(['pdf_url' => '/3Shape_project/temp/'.$filename]);
} else {
    http_response_code(405);
    echo 'Método no permitido';
}
?>