<?php
ob_start();
session_start();
require_once('../Model/fpdf.php');
include "../Configuration/Connection.php";

// Guardar los datos del formulario en la sesión para el PDF
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Si hay datos del activo en sesión, actualizarlos con lo enviado en el formulario
    if (isset($_SESSION['asset_data']) && is_array($_SESSION['asset_data'])) {
        foreach ($_POST as $key => $value) {
            $_SESSION['asset_data'][$key] = $value;
        }
    }
    
    // Guardar las firmas si fueron enviadas
    if (isset($_POST['signature'])) {
        $_SESSION['signature_data'] = $_POST['signature'];
    }
    
    if (isset($_POST['vobo_signature'])) {
        $_SESSION['vobo_signature_data'] = $_POST['vobo_signature'];
    }
    
    // Redirigir al script que genera el PDF
    header("Location: ../Controller/act_indv_sal.php");
    exit;
} else {
    // Si no es una solicitud POST, redirigir al inicio
    header("Location: ../index.php");
    exit;
}
