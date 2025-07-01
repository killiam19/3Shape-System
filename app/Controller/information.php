<?php
require('../Model/fpdf.php');
include "../Configuration/Connection.php";

function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    private $primaryColor = array(9, 64, 125);
    private $secondaryColor = array(6, 83, 126);

    function __construct()
    {
        parent::__construct();
        $this->SetMargins(15, 30, 15); // Márgenes izquierdo, superior, derecho
    }

    function Header()
    {
        // Encabezado izquierdo (Información de la empresa)
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Cell(100, 6, convertirTexto('3 Shape.'), 0, 1, 'L');
        $this->SetFont('Helvetica', '', 8);
        $this->Cell(100, 4, convertirTexto('Carrera 9A # 99 - 02, Bogotá'), 0, 1, 'L');
        $this->Cell(100, 4, convertirTexto('NIT: 900574780'), 0, 1, 'L');
        $this->Ln(10);
        // Logo
        $this->Image('../Model/3shape-logo.png', 155, 12, 35);

        // Línea decorativa
        $this->SetY(45);
        $this->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->SetLineWidth(0.8);
        $this->Line(15, 46, 195, 46);
        $this->Ln(10);

        // Título principal
        $this->SetY(53);
        $this->SetFont('Helvetica', 'B', 20);
        $this->Cell(0, 10, convertirTexto('Informe Técnico de Activos'), 0, 1, 'C');

        // Fecha generación
        $this->SetY(71);
        $this->SetFont('Helvetica', 'I', 9);
        $this->SetTextColor(100);
        $this->Cell(0, 5, convertirTexto('Generado el: ') . date('d/m/Y H:i'), 0, 1, 'R');

        $this->Ln(8);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100);
        $this->SetDrawColor(200);
        $this->Line(15, $this->GetY() + 2, 195, $this->GetY() + 2);
        $this->Cell(0, 10, convertirTexto('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function ChapterTitle($num, $label)
    {
        $this->SetFont('Helvetica', 'B', 12);
        $this->SetFillColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $this->SetTextColor(255);
        $this->Cell(0, 8, convertirTexto($label), 0, 1, 'L', true);
        $this->Ln(4);
    }

    function InfoRow($title, $content, $width = 80, $height = 8)
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Cell($width, $height, convertirTexto($title), 0, 0);

        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(0);
        $this->MultiCell(0, $height, convertirTexto($content), 0, 1);

        $this->Ln(2);
    }
}

ob_start();
session_start();
$resultados = isset($_SESSION['asset_data']) ? $_SESSION['asset_data'] : [];
$additional_data = isset($_SESSION['additional_data']) ? $_SESSION['additional_data'] : [];

// Improved error handling
if (empty($resultados)) {
    die('No se encontraron datos en $_SESSION[\'asset_data\'] para generar el PDF.');
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetAutoPageBreak(true, 25);


foreach ($resultados as $row) {

    // Marco para cada activo
    $pdf->SetDrawColor(200);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFillColor(255);


    // Título de sección
    $pdf->ChapterTitle(1, 'Detalles del Activo: ' . $row['assetname']);
    $pdf->Ln(4);

    // Contenido en dos columnas
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $initialY = $pdf->GetY(); // Guardamos la posición Y inicial
    $pdf->SetX(20); // Margen izquierdo aumentado
    $pdf->InfoRow('Serial:', empty($row['serial_number']) ? 'Indefinido' : $row['serial_number'], 15);
    $pdf->SetX(20);
    $pdf->InfoRow('Estado del usuario:', empty($row['user_status']) ? 'Indefinido' : $row['user_status'], 30);
    $pdf->SetX(20);
    $pdf->InfoRow('Último Usuario:', empty($row['last_user']) ? 'Indefinido' : $row['last_user'], 24);
    $pdf->SetX(20);
    $pdf->InfoRow('Cargo:', empty($row['job_title']) ? 'Indefinido' : $row['job_title'], 12);
    $pdf->SetX(20);
    $pdf->InfoRow('Pais de compra:', empty($row['purchase_country']) ? 'Indefinido' : $row['purchase_country'], 25);

    // Columna derecha - Resetear a la posición Y inicial
    $maxY = $pdf->GetY(); // Capturamos la máxima Y alcanzada
    $pdf->SetXY(105, $initialY); // Posicionamos en columna derecha (105mm desde izquierda)
    $pdf->InfoRow('Estado de Cambio:', empty($row['status_change']) ? 'Indefinido' : $row['status_change'], 35);
    $pdf->SetXY(105, $initialY + 10); // Posicionamos en columna derecha (105mm desde izquierda) mas la poscicion inicial de Y
    $pdf->InfoRow('Identificación:', empty($row['cedula']) ? 'Indefinido' : $row['cedula'], 30);
    $pdf->SetXY(105, $initialY + 20); // Posicionamos en columna derecha (105mm desde izquierda) mas la poscicion porgresiva inicial de Y
    $pdf->InfoRow('Tipo ID:', empty($row['Tipo_ID']) ? 'Indefinido' : $row['Tipo_ID'], 15);
    $pdf->SetXY(105, $initialY + 30);
    $pdf->InfoRow('Fecha Salida:', empty($row['fecha_salida']) ? 'Indefinida' : $row['fecha_salida'], 25);
    $pdf->SetXY(105, $initialY + 40);
    $pdf->InfoRow('Fecha fin de garantia:', empty($row['warranty_enddate']) ? 'Indefinida' : $row['warranty_enddate'], 40);
    $pdf->Ln(10);

    // Accesorios
    $pdf->SetXY($x + 2, $pdf->GetY() + 5);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(0, 7, convertirTexto('Complementos:'), 0, 1);
    $pdf->Ln(3);

    $accessories = [
        'Dongle' => empty($row['Dongle']) || $row['Dongle'] == 0 ? 'Indefinido' : $row['Dongle'],
        'HeadSet' => empty($row['HeadSet']) || $row['HeadSet'] == 0 ? 'Indefinido' : $row['HeadSet'],
        'Carnet' => empty($row['Carnet']) || $row['Carnet'] == 0 ? 'Pendiente' : $row['Carnet'],
        'LLave' => empty($row['LLave']) || $row['LLave'] == 0 ? 'Pendiente' : $row['LLave']
    ];

    foreach ($accessories as $name => $value) {
        $pdf->SetX($x + 5);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(20, 6, convertirTexto($name . ':'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell(20, 6, convertirTexto($value ?? 'No'), 0, 1);
    }

    $pdf->Ln(90);
}

$pdf->Output('I', 'Informe_de_Activos.pdf', true);
ob_end_flush();
