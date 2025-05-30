<?php
ob_start();
session_start();
require('../Model/fpdf.php');
include "../Configuration/Connection.php";

// Inicializar variables de sesión si no existen
if (!isset($_SESSION['signature'])) {
    $_SESSION['signature'] = array();
}
if (!isset($_SESSION['vobo_signature'])) {
    $_SESSION['vobo_signature'] = '';
}
// Verificar y guardar las firmas si se enviaron
if (isset($_POST['signature']) && isset($_POST['vobo_signature'])) {
    // Configurar directorio de firmas
    $signatureDir = __DIR__ . '/../signatures/';
    if (!file_exists($signatureDir)) {
        mkdir($signatureDir, 0755, true);
    }

    // Generar nombres únicos para los archivos
    $signatureFilename = 'firma_' . uniqid() . '.png';
    $voboSignatureFilename = 'vobo_' . uniqid() . '.png';

    $signaturePath = $signatureDir . $signatureFilename;
    $voboSignaturePath = $signatureDir . $voboSignatureFilename;

    // Decodificar y guardar las imágenes
    $signatureData = base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_POST['signature']));
    file_put_contents($signaturePath, $signatureData);

    $voboSignatureData = base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_POST['vobo_signature']));
    file_put_contents($voboSignaturePath, $voboSignatureData);

    // Guardar rutas en sesión
    $_SESSION['signature'][] = $signaturePath;
    $_SESSION['vobo_signature'] = $voboSignaturePath;
}

// Función para convertir texto a ISO-8859-1
function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../Model/3shape-logo.png', 155, 5, 45);
        $this->Ln(10);
        $fecha_actual = date('d/m/Y');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'L');
        $lugar_entrega = "Bogotá D.C.";
        $this->Cell(0, 10, 'Lugar de Entrega: ' . convertirTexto($lugar_entrega), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, convertirTexto('Acta de Entrega'), 0, 1, 'C');
        $this->Ln(20);
    }

    function addCertificadoTexto($last_user, $cedula, $cargo, $tipo_id,$fecha_ingreso)
    {
        $this->SetFont('Arial', '', 12);
        $texto = "Por medio de la presente se certifica que el/la señor(a); ";
        $this->Write(8, convertirTexto($texto));
        $this->SetFont('Arial', 'B', 12);
        $this->Write(8, $last_user);
        $this->SetFont('Arial', '', 12);

        // Conditional text based on $tipo_id
        switch ($tipo_id) {
            case 'CE':
                $tipo_text = " identificado(a) con Cédula de Extranjeria No. $cedula, y quien ocupa el cargo de $cargo se le hace entrega de los siguientes activos que manejará en 3Shape apartir de la fecha.";
                break;
            case 'PP':
                $tipo_text = " identificado(a) con Pasaporte No. $cedula, y quien ocupa el cargo de $cargo se le hace entrega de los siguientes activos que manejará en 3Shape apartir de la fecha $fecha_ingreso.";
                break;
            case 'RC':
                $tipo_text = " identificado(a) con Cédula de Residencia No. $cedula,y quien ocupa el cargo de $cargo se le hace entrega de los siguientes activos que manejará en 3Shape apartir de la fecha $fecha_ingreso.";
                break;
            case 'CC':
                $tipo_text = " identificado(a) con Cédula de Ciudadanía No. $cedula, y quien ocupa el cargo de $cargo se le hace entrega de los siguientes activos que manejará en 3Shape apartir de la fecha $fecha_ingreso.";
                break;
            case 'TI':
                $tipo_text = " identificado(a) con Tarjeta de Identidad No. $cedula, y quien ocupa el cargo de $cargo se le hace entrega de los siguintes activos que manejará en 3Shape apartir de la fecha $fecha_ingreso.";
                break;
            default:
                $tipo_text = " identificado(a) con ID: $cedula, y quien ocupa el cargo de $cargo se le hace entrega de los siguientes activos que manejará en 3Shape apartir de la fecha $fecha_ingreso.";
                break;
        }

        $this->Write(8, convertirTexto($tipo_text));
        $this->Ln(18);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(22, 10, convertirTexto('No'), 1, 0, 'C');
        $this->Cell(40, 10, convertirTexto('Descripción del Activo'), 1, 0, 'C');
        $this->Cell(29, 10, convertirTexto('Serial'), 1, 0, 'C');
        $this->Cell(29, 10, convertirTexto('Estado'), 1, 0, 'C');
        $this->Cell(38, 10, convertirTexto('Observaciones'), 1, 1, 'C');
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, convertirTexto('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// Al inicio del archivo, después de session_start():
if (isset($_SESSION['signature_data']) && isset($_SESSION['vobo_signature_data'])) {
    $signatureDir = __DIR__ . '/../signatures/';
    if (!file_exists($signatureDir)) {
        mkdir($signatureDir, 0755, true);
    }

    $signatureFilename = 'firma_' . uniqid() . '.png';
    $voboSignatureFilename = 'vobo_' . uniqid() . '.png';

    $signaturePath = $signatureDir . $signatureFilename;
    $voboSignaturePath = $signatureDir . $voboSignatureFilename;

    // Decodificar y guardar las imágenes
    $signatureData = base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_SESSION['signature_data']));
    file_put_contents($signaturePath, $signatureData);

    $voboSignatureData = base64_decode(preg_replace('#^data:image/png;base64,#i', '', $_SESSION['vobo_signature_data']));
    file_put_contents($voboSignaturePath, $voboSignatureData);

    // Guardar rutas en sesión
    $_SESSION['signature'][] = $signaturePath;
    $_SESSION['vobo_signature'] = $voboSignaturePath;

    // Limpiar los datos temporales
    unset($_SESSION['signature_data']);
    unset($_SESSION['vobo_signature_data']);
}

// Obtener datos de activos desde la sesión
$resultados = isset($_SESSION['asset_data']) ? $_SESSION['asset_data'] : [];
if (empty($resultados)) {
    die('No se encontraron datos en $_SESSION[\'asset_data\'] para generar el PDF.');
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'I', 10);

$count = 0;
$totalRegistros = count($resultados);
$last_user = '';

foreach ($resultados as $index => $row) {
    if (!isset($row['cedula']) || $row['cedula'] == 0) {
        continue;
    }

    if ($count >= 15) break;

    $count++;
    // Obtenemos todos los nuevos campos
    $tipo_id = $row['Tipo_ID'];
    $last_user = $row['last_user'];
    $cedula = $row['cedula'];
    $serial = $row['serial_number'];
    $cargo = $row['job_title'];
    $fecha_ingreso = $row['fecha_ingreso'];
    $asset_status = $row['asset_status'] ?? '';
    $asset_observations = $row['asset_observations'] ?? '';
    $headSet = $row['HeadSet'] ?? '';
    $headSet_status = $row['headset_status'] ?? '';
    $headSet_observations = $row['headset_observations'] ?? '';
    $dongle = $row['Dongle'] ?? '';
    $dongle_status = $row['dongle_status'] ?? '';
    $dongle_observations = $row['dongle_observations'] ?? '';
    $celular = $row['Celular'] ?? '';
    $celular_status = $row['celular_status'] ?? '';
    $celular_observations = $row['celular_observations'] ?? '';
    $carnet = !empty($row['Carnet']) ? $row['Carnet'] : 'Pendiente';
    $llave = !empty($row['LLave']) ? $row['LLave'] : 'Pendiente';

    // Añadir texto de certificado
    $pdf->addCertificadoTexto(
        convertirTexto($last_user),
        convertirTexto($cedula),
        convertirTexto($cargo),
        convertirTexto($tipo_id),
        convertirTexto($fecha_ingreso),
    );

    $itemCounter = 1;

    // Computador
    $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
    $pdf->Cell(40, 10, convertirTexto('Computador Personal'), 1, 0, 'C');
    $pdf->Cell(29, 10, convertirTexto($serial), 1, 0, 'C');
    $pdf->Cell(29, 10, convertirTexto($asset_status), 1, 0, 'C');
    $pdf->Cell(38, 10, convertirTexto($asset_observations), 1, 1, 'C');

    // HeadSet (si existe)
    if (!empty($headSet)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Head Set'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($headSet), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($headSet_status), 1, 0, 'C');
        $pdf->Cell(38, 10, convertirTexto($headSet_observations), 1, 1, 'C');
    }

    // Dongle (si existe)
    if (!empty($dongle)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Dongle'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($dongle), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($dongle_status), 1, 0, 'C');
        $pdf->Cell(38, 10, convertirTexto($dongle_observations), 1, 1, 'C');
    }

    // Celular (si existe)
    if (!empty($celular)) {
        $pdf->Cell(22, 10, convertirTexto($itemCounter++), 1, 0, 'C');
        $pdf->Cell(40, 10, convertirTexto('Celular'), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($celular), 1, 0, 'C');
        $pdf->Cell(29, 10, convertirTexto($celular_status), 1, 0, 'C');
        $pdf->Cell(38, 10, convertirTexto($celular_observations), 1, 1, 'C');
    }

    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->MultiCell(0, 10, convertirTexto('Datos exclusivos Administrativos:'));
    $pdf->Ln(7);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, convertirTexto('Carne: '), 1, 0, 'L');
    $pdf->Cell(30, 7, convertirTexto($carnet), 1, 1, 'C');
    $pdf->Cell(40, 7, convertirTexto('Llave locker: '), 1, 0, 'L');
    $pdf->Cell(30, 7, convertirTexto($llave), 1, 1, 'C');
    $pdf->Cell(40, 7, convertirTexto('SIM card: '), 1, 0, 'L');
    $pdf->Cell(30, 7, convertirTexto(''), 1, 1, 'C');
    $pdf->Ln(8);

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, convertirTexto('El abajo firmante declara haber recibido con satisfacción los elementos antes mencionados con la condición de que cuidará de ellos.'));

    // Sección de firmas
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 10);

    // Firma del colaborador
    $pdf->Cell(44, 15, convertirTexto('Firma del Colaborador'), 1, 0, 'C');
    if (isset($_SESSION['signature'][$count - 1]) && file_exists($_SESSION['signature'][$count - 1])) {
        $pdf->Image($_SESSION['signature'][$count - 1], $pdf->GetX(), $pdf->GetY(), 44, 15);
    }
    $pdf->Cell(44, 15, '', 1, 0, 'C');

    // Firma Vo.Bo.
    $pdf->Cell(47, 15, convertirTexto('Vo.Bo. Departamento de IT'), 1, 0, 'C');
    if (isset($_SESSION['vobo_signature']) && file_exists($_SESSION['vobo_signature'])) {
        $pdf->Image($_SESSION['vobo_signature'], $pdf->GetX(), $pdf->GetY(), 44, 15);
    }
    $pdf->Cell(47, 15, '', 1, 0, 'C');

    // Limpiar archivos temporales y variables de sesión
    if ($count < $totalRegistros) {
        $pdf->AddPage();
    }
}

$last_user_filename = 'default.pdf';
if (!empty($last_user)) {
    $last_user_filename = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $last_user)) . '.pdf';
}

$pdf->Output('I', $last_user_filename);

// 5. Eliminar archivos de firmas DESPUÉS de generar el PDF
if (!empty($_SESSION['signature'])) {
    foreach ($_SESSION['signature'] as $file) {
        if (file_exists($file)) @unlink($file);
    }
    unset($_SESSION['signature']);
}

if (!empty($_SESSION['vobo_signature'])) {
    if (file_exists($_SESSION['vobo_signature'])) @unlink($_SESSION['vobo_signature']);
    unset($_SESSION['vobo_signature']);
}

ob_end_flush();
