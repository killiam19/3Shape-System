<?php
require('./fpdf.php');
include '../Configuration/Connection.php';

// Función de conversión para texto
function convertirTexto($texto)
{
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF
{
    public $fecha;
    // Cabecera personalizada para el Acta de Salida
    function Header()
    {
        $this->Image('3shape-logo.png', 155, 5, 45);

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Fecha: ' . $this->fecha, 0, 1, 'L');
        $this->Ln(15);
        $lugar_entrega = "Bogotá D.C.";
        $this->Cell(0, 10, 'Lugar de Entrega: ' . convertirTexto($lugar_entrega), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, convertirTexto('Acta de Salida'), 0, 1, 'C');
        $this->Ln(18);
    }
    // Método para agregar el texto personalizado con Last_user, cédula y fecha
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
            case 'CC':
                $tipo_text = " identificado(a) con Cédula de Ciudadanía: $cedula, procede a desvincularse del cargo: $cargo  que venía desempeñando, a partir de la fecha $fecha.";
                break;
            case 'TI':
                $tipo_text = " identificado(a) con Tarjeta de Identidad: $cedula, procede a desvincularse del cargo: $cargo que venia desempeñando, a partir de la fecha $fecha.";
                break;
            default:
                $tipo_text = " identificado(a) con Cedula de Ciudadania: $cedula, procede a desvincularse del cargo: $cargo que venía desempeñando, a partir de la fecha $fecha.";
                break;
        }

        $this->Write(8, convertirTexto($tipo_text));
        $this->Ln(18);

        $text3 = "adicionalmente, se declara que esta paz y salvo con la organización en cuanto a la entrega de los siguientes activos asignados para el desempeño de sus funciones: ";
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
         // Pie de Pagina
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, convertirTexto('Página') . $this->PageNo(), 0, 0, 'C');
    }
}

ob_start();
$pdf = new PDF();
$sql = "SELECT *
    FROM equipos
    LEFT JOIN usuarios_equipos ON equipos.assetname = usuarios_equipos.fk_assetname";
$where = [];
$params = []; //Arreglo para almacenar datos de la consulta
//Consultas 
if (!empty($_GET['search_assetname'])) {
    $where[] = "LOWER(equipos.assetname) LIKE :search_assetname";
    $params[':search_assetname'] = "%" . strtolower(trim($_GET['search_assetname'])) . "%";
}

if (!empty($_GET['search_entry_date']) && !empty($_GET['search_departure_date'])) {
    $entry_date = $_GET['search_entry_date'];
    $departure_date = $_GET['search_departure_date'];
    $entry_date_mysql = date('Y-m-d', strtotime($entry_date));
    $departure_date_mysql = date('Y-m-d', strtotime($departure_date));
    $where[] = "STR_TO_DATE(usuarios_equipos.fecha_salida, '%d/%m/%Y') BETWEEN :entry_date AND :departure_date";
    $params[':entry_date'] = $entry_date_mysql;
    $params[':departure_date'] = $departure_date_mysql;
}
if (!empty($_GET['search_serial'])) {
    $where[] = "LOWER(equipos.serial_number) LIKE :search_serial";
    $params[':search_serial'] = "%" . strtolower(trim($_GET['search_serial'])) . "%";
}
if (!empty($_GET['search_cedula'])) {
    $where[] = "LOWER(usuarios_equipos.cedula) LIKE :search_cedula";
    $params[':search_cedula'] = "%" . strtolower(trim($_GET['search_cedula'])) . "%";
}
// Filtro de nombre de usuario
if (!empty($_GET['search_user'])) {
    $where[] = "LOWER(usuarios_equipos.last_user) LIKE :usuario";
    $params[':usuario'] = '%' . strtolower(trim($_GET['search_user'])) . '%';
}

if (!empty($_GET['search_status_change'])) {
    $where[] = "LOWER(usuarios_equipos.status_change) LIKE :status_change";
    $params[":status_change"] = '%' . strtolower(trim($_GET['search_status_change'])) . '%';
}

// Filtro de estado de usuario (selección múltiple)
if (!empty($_GET['search_user_status'])) {
    // Filtrar los valores no deseados, como "0"
    $filtered_statuses = array_filter($_GET['search_user_status'], function ($status) {
        return $status != 0;  // Filtra valores que no sean 0
    });

    if (!empty($filtered_statuses)) {  // Solo procedemos si hay valores válidos
        $user_status_conditions = [];
        foreach ($filtered_statuses as $index => $status) {
            $param_name = ":status_$index";  // Generar un nombre único para cada parámetro
            $user_status_conditions[] = "usuarios_equipos.user_status = $param_name";
            $params[$param_name] = $status;  // Guardar el valor en un array temporal para su enlace posterior
        }
        $where[] = '(' . implode(' OR ', $user_status_conditions) . ')';
    }
}

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value); // Aquí usamos $key en lugar de $param
}
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRegistros = count($resultados);

try {
    // Set the fecha property before adding the first page
    $fecha = isset($resultados[0]['fecha_salida']) && !empty($resultados[0]['fecha_salida']) ?
        $resultados[0]['fecha_salida'] : date('d/m/Y');
    $pdf->fecha = $fecha;

    $pdf->AddPage();
    $pdf->SetFont('Arial', 'I', 10);

    if ($resultados) {
        $count = 0;

        foreach ($resultados as $index => $row) {
            if (empty($row['cedula']) || $row['cedula'] == 0) {
            continue; // Saltar al siguiente registro cuando el numero de cedula es 0 o vacío
            }

            if ($count >= 15)
                break; // limitar el registro de laa pagina
            if ($count > 0) {
                $pdf->AddPage();
            }
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
            $fecha = isset($resultados[0]['fecha_salida']) && !empty($resultados[0]['fecha_salida']) ?
                $resultados[0]['fecha_salida'] : date('d/m/Y');
            // Añadir texto del certificado y encabezados de la tabla para cada registro
            $pdf->addCertificadoTexto(
                convertirTexto($last_user),
                convertirTexto($cedula),
                $fecha,
                convertirTexto($cargo),
                convertirTexto($tipo_id)
            );

            $itemCounter = 1; // Add this at the start of your loop

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

            $pdf->Ln(0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(44, 6, convertirTexto('Carné: '), 1, 0, 'C');
            $pdf->Cell(30, 6, convertirTexto($carnet), 1, 0, 'C');
            $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
            $pdf->Cell(44, 6, convertirTexto('Llave Locker: '), 1, 0, 'C');
            $pdf->Cell(30, 6, convertirTexto($llave), 1, 0, 'C');
            $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
            $pdf->Cell(44, 6, convertirTexto('SIM: '), 1, 0, 'C');
            $pdf->Cell(30, 6, convertirTexto(''), 1, 0, 'C');
            $pdf->Cell(44, 6, convertirTexto(''), 1, 1, 'C');
            $pdf->Ln(12);




            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 8, convertirTexto('El abajo firmante declara haber recibido a satisfacción los elementos antes mencionados y no tener pendiente ninguna entrega adicional de activos o documentación relevante a la empresa.'), 0, 'L');
            $pdf->Ln(6); // Salto de línea

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(44, 10, convertirTexto('Firma Colaborador'), 1, 0, 'C');
            $pdf->Cell(48, 10, convertirTexto('Firma del Jefe Inmediato'), 1, 0, 'C');
            $pdf->Cell(44, 10, convertirTexto('Vo.Bo IT'), 1, 0, 'C');
            $pdf->Cell(44, 10, convertirTexto('Vo.Bo.Administrativo'), 1, 1, 'C');

            $pdf->Cell(44, 10, convertirTexto(''), 1, 0, 'C');
            $pdf->Cell(48, 10, convertirTexto(''), 1, 0, 'C');
            $pdf->Cell(44, 10, convertirTexto(''), 1, 0, 'C');
            $pdf->Cell(44, 10, convertirTexto(''), 1, 1, 'C'); // Espacios para firma de las personas correspondientes

            $count++;
        }
    } else {
        $pdf->Cell(0, 10, 'No data found', 1, 1, 'C');
    }
} catch (Exception $e) {
    echo "Error" . $e->getMessage();
    exit;
}

ob_end_flush();
$last_user_filename = isset($last_user) ? strtoupper(preg_replace('/[^a-zA-Z0-9 ]/', '_', $last_user)) : 'UNKNOWN';

$pdf->Output('I', "$last_user_filename.pdf");
