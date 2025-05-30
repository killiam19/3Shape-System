<?php
$jsonFilePath = '../Model/Logs/session_messages.json';
$alerts = [];
$maxMessages = 8;

if (file_exists($jsonFilePath)) {
    $logData = json_decode(file_get_contents($jsonFilePath), true);
    $messageType = $_GET['message_type'] ?? null;
    $keyword = $_GET['keyword'] ?? null;
    // Uso de caché para el archivo JSON
    $cacheKey = 'notification_' . filemtime($jsonFilePath);
    $logData = isset($_SESSION[$cacheKey]) ? $_SESSION[$cacheKey] : null;

    if (!$logData) {
        $logData = json_decode(file_get_contents($jsonFilePath), true);
        $_SESSION[$cacheKey] = $logData;
    }

    // Process all message types
    foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
        if (!empty($logData[$type]) && ($messageType === null || $messageType === $type)) {
            $alertClass = match ($type) {
                'success' => 'success',
                'error', 'error_message' => 'danger',
                'warnings' => 'warning'
            };

            $filteredEntries = array_filter($logData[$type], function ($entry) use ($keyword) {
                $message = is_array($entry) ? $entry['message'] : $entry;
                return $keyword === null || stripos($message, $keyword) !== false;
            });

            $limitedEntries = array_slice($filteredEntries, 0, $maxMessages);

            foreach ($limitedEntries as $entry) {
                $message = is_array($entry) ? $entry['message'] : $entry;
                $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';

                $alerts[] = "<div class='p-3 mb-2 bg-light shadow-sm'>{$message}{$timestamp}</div>";
            }
        }
    }
}
