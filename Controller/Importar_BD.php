<?php
ob_start(); // Iniciar buffer
session_start();
include('../Configuration/Connection.php');

// Instalar PhpSpreadsheet via Composer: composer require phpoffice/phpspreadsheet
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Validación de archivo incluyendo Excel
function validateFile($file)
{
    $allowedExtensions = ['csv', 'txt', 'xlsx', 'xls'];
    $allowedMimeTypes = [
        'text/csv', 
        'application/vnd.ms-excel', 
        'text/plain',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.ms-excel' // xls
    ];
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

    return in_array($fileExtension, $allowedExtensions) && in_array($file['type'], $allowedMimeTypes);
}

function generateUniqueAssetName($pdo)
{
    $maxAttempts = 5;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $assetname = 'CO-LPT-' . bin2hex(random_bytes(3));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE assetname = ?");
        $stmt->execute([$assetname]);
        if ($stmt->fetchColumn() == 0) {
            return $assetname;
        }
    }
    throw new Exception("Failed to generate a unique assetname after $maxAttempts attempts");
}

function insertData($pdo, $data)
{
    // Valores por defecto
    $data['serial_number'] = preg_replace('/[^\w.-]/', '', $data['serial_number']); // Sanitize serial_number
    $data['last_user'] = preg_replace('/[^\w\s.-]/', '', $data['last_user']); // Sanitize last_user
    $defaults = [
        'Tipo_ID' => 'CC',
        'Dongle' => 0,
        'serial_number' => 0,
        'Carnet' => 'Pendiente',
        'LLave' => 'Pendiente',
        'job_title' => 'unknown',
        'last_user' => 'No user',
        'asset_status' => 'Bueno',
        'asset_observations' => '',
        'headset_status' => 'Bueno',
        'headset_observations' => '',
        'dongle_status' => 'Bueno',
        'dongle_observations' => '',
        'celular_status' => 'Bueno',
        'celular_observations' => '',
        'SIMcard' => '',
        'HeadSet' => '',
        'Celular' => '',
        'fecha_ingreso' => date('Y-m-d') // Valor por defecto para fecha_ingreso (fecha actual)
    ];

    // Combinar solo si el valor en $data está vacío o no existe
    foreach ($defaults as $key => $value) {
        if (!isset($data[$key]) || empty($data[$key])) {
            $data[$key] = $value;
        }
    }

    $assetname = preg_replace('/\s+/u', '', $data['assetname']); // Elimina todo tipo de espacios
    $assetname = strtoupper($assetname);
    $data['assetname'] = $assetname;
    // Generar assetname si está vacío
    if (empty($data['assetname'])) {
        $data['assetname'] = generateUniqueAssetName($pdo);
    } else {
        // Normalización agresiva (espacios + caracteres especiales)
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE assetname = ?");
        $checkStmt->execute([$assetname]);

        if ($checkStmt->fetchColumn() > 0) {
            throw new PDOException("Duplicate Assetname", 1062);
        }

        $data['assetname'] = $assetname; // Actualiza con el valor normalizado
    }

    // Check for duplicate serial_number
    if (!empty($data['serial_number'])) {
        $serialCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE serial_number = ?");
        $serialCheckStmt->execute([$data['serial_number']]);
        if ($serialCheckStmt->fetchColumn() > 0) {
            $warnings[] = "Duplicate Serial Number: " . $data['serial_number'];
            return; // Skip this entry
        }
    }

    // Forzar la insersion de datos
    $data['Dongle'] = $data['Dongle'] ?? 0; // Si es null, usa 0
    $data['Dongle'] = (int)$data['Dongle'];
    $data['job_title'] = $data['job_title'] ?? 'unknown'; // Si es null, usa 'unknown'
    $data['job_title'] = (string)$data['job_title'];
    $data['last_user'] = $data['last_user'] ?? 'No user'; // Si es null, usa 'No user'
    $data['last_user'] = (string)$data['last_user'];

    try {
        $pdo->beginTransaction();
        // Insertar en equipos - Actualizado con todos los campos correctos
        $stmtEquipos = $pdo->prepare("
        INSERT INTO equipos 
        (assetname, serial_number,asset_status, asset_observations,
        HeadSet, headset_status, headset_observations, Dongle, dongle_status, dongle_observations, 
        Celular, celular_status, celular_observations, SIMcard)
        VALUES 
        (:assetname, :serial_number,:asset_status, :asset_observations,
        :HeadSet, :headset_status, :headset_observations, :Dongle, :dongle_status, :dongle_observations,
        :Celular, :celular_status, :celular_observations, :SIMcard)
        ");

        $stmtEquipos->execute([
            ':assetname' => $data['assetname'],
            ':serial_number' => $data['serial_number'],
            ':asset_status' => $data['asset_status'],
            ':asset_observations' => $data['asset_observations'],
            ':HeadSet' => $data['HeadSet'],
            ':headset_status' => $data['headset_status'],
            ':headset_observations' => $data['headset_observations'],
            ':Dongle' => $data['Dongle'],
            ':dongle_status' => $data['dongle_status'],
            ':dongle_observations' => $data['dongle_observations'],
            ':Celular' => $data['Celular'],
            ':celular_status' => $data['celular_status'],
            ':celular_observations' => $data['celular_observations'],
            ':SIMcard' => $data['SIMcard']
        ]);

        // Insertar en usuarios_equipos - Actualizado con los campos correctos y agregando fecha_ingreso
        $stmtUsuarios = $pdo->prepare(
            "INSERT INTO usuarios_equipos 
            (fk_assetname, user_status, last_user, job_title, cedula, 
            Carnet, LLave, Tipo_ID, fecha_salida, fecha_ingreso)
            VALUES 
            (:fk_assetname, :user_status, :last_user, :job_title, :cedula, 
            :Carnet, :LLave, :Tipo_ID, :fecha_salida, :fecha_ingreso)"
        );

        $stmtUsuarios->execute([
            ':fk_assetname' => $data['assetname'],
            ':user_status' => $data['user_status'],
            ':last_user' => $data['last_user'],
            ':job_title' => $data['job_title'],
            ':cedula' => $data['cedula'],
            ':Carnet' => $data['Carnet'] ?? 'Pendiente',
            ':LLave' => $data['LLave'] ?? 'Pendiente',
            ':Tipo_ID' => $data['Tipo_ID'],
            ':fecha_salida' => $data['fecha_salida'],
            ':fecha_ingreso' => $data['fecha_ingreso']
        ]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack(); // Revertir en caso de error
        throw $e;
    }
}

// Nueva función para procesar archivos Excel
function processExcel($pdo, $filePath)
{
    $warnings = [];
    
    try {
        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Obtener todas las filas como array
        $rows = $worksheet->toArray();
        
        if (empty($rows)) {
            throw new Exception("El archivo Excel está vacío.");
        }
        
        // La primera fila son los headers
        $headers = array_shift($rows);
        
        // Normalizar nombres de columnas
        $normalizedHeaders = array_map(function ($header) {
            return strtolower(preg_replace('/[^a-z0-9]/', '', $header));
        }, $headers);
        
        // Mapeo de campos
        include_once $_SERVER['DOCUMENT_ROOT'] . '/3Shape_project/Configuration/Config_map.php';

        // Validar que el array de mapeo existe
        if (!isset($fieldMap) || empty($fieldMap)) {
            throw new Exception("Error: El array fieldMap no está definido en Config_map.php");
        }

        // Crear mapeo de columnas
        $columnMapping = [];
        foreach ($fieldMap as $dbField => $possibleNames) {
            foreach ($possibleNames as $name) {
                $normalizedName = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
                $index = array_search($normalizedName, $normalizedHeaders);
                if ($index !== false) {
                    $columnMapping[$dbField] = $index;
                    break;
                }
            }
        }

        // Campos obligatorios
        $requiredFields = ['serial_number'];
        foreach ($requiredFields as $field) {
            if (!isset($columnMapping[$field])) {
                throw new Exception("Missing required column: " . $fieldMap[$field][0] . " in the Excel file.");
            }
        }

        // Procesar cada fila
        foreach ($rows as $rowIndex => $row) {
            $rowData = [];
            
            foreach ($columnMapping as $field => $index) {
                $value = trim($row[$index] ?? '');

                // Manejo especial para campos numéricos
                switch ($field) {
                    case 'cedula':
                    case 'Dongle':
                        $rowData[$field] = ($value === '') ? null : (int)$value;
                        break;
                    default:
                        $rowData[$field] = $value;
                }
            }

            // Validar campos obligatorios
            foreach ($requiredFields as $field) {
                if (empty($rowData[$field])) {
                    $warnings[] = "Missing required field '{$field}' in row " . ($rowIndex + 2); // +2 porque empezamos desde 0 y saltamos header
                    continue 2;
                }
            }

            $originalAssetName = $rowData['assetname'] ?? '';
            
            // Insertar y validar registros con id único en la columna 'assetname'
            try {
                insertData($pdo, $rowData);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $warnings[] = "Duplicate Log: " . ($originalAssetName ?: 'Assetname') . ' - ' . $rowData['serial_number'];
                } else {
                    $warnings[] = "Error in row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }
        }
        
    } catch (Exception $e) {
        throw new Exception("Error processing Excel file: " . $e->getMessage());
    }
    
    return $warnings;
}

function processCSV($pdo, $filePath)
{
    $file = fopen($filePath, "r");
    $headers = fgetcsv($file);
    $warnings = [];

    // Normalizar nombres de columnas
    $normalizedHeaders = array_map(function ($header) {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $header));
    }, $headers);
    // Mapeo de campos
    include_once $_SERVER['DOCUMENT_ROOT'] . '/3Shape_project/Configuration/Config_map.php';

// Validar que el array de mapeo existe
if (!isset($fieldMap) || empty($fieldMap)) {
    throw new Exception("Error: El array $fieldMap no está definido en Config_map.php");
}

    // Crear mapeo de columnas
    $columnMapping = [];
    foreach ($fieldMap as $dbField => $possibleNames) {
        foreach ($possibleNames as $name) {
            $normalizedName = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
            $index = array_search($normalizedName, $normalizedHeaders);
            if ($index !== false) {
                $columnMapping[$dbField] = $index;
                break;
            }
        }
    }

    // Campos obligatorios
    $requiredFields = ['serial_number'];
    foreach ($requiredFields as $field) {
        if (!isset($columnMapping[$field])) {
            throw new Exception("Missing required column: " . $fieldMap[$field][0] . " in the CSV file.");
        }
    }

    // Procesar filas
    while (($row = fgetcsv($file)) !== false) {
        $rowData = [];
        foreach ($columnMapping as $field => $index) {
            $value = trim($row[$index] ?? '');

            // Manejo especial para campos numéricos
            switch ($field) {
                case 'cedula':
                case 'Dongle':
                    $rowData[$field] = ($value === '') ? null : (int)$value;
                    break;
                default:
                    $rowData[$field] = $value;
            }
        }

        // Validar campos obligatorios
        foreach ($requiredFields as $field) {
            if (empty($rowData[$field])) {
                $warnings[] = "Missing required field '{$field}' in row " . (ftell($file) + 1);
                continue 2;
            }
        }

        $originalAssetName = $rowData['assetname'] ?? '';
        // Insersion y validacion de registros con id unico en la columna 'assetname'
        try {
            insertData($pdo, $rowData);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $warnings[] = "Duplicate Log: " . ($originalAssetName ?: 'Assetname') . ' - ' . $rowData['serial_number'];
            }
        }

        try {
            insertData($pdo, $rowData);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Código de error para duplicados
                $warnings[] = "Duplicate Log: " . $rowData['assetname'];
            } else {
                $warnings[] = "Error in row file " . (ftell($file) + 1) . ": " . $e->getMessage();
            }
        }
    }

    fclose($file);
    return $warnings;
}

function processTXT($pdo, $filePath)
{
    $data = json_decode(file_get_contents($filePath), true);
    $warnings = [];

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decodificando JSON: " . json_last_error_msg());
    }

    // Mismo mapeo de campos que en processCSV
    include_once $_SERVER['DOCUMENT_ROOT'] . '/3Shape_project/Configuration/Config_map.php';

// Validar que el array de mapeo existe
if (!isset($fieldMap) || empty($fieldMap)) {
    throw new Exception("Error: El array $fieldMap no está definido en Config_map.php");
}

    foreach ($data as $record) {
        $rowData = [];

        // Normalizar claves del JSON
        $normalizedRecord = [];
        foreach ($record as $key => $value) {
            $normalizedKey = strtolower(preg_replace('/[^a-z0-9]/', '', $key));
            $normalizedRecord[$normalizedKey] = $value;
        }

        // Mapear campos
        foreach ($fieldMap as $dbField => $possibleNames) {
            foreach ($possibleNames as $name) {
                $normalizedName = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
                if (isset($normalizedRecord[$normalizedName])) {
                    $rowData[$dbField] = trim($normalizedRecord[$normalizedName]);
                    break;
                }
            }
        }

        // Validar campos obligatorios
        $requiredFields = ['serial_number'];
        foreach ($requiredFields as $field) {
            if (empty($rowData[$field])) {
                $warnings[] = "Missing required field '$field' in record";
                continue 2;
            }
        }

        // Generar assetname si es necesario
        if (empty($rowData['assetname'])) {
            try {
                $rowData['assetname'] = generateUniqueAssetName($pdo);
            } catch (Exception $e) {
                $warnings[] = $e->getMessage();
                continue;
            }
        }

        // Insertar registro
        try {
            insertData($pdo, $rowData);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $warnings[] = "Duplicate Record: " . ($rowData['assetname'] ?? 'Unknown');
            } else {
                $warnings[] = "Error in Record: " . $e->getMessage();
            }
        }
    }

    return $warnings;
}

// Procesamiento principal
if (isset($_FILES['Proyeccion_garan']) && $_FILES['Proyeccion_garan']['error'] == 0) {
    try {
        if (!validateFile($_FILES['Proyeccion_garan'])) {
            throw new Exception("Invalid file format. Supported formats: CSV, TXT, XLSX, XLS");
        }

        $filePath = $_FILES['Proyeccion_garan']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['Proyeccion_garan']['name'], PATHINFO_EXTENSION));

        $warnings = [];

        switch ($fileExtension) {
            case 'csv':
                $warnings = processCSV($pdo, $filePath);
                break;
            case 'txt':
                $warnings = processTXT($pdo, $filePath);
                break;
            case 'xlsx':
            case 'xls':
                $warnings = processExcel($pdo, $filePath);
                break;
            default:
                throw new Exception("Unsupported file format: " . $fileExtension);
        }

        $_SESSION['warnings'] = array_slice($warnings, 0, 10); // Mostrar máximo 10 advertencias
        $_SESSION['success'] = "File '" . htmlspecialchars($_FILES['Proyeccion_garan']['name']) . "' processed successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Error uploading file";
}
ob_end_clean(); // Limpiar buffer de salida
header("Location: ../index.php");
exit;