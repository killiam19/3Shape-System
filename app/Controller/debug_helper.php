<?php
/**
 * Debug Helper Functions
 * 
 * This file contains helper functions for debugging the application.
 */

/**
 * Logs a debug message to a file
 * 
 * @param mixed $data The data to log
 * @param string $label Optional label for the log entry
 * @return void
 */
function debug_log($data, $label = '') {
    $logFile = __DIR__ . '/../logs/debug.log';
    
    // Create logs directory if it doesn't exist
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    // Format the log entry
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}]";
    
    if (!empty($label)) {
        $entry .= " [{$label}]";
    }
    
    if (is_array($data) || is_object($data)) {
        $entry .= " " . print_r($data, true);
    } else {
        $entry .= " " . $data;
    }
    
    // Write to log file
    file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND);
}

/**
 * Displays debug information in the browser
 * 
 * @param mixed $data The data to display
 * @param string $label Optional label for the debug output
 * @param bool $die Whether to stop execution after displaying debug info
 * @return void
 */
function debug_display($data, $label = '', $die = false) {
    echo '<div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px; padding:15px; margin:15px 0; font-family:monospace;">';
    
    if (!empty($label)) {
        echo '<h4 style="margin-top:0; color:#0d6efd;">' . htmlspecialchars($label) . '</h4>';
    }
    
    echo '<pre style="margin-bottom:0;">';
    if (is_array($data) || is_object($data)) {
        print_r($data);
    } else {
        echo htmlspecialchars($data);
    }
    echo '</pre>';
    echo '</div>';
    
    if ($die) {
        die();
    }
}

/**
 * Checks if debug mode is enabled
 * 
 * @return bool True if debug mode is enabled, false otherwise
 */
function is_debug_mode() {
    return isset($_GET['debug']) && $_GET['debug'] == 1;
}

/**
 * Adds a debug button to the page
 * 
 * @return void
 */
function add_debug_button() {
    $current_url = $_SERVER['REQUEST_URI'];
    $debug_param = (strpos($current_url, '?') !== false) ? '&debug=1' : '?debug=1';
    $debug_url = $current_url . $debug_param;
    
    echo '<a href="' . htmlspecialchars($debug_url) . '" class="btn btn-outline-secondary action-button ms-2">
            <i class="bi bi-bug"></i> Modo Debug
          </a>';
}
