<?php
header('Content-Type: application/json');
$logPath = $_SERVER['DOCUMENT_ROOT'].'/3Shape_project/Model/Logs/session_messages.json';

// Estructura base vacía
$emptyLog = [
    "success" => [],
    "error" => [],
    "error_message" => [],
    "warnings" => []
];

try {
    // Verificar si el archivo existe
    if (file_exists($logPath)) {
        // Escribir estructura vacía al archivo
        if (file_put_contents($logPath, json_encode($emptyLog, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'message' => 'Records deleted successfully']);
        } else {
            throw new Exception("Failed to write to log file");
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'The log file does not exist']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting records: ' . $e->getMessage()]);
}