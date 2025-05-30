<?php
// Configuración de idioma
$default_lang = 'en';
$available_langs = ['en', 'es', 'pl'];

// Determinar idioma actual
if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $default_lang;
}

// Cargar archivo de idioma
$lang_file = '../lang/' . $_SESSION['lang'] . '.json';
if (file_exists($lang_file)) {
    $lang = json_decode(file_get_contents($lang_file), true);
} else {
    $lang = json_decode(file_get_contents('./lang/en.json'), true);
}

// Función de traducción
function __($key, $lang) {
    return $lang[$key] ?? $key;
}

?>