<?php
ob_start();
//Archivo que consulta la mayoria de los datos de los activos osea registros y los trae a el pdf principal como pdf
require './fpdf.php';
include '../Configuration/Connection.php';

// Función de conversión para texto
function convertirTexto($texto) {
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

//Crear Clase en base a la Api de FPDF
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Configuración inicial
        $this->SetMargins(10, 10, 10);
        
        // Fondo del encabezado
        $this->SetFillColor(245, 245, 245);
        $this->Rect(0, 0, $this->GetPageWidth(), 50, 'F');
        
        // Logo
        $this->Image('3shape-logo.png', 15, 10, 35);
        
        // Línea decorativa
        $this->SetDrawColor(164, 0, 125);
        $this->SetLineWidth(0.5);
        $this->Line(55, 15, $this->GetPageWidth() - 15, 15);
        
        // Título principal
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(50, 50, 50);
        $this->SetXY(55, 20);
        $this->Cell(0, 10, convertirTexto("General Report"), 0, 1, 'L');
        
        // Fecha de generación
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(100, 100, 100);
        $this->SetXY(55, 32);
        $this->Cell(0, 10, convertirTexto("Generated on: " . date('d/m/Y')), 0, 1, 'L');
        
        // Espacio antes de la tabla
        $this->Ln(20);
        
        // Estilo de la tabla
        $this->SetFillColor(164, 0, 125);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(190, 190, 190);
        $this->SetLineWidth(0.2);
        $this->SetFont('Arial', 'B', 8);

        // Encabezados de tabla con mejor distribución - REMOVED FIELDS AS REQUESTED
        $this->SetX(5); // Ajuste de posición inicial
        $cellHeight = 12;
        $this->Cell(40, $cellHeight, convertirTexto('Asset Name'), 1, 0, 'C', 1);
        $this->Cell(40, $cellHeight, convertirTexto('Serial'), 1, 0, 'C', 1);
        $this->Cell(35, $cellHeight, convertirTexto('User Status'), 1, 0, 'C', 1);
        $this->Cell(60, $cellHeight, convertirTexto('Last User'), 1, 0, 'C', 1);
        $this->Cell(20, $cellHeight, convertirTexto('ID'), 1, 1, 'C', 1);
    }

    // Pie de página
    function Footer()
    {
        // Línea separadora
        $this->SetY(-20);
        $this->SetDrawColor(164, 0, 125);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), $this->GetPageWidth() - 15, $this->GetY());
        
        // Información del pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, convertirTexto('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }
    
    // Función para celdas con texto multilínea
    function MultiCellTable($w, $h, $txt, $border=1, $align='L', $fill=false)
    {
        // Guardar posición actual
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Calcular altura necesaria para el texto
        $this->MultiCell($w, 5, $txt, 0, $align, false);
        $height = $this->GetY() - $y;
        
        // Asegurar altura mínima
        $height = max($height, $h);
        
        // Volver a la posición original
        $this->SetXY($x, $y);
        
        // Dibujar celda con la altura calculada
        $this->Cell($w, $height, '', $border, 0, $align, $fill);
        
        // Reposicionar para escribir texto
        $this->SetXY($x, $y);
        
        // Escribir el texto
        $this->MultiCell($w, 5, $txt, 0, $align, $fill);
        
        // Mover a la siguiente posición
        $this->SetXY($x + $w, $y + $height);
        
        return $height;
    }
}

$pdf = new PDF();//CREAR PDF
$pdf->SetLeftMargin(5); // Ajuste para dar más espacio horizontal
$pdf->SetAutoPageBreak(true, 20); // Asegurar saltos de página automáticos
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 7); // Tamaño de fuente ligeramente reducido para contenido
$pdf->SetDrawColor(163, 163, 163);

// Construir la consulta usando los filtros de búsqueda
$sql = "SELECT * FROM vista_equipos_usuarios";
$where = [];
$params = [];


// Otros filtros
if (!empty($_GET['search_assetname'])) {
    $assetname = trim($_GET['search_assetname']); // Elimina espacios en blanco al inicio y al final
}

if (!empty($assetname)) {
    $where[] = "LOWER(assetname) LIKE LOWER(:assetname)";
    $params[':assetname'] = '%' . $assetname . '%';
}

// Filtro de número de serie
if (!empty($_GET['search_serial'])) {
    $serial = trim($_GET['search_serial']); // Elimina espacios en blanco al inicio y al final
    if (!empty($serial)) {
        $where[] = "LOWER(serial_number) LIKE LOWER(:serial)";
        $params[':serial'] = '%' . $serial . '%';
    }
}

// Filtro de cédula
if (!empty($_GET['search_cedula'])) {
    $cedula = trim($_GET['search_cedula']); // Elimina espacios en blanco al inicio y al final
    if (!empty($cedula)) {
        $where[] = "LOWER(cedula) LIKE LOWER(:cedula)";
        $params[':cedula'] = '%' . $cedula . '%';
    }
}

// Filtro de estado de usuario (selección múltiple)
if (!empty($_GET['search_user_status'])) {
    $filtered_statuses = array_filter($_GET['search_user_status'], function ($status) {
        return $status != 0;
    });
    //Condicion en caso de que el estado del usuario no este vacio
    if (!empty($filtered_statuses)) {
        $user_status_conditions = [];
        foreach ($filtered_statuses as $index => $status) {
            $param_name = ":status_$index";
            $user_status_conditions[] = "user_status = $param_name";
            $params[$param_name] = $status;
        }
        $where[] = '(' . implode(' OR ', $user_status_conditions) . ')';
    }
}

// Agregar condiciones dinámicas
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Pasar todos los parámetros de una vez
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        $fillColor = false; // Inicializar la variable antes del bucle
        
        foreach ($resultados as $row) {
            if ($row['cedula'] == 0 || $row['user_status'] == 'Stock') {
                continue; // Saltar registros según los criterios
            }
            
            // Comprobar si hay espacio suficiente en la página para la fila
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }
            
            // Generar contenido del PDF con filas alternadas
            $fillColor = !$fillColor;
            $pdf->SetFillColor($fillColor ? 245 : 255, $fillColor ? 245 : 255, $fillColor ? 245 : 255);
            $pdf->SetTextColor(50, 50, 50);
            
            // Guardar posición inicial Y
            $initialY = $pdf->GetY();
            $maxRowHeight = 8; // Altura mínima de fila
            
            // Celdas con posible contenido multilínea
            $pdf->SetX(5);
            
            // Celda con texto multilínea para assetname
            $heightAsset = $pdf->MultiCellTable(40, $maxRowHeight, convertirTexto($row["assetname"]), 1, 'C', $fillColor);
            
            // Volver a la posición para la siguiente celda
            $pdf->SetXY(45, $initialY);
            $heightSerial = $pdf->MultiCellTable(40, $maxRowHeight, convertirTexto($row["serial_number"]), 1, 'C', $fillColor);
            
            $pdf->SetXY(85, $initialY);
            $heightStatus = $pdf->MultiCellTable(35, $maxRowHeight, convertirTexto($row["user_status"]), 1, 'C', $fillColor);
            
            $pdf->SetXY(120, $initialY);
            $heightUser = $pdf->MultiCellTable(60, $maxRowHeight, convertirTexto($row["last_user"]), 1, 'C', $fillColor);
            
            $pdf->SetXY(180, $initialY);
            $heightCedula = $pdf->MultiCellTable(20, $maxRowHeight, convertirTexto($row["cedula"]), 1, 'C', $fillColor);
            
            // Encontrar la altura máxima de todas las celdas
            $rowHeight = max($heightAsset, $heightSerial, $heightStatus, $heightUser, $heightCedula);
            
            // Mover al inicio de la próxima fila
            $pdf->SetY($initialY + $rowHeight);
        }
    } else {
        $pdf->Cell(0, 10, 'No data found', 1, 1, 'C', 0);
    }
} catch (PDOException $e) {
    ob_end_clean(); // Limpiar el buffer en caso de error
    die("Error: " . $e->getMessage());
}

// Limpiar cualquier salida en el buffer antes de enviar el PDF
ob_end_clean();
$pdf->Output('Reporte_Equipos.pdf', 'I');