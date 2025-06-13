<?php
// Start output buffering to prevent any accidental output
ob_start();
session_start();
include "../Configuration/Connection.php";

// Incluir PhpSpreadsheet
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $selectedColumns = $_POST['columns'] ?? [];
    $format = $_POST['format'] ?? 'csv'; // Default to CSV if not specified

    if (empty($selectedColumns)) {
        $_SESSION['error'] = 'No columns selected';
        header('Location: ../Admin/index_admin.php');
        exit();
    }

    $columns = implode(", ", $selectedColumns);
    $sql = "SELECT $columns FROM equipos LEFT JOIN usuarios_equipos ON equipos.assetname = usuarios_equipos.fk_assetname";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica si existen datos
    if ($data) {
        // Determine file extension based on selected format
        $file_extension = '';
        $content_type = '';
        
        switch ($format) {
            case 'txt':
                $file_extension = 'txt';
                $content_type = 'text/plain';
                break;
            case 'excel':
                $file_extension = 'xlsx';
                $content_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            default:
                $file_extension = 'csv';
                $content_type = 'text/csv';
        }

        // Generacion del nombre del archivo
        $filename = "exportData_" . date('Y-m-d') . "." . $file_extension;

        if ($format === 'excel') {
            // Manejo especial para Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Get column headers
            $headers = array_keys($data[0]);
            
            // Escribir encabezados
            $columnLetter = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($columnLetter . '1', $header);
                // Aplicar estilo a los encabezados
                $sheet->getStyle($columnLetter . '1')->getFont()->setBold(true);
                $sheet->getStyle($columnLetter . '1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD3D3D3'); // Gris claro
                $columnLetter++;
            }
            
            // Escribir datos
            $rowIndex = 2;
            foreach ($data as $row) {
                $columnLetter = 'A';
                foreach ($row as $value) {
                    $sheet->setCellValue($columnLetter . $rowIndex, $value);
                    $columnLetter++;
                }
                // Alternar el color de fondo de las filas
                if ($rowIndex % 2 == 0) {
                    $lastColumn = --$columnLetter; // Última columna usada
                    $sheet->getStyle('A' . $rowIndex . ':' . $lastColumn . $rowIndex)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFEBEBEB'); // Gris muy claro
                }
                $rowIndex++;
            }
            
            // Aplicar autoajuste a las columnas
            $columnLetter = 'A';
            for ($i = 0; $i < count($headers); $i++) {
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                $columnLetter++;
            }

            // Aplicar bordes a todas las celdas con datos
            $lastColumn = --$columnLetter; // Última columna usada
            $lastRow = $rowIndex - 1; // Última fila usada
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray($styleArray);
            
            // Crear el writer
            $writer = new Xlsx($spreadsheet);
            
            // Encabezados HTTP
            header('Content-Type: ' . $content_type);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');
            
            // Limpiar buffer y escribir archivo
            ob_end_clean();
            $writer->save('php://output');
            exit();
            
        } else {
            // Manejo para CSV y TXT (código original)
            // Encabezados HTTP
            header('Content-Type: ' . $content_type . '; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Apertura del flujo de salida con manejo de errores
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new RuntimeException('Unable to open output stream');
            }

            // Get column headers
            $headers = array_keys($data[0]);

            if ($format === 'txt') {
                // Escribir el inicio del array JSON
                fwrite($output, "[");
                
                $totalRows = count($data);
                foreach ($data as $index => $row) {
                    // Agregar sangría y formato JSON pretty print
                    fwrite($output, ($index === 0 ? "\n" : "") . "    " . json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    
                    // Agregar coma y nueva línea si no es el último elemento
                    if ($index < $totalRows - 1) {
                        fwrite($output, ",\n");
                    }
                }
                
                // Cerrar el array JSON
                fwrite($output, "\n]");
            } else {
                // For CSV format (default)
                fputcsv($output, $headers);

                // Write data rows with default delimiter (comma)
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }

            // Se cierra el flujo de salida
            fclose($output);

            // Limpiar buffer de salida y terminar ejecución
            ob_end_flush();
            exit();
        }
    } else {
        $_SESSION['error'] = 'No data found';
        header('Location: ../Admin/index_admin.php');
        exit();
    }

} else {
    $_SESSION['error'] = 'Invalid request';
    header('Location: ../Admin/index_admin.php');
    exit();
}
