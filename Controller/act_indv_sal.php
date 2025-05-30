<?php
session_start();

// Inicializar variables de sesión si no existen
if (!isset($_SESSION['signature'])) {
    $_SESSION['signature'] = array();
}
if (!isset($_SESSION['vobo_signature'])) {
    $_SESSION['vobo_signature'] = '';
}
if (!isset($_SESSION['it_signature'])) {
    $_SESSION['it_signature'] = '';
}
if (!isset($_SESSION['admin_signature'])) {
    $_SESSION['admin_signature'] = '';
}

// Verificar si todas las firmas necesarias están presentes
if (
    empty($_SESSION['signature']) || empty($_SESSION['vobo_signature']) ||
    empty($_SESSION['it_signature']) || empty($_SESSION['admin_signature'])
) {
    header("Location: /3Shape_project/View/firma_act_indv_sal.php");
    exit();
}
require('../Model/fpdf.php');
include "../Configuration/Connection.php";

//Funcion reciclada para converitr texto
function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    public $fecha; // Add the fecha property

    function Header()
    {
        $this->Image('../Model/3shape-logo.png', 155, 5, 45);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 10, 'Fecha: ' . $this->fecha, 0, 1, 'L');
        $lugar_entrega = "Bogotá D.C.";
        $this->Cell(0, 10, 'Lugar de Entrega: ' . convertirTexto($lugar_entrega), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, convertirTexto('Acta de Salida'), 0, 1, 'C');
        $this->Ln(20);
    }

    function addCertificadoTexto($last_user, $cedula, $fecha, $cargo, $tipo_id)
    {
        $this->SetFont('Arial', '', 12);
        $texto1 = "Por medio de la presente se certifica que el/la señor(a); ";
        $this->Write(8, convertirTexto($texto1));
        $this->SetFont('Arial', 'B', 12);
        $this->Write(8, $last_user);
        $this->SetFont('Arial', '', 12);

        // Conditional text based on $tipo_id
        switch ($tipo_id) {
            case 'CE':
                $tipo_text = " identificado(a) con Cédula de Extranjeria: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'PP':
                $tipo_text = " identificado(a) con Pasaporte: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'RC':
                $tipo_text = " identificado(a) con Cédula de Residencia: $cedula, procede a desvincularse del cargo: $cargo  que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'CC':
                $tipo_text = " identificado(a) con Cédula de Ciudadania: $cedula, procede a desvincularse del cargo: $cargo  que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'TI':
                $tipo_text = " identificado(a) con Tarjeta de Identidad: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
            default:
                $tipo_text = " identificado(a) con ID: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
        }

        $this->Write(8, convertirTexto($tipo_text));
        $this->Ln(8);
        $text3 = "Adicionalmente, se declara que esta paz y salvo con la organización en cuanto a la entrega de los siguientes activos asignados para el desempeño de sus funciones: ";
        $this->MultiCell(0, 8, convertirTexto($text3), 0, 'L');

        $this->Ln(10);
        // Encabezados de la tabla
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

// Inicia el almacenamiento en búfer para evitar errores
ob_start();
$resultados = isset($_SESSION['asset_data']) ? $_SESSION['asset_data'] : [];
if (empty($resultados)) {
    die('No se encontraron datos en $_SESSION[\'asset_data\'] para generar el PDF.');
}

// Get the fecha from the first result
$fecha = isset($resultados[0]['fecha_salida']) && !empty($resultados[0]['fecha_salida']) ?
    $resultados[0]['fecha_salida'] : date('d/m/Y');
$pdf = new PDF();
$pdf->fecha = $fecha; // Set the fecha property
$pdf->AddPage();
$pdf->SetFont('Arial', 'I', 10);

$count = 0;
$totalRegistros = count($resultados);

foreach ($resultados as $index => $row) {
    if (!isset($row['cedula']) || $row['cedula'] == 0 || !isset($row['user_status'])) {
        continue;
    }

    if ($count >= 15)
        break;

    // Obtenemos todos los nuevos campos
    $tipo_id = $row['Tipo_ID'];
    $last_user = $row['last_user'];
    $cedula = $row['cedula'];
    $serial = $row['serial_number'];
    $cargo = $row['job_title'];
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

    $pdf->addCertificadoTexto(
        convertirTexto($last_user),
        convertirTexto($cedula),
        $fecha,
        convertirTexto($cargo),
        convertirTexto($tipo_id)
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
    $pdf->MultiCell(0, 8, convertirTexto('Datos exclusivos administrativos:'));
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Ln(0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(44, 6, convertirTexto('Carne: '), 1, 0, 'C');
    $pdf->Cell(30, 6, convertirTexto($carnet), 1, 0, 'C');
    $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
    $pdf->Cell(44, 6, convertirTexto('Llave Locker: '), 1, 0, 'C');
    $pdf->Cell(30, 6, convertirTexto($llave), 1, 0, 'C');
    $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
    $pdf->Cell(44, 6, convertirTexto('SIM: '), 1, 0, 'C');
    $pdf->Cell(30, 6, convertirTexto(''), 1, 0, 'C');
    $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
    $pdf->Ln(12);

    // Sección de firmas - reemplaza el bloque actual
    $pdf->Ln(12);

    // Títulos de las firmas
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(44, 10, convertirTexto('Firma Colaborador'), 1, 0, 'C');
    $pdf->Cell(48, 10, convertirTexto('Firma Jefe Inmediato'), 1, 0, 'C');
    $pdf->Cell(44, 10, convertirTexto('Vo.Bo. IT'), 1, 0, 'C');
    $pdf->Cell(44, 10, convertirTexto('Vo.Bo. Administrativo'), 1, 1, 'C');

    // Espacio para las firmas con imágenes
    $y = $pdf->GetY();
    $x = $pdf->GetX();

    // Firma Colaborador
    if (isset($_SESSION['signature'][$index]) && file_exists($_SESSION['signature'][$index])) {
        $pdf->Image($_SESSION['signature'][$index], $x, $y, 44, 15);
    }
    $pdf->Cell(44, 15, '', 1, 0, 'C');
    $x += 44;

    // Firma Jefe Inmediato
    if (isset($_SESSION['vobo_signature']) && file_exists($_SESSION['vobo_signature'])) {
        $pdf->Image($_SESSION['vobo_signature'], $x, $y, 48, 15);
    }
    $pdf->Cell(48, 15, '', 1, 0, 'C');
    $x += 48;

    // Firma IT
    if (isset($_SESSION['it_signature']) && file_exists($_SESSION['it_signature'])) {
        $pdf->Image($_SESSION['it_signature'], $x, $y, 44, 15);
    }
    $pdf->Cell(44, 15, '', 1, 0, 'C');
    $x += 44;

    // Firma Administrativo
    if (isset($_SESSION['admin_signature']) && file_exists($_SESSION['admin_signature'])) {
        $pdf->Image($_SESSION['admin_signature'], $x, $y, 44, 15);
    }
    $pdf->Cell(44, 15, '', 1, 1, 'C');

    // Limpieza de archivos temporales al final del proceso
    if ($index === count($resultados) - 1) {
        // Limpiar firmas de colaboradores
        if (isset($_SESSION['signature']) && is_array($_SESSION['signature'])) {
            foreach ($_SESSION['signature'] as $sig_path) {
                if (file_exists($sig_path)) {
                    @unlink($sig_path);
                }
            }
            unset($_SESSION['signature']);
        }

        // Limpiar otras firmas
        $signatures = ['vobo_signature', 'it_signature', 'admin_signature'];
        foreach ($signatures as $sig) {
            if (isset($_SESSION[$sig]) && file_exists($_SESSION[$sig])) {
                @unlink($_SESSION[$sig]);
                unset($_SESSION[$sig]);
            }
        }
    }

    $count++;
    if ($count < $totalRegistros) {
        $pdf->AddPage();
    }
}

$last_user_filename = isset($last_user) ? strtoupper(preg_replace('/[^a-zA-Z0-9 ]/', '_', $last_user)) : 'UNKNOWN';

$pdf->Output('I', "$last_user_filename.pdf");
ob_end_flush();
