<?php
// Agregar este código donde desees mostrar el botón de depuración, 
// por ejemplo, justo después de los botones de acción en index.php

if (!function_exists('add_debug_button')) {
    function add_debug_button() {
        $current_url = $_SERVER['REQUEST_URI'];
        $debug_param = (strpos($current_url, '?') !== false) ? '&debug=1' : '?debug=1';
        $debug_url = $current_url . $debug_param;
        
        echo '<a href="' . htmlspecialchars($debug_url) . '" class="btn btn-outline-secondary action-button ms-2">
                <i class="bi bi-bug"></i> Modo Debug
              </a>';
    }
}

// Llamar a la función para mostrar el botón
add_debug_button();
?>
