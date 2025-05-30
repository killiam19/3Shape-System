<?php
if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('Archivo no especificado');
}

$filename = basename($_GET['file']);
$filepath = sys_get_temp_dir() . '/' . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// Verificar que el archivo sea temporal y reciente (menos de 5 minutos)
if (time() - filemtime($filepath) > 300) {
    unlink($filepath);
    http_response_code(410);
    exit('El archivo ha expirado');
}

// Servir el PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="documento.pdf"');
header('Cache-Control: no-cache, must-revalidate');

readfile($filepath);

// Eliminar el archivo temporal después de servirlo
unlink($filepath);