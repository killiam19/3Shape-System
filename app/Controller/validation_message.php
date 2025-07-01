<?php
date_default_timezone_set('America/Bogota');
// Función optimizada para mostrar alertas

function displayAlert($type, $content, $isList = false) {
    ob_start();
    ?>
    <div class="alert alert-<?= $type ?> animate-slide-in">
        <div class="alert-progress"></div>
        <?php if($isList && is_array($content)): ?>
            <div class="alert-scroll-container">
                <ul>
                    <?php foreach($content as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <?= htmlspecialchars($content) ?>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

$alerts = [];
$tempMessages = []; // Array to temporarily store session messages

// Manejar todos los tipos de mensajes
$error_message = isset($_COOKIE['error_message']) ? $_COOKIE['error_message'] : null;
if ($error_message) {
    $alerts[] = displayAlert('danger', $error_message);
    setcookie('error_message', '', time() - 3600, '/');
    $tempMessages['error_message'] = $error_message; // Store message before unsetting
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
if ($error) {
    $alerts[] = displayAlert('danger', $error);
    $tempMessages['error'] = $error; // Store message before unsetting
    unset($_SESSION['error']);
}

$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
if ($success) {
    $alerts[] = displayAlert('success', $success);
    $tempMessages['success'] = $success; // Store message before unsetting
    unset($_SESSION['success']);
}

$warnings = !empty($_SESSION['warnings']) ? $_SESSION['warnings'] : null;
if ($warnings) {
    $alerts[] = displayAlert('warning', $warnings, true);
    $tempMessages['warnings'] = $warnings; // Store message before unsetting
    unset($_SESSION['warnings']);
}

$logPath = $_SERVER['DOCUMENT_ROOT'].'/3Shape_project/app/Model/Logs/session_messages.json';

// Leer registro existente
$existingLog = [];
if (file_exists($logPath)) {
    $existingLog = json_decode(file_get_contents($logPath), true) ?: [];
}

// Fusionar nuevos mensajes con el registro
foreach ($tempMessages as $type => $content) {
    if ($type === 'warnings') {
        // Fusionar arrays de advertencias
        $existingLog[$type] = array_merge($existingLog[$type] ?? [], $content);
    } else {
        // Agregar mensajes simples con timestamp
        $existingLog[$type][] = [
            'message' => $content,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Guardar registro actualizado
file_put_contents($logPath, json_encode($existingLog, JSON_PRETTY_PRINT));

// Mostrar todas las alertas acumuladas
echo implode('', $alerts);
?>

<style>
/* Estilos modernos para alertas */
.alert {
    position: relative;
    margin: 0.75rem 0;
    padding: 1rem;
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.alert-danger {
    background-color: #fef2f2;
    color: #991b1b;
    border-left: 3px solid #dc2626;
}

.alert-success {
    background-color: #f0f9ff;
    color: #075985;
    border-left: 3px solid #0ea5e9;
}

.alert-warning {
    background-color: #fffbeb;
    color: #92400e;
    border-left: 3px solidrgb(217, 157, 6);
}

.animate-slide-in {
    animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1), fadeIn 0.5s ease;
}

.alert.fade-out {
    opacity: 0;
    transform: translateX(100%) scale(0.95);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.alert-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 4px;
    background: linear-gradient(to right, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
    width: 100%;
    transform-origin: left;
    animation: progress 5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%) scale(0.95); opacity: 0; }
    to { transform: translateX(0) scale(1); opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes progress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}

/* Optimización de rendimiento */
@media (prefers-reduced-motion: no-preference) {
    .alert {
        will-change: transform, opacity;
    }
}

.alert-scroll-container {
    max-height: 200px;
    overflow-y: auto;
    margin: 10px 0;
    padding-right: 8px;
    border-radius: 6px;
}

/* Scrollbar moderno */
.alert-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.alert-scroll-container::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
}

.alert-scroll-container::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
    transition: background 0.3s ease;
}

.alert-scroll-container::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.3);
}

.alert-scroll-container ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.alert-scroll-container li {
    padding: 8px 12px;
    line-height: 1.5;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    transition: background-color 0.2s ease;
    border-radius: 4px;
}

.alert-scroll-container li:hover {
    background-color: rgba(255,255,255,0.05);
}

.alert-scroll-container li:last-child {
    border-bottom: none;
}
</style>

<script>
// Función asíncrona para desvanecer alertas
async function autoDismissAlert(alertElement, duration = 5000) {
    // Esperar el tiempo de duración
    await new Promise(resolve => setTimeout(resolve, duration));
    
    // Añadir clase para animación de salida
    alertElement.classList.add('fade-out');
    
    // Esperar a que termine la transición
    await new Promise(resolve => {
        alertElement.addEventListener('transitionend', resolve, {once: true});
    });
    
    // Eliminar el elemento del DOM
    alertElement.remove();
}

// Inicializar para todas las alertas al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert').forEach(alert => {
        autoDismissAlert(alert);
    });
});
</script>
