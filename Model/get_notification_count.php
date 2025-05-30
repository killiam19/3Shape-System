<?php
// File: Model/get_notification_count.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get the last check timestamp from the request
$data = json_decode(file_get_contents('php://input'), true);
$lastCheck = isset($data['lastCheck']) ? (int)$data['lastCheck'] : 0;

// Path to the notifications JSON file
$jsonFilePath = '../Model/Logs/session_messages.json';
$notificationCount = 0;

// Check if file exists
if (file_exists($jsonFilePath)) {
    // Get file modification time to check if it's been updated
    $fileModTime = filemtime($jsonFilePath) * 1000; // Convert to milliseconds
    
    // Read the JSON file
    $logData = json_decode(file_get_contents($jsonFilePath), true);
    
    // Count all types of messages
    foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
        if (!empty($logData[$type])) {
            foreach ($logData[$type] as $entry) {
                // Si hay un timestamp en la entrada
                if (is_array($entry) && isset($entry['timestamp'])) {
                    // Convertir el formato de fecha al timestamp en milisegundos
                    $timestamp = strtotime($entry['timestamp']) * 1000;
                    
                    // Contar si es más reciente que la última vez que el usuario vio las notificaciones
                    if ($timestamp > $lastCheck) {
                        $notificationCount++;
                    }
                } else {
                    // Para entradas sin timestamp, usar el tiempo de modificación del archivo
                    if ($fileModTime > $lastCheck || $lastCheck == 0) {
                        $notificationCount++;
                    }
                }
            }
        }
    }
    
    // Guardar el tiempo actual en el que se consultaron las notificaciones
    $currentTimestamp = time() * 1000; // tiempo actual en milisegundos
}

// Return the count as JSON
echo json_encode([
    'count' => $notificationCount,
    'timestamp' => time() * 1000, // Current time in milliseconds
    'debug' => [
        'lastCheck' => $lastCheck,
        'fileModTime' => $fileModTime ?? 0
    ]
]);
?>