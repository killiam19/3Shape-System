<?php
session_start();
include "../Configuration/Connection.php";

// Mostrar mensajes de éxito/error
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}
//Si no se proporciona el nombre del equipo
if (!isset($_GET['assetname'])) {
    echo "No se ha proporcionado un assetname.";
    exit;
}


$assetname = $_GET['assetname'];

// Consulta usando JOIN para obtener datos de ambas tablas
$sql = "
    SELECT *
    FROM equipos
    INNER JOIN usuarios_equipos
    ON equipos.assetname = usuarios_equipos.fk_assetname
    WHERE equipos.assetname = :assetname
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
$stmt->execute();

$asset_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset_data) {
    echo "No se han encontrado datos para el activo especificado.";
    exit;
}

$_SESSION['asset_data'] = $asset_data;
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Estados predefinidos para cada tipo de activo
$asset_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$monitor_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$headset_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$dongle_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];
$celular_status_options = ["Nuevo", "Bueno", "Regular", "Malo", "Dañado", "Perdido", "No Aplica"];

// Fecha predeterminada (hoy)
$fecha_salida = isset($asset_data['fecha_salida']) ? $asset_data['fecha_salida'] : date('d/m/Y');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="./Css/FormsCRUD.css">
    <title>Preview PDF Acta de Salida</title>
    <style>
        .pdf-preview {
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 40px;
            margin: 20px auto;
            max-width: 850px;
            font-family: Arial, sans-serif;
        }
        .header-logo {
            text-align: right;
            margin-bottom: 15px;
        }
        .pdf-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .certificate-text {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .asset-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .asset-table th, .asset-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }
        .asset-table th {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .admin-section {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .admin-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .admin-table {
            width: 70%;
            border-collapse: collapse;
        }
        .admin-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
        }
        .declaration-text {
            font-size: 14px;
            margin: 20px 0;
            line-height: 1.6;
        }
        .signature-section {
            width: 100%;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            border: 1px solid #000;
            width: 45%;
            height: 100px;
            position: relative;
            padding-top: 30px;
            text-align: center;
        }
        .signature-label {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #000;
            background-color: #f5f5f5;
        }
        /* Controles de edición */
        .readonly-field {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
            display: inline-block;
            min-width: 150px;
        }
        .signature-canvas {
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            width: 100%;
            height: 150px;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">Preview - Output Record</h2>
                
                <div class="pdf-preview">
                        <!-- Encabezado del PDF -->
                        <div class="header-logo">
                            <img src="../Configuration/3shape-logo.png" alt="Logo" height="30">
                        </div>
                        <div class="text-left">
                            <strong>Fecha: </strong>
                            <span class="readonly-field"><?php echo htmlspecialchars($fecha_salida); ?></span>
                        </div>
                        <div class="pdf-title">Acta de Salida</div>
                        
                        <!-- Texto del certificado -->
                        <div class="certificate-text">
                            Por medio de la presente se certifica que el/la señor(a); 
                            <span class="readonly-field"><?php echo htmlspecialchars($asset_data['last_user']); ?></span>
                            
                            <?php 
                            $tipo_id = isset($asset_data['Tipo_ID']) ? $asset_data['Tipo_ID'] : 'CC';
                            $tipo_text = "";
                            
                            switch ($tipo_id) {
                                case 'CE':
                                    $tipo_text = "identificado(a) con Cédula de Extranjeria No.";
                                    break;
                                case 'PP':
                                    $tipo_text = "identificado(a) con Pasaporte No.";
                                    break;
                                case 'RC':
                                    $tipo_text = "identificado(a) con Cédula de Residencia No.";
                                    break;  
                                case 'CC':
                                    $tipo_text = "identificado(a) con Cédula de Ciudadanía No.";
                                    break;
                                case 'TI':
                                    $tipo_text = "identificado(a) con Tarjeta de Identidad No.";
                                    break;
                                default:
                                    $tipo_text = "identificado(a) con ID:";
                                    break;
                            }
                            echo $tipo_text;
                            ?>
                            
                            <span class="readonly-field"><?php echo htmlspecialchars($asset_data['cedula'] ?? ''); ?></span>, 
                            procede a desvincularse del cargo:
                            <span class="readonly-field"><?php echo htmlspecialchars($asset_data['job_title'] ?? ''); ?></span>
                            que venía desempeñando, a partir de la fecha <span><?php echo htmlspecialchars($fecha_salida); ?></span>.
                        </div>
                        
                        <div class="certificate-text">
                            Adicionalmente, se declara que está paz y salvo con la organización en cuanto a la entrega de los siguientes activos asignados para el desempeño de sus funciones:
                        </div>
                        
                        <!-- Tabla de activos -->
                        <table class="asset-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Descripción del Activo</th>
                                    <th>Serial</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Computador -->
                                <tr>
                                    <td>1</td>
                                    <td>Computador Personal</td>
                                    <td><?php echo htmlspecialchars($asset_data['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['asset_status']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['asset_observations'] ?? ''); ?></td>
                                </tr>
                                
                                <!-- Monitor (si existe) -->
                                <?php if (!empty($asset_data['Monitor'])): ?>
                                <tr>
                                    <td>2</td>
                                    <td>Monitor</td>
                                    <td><?php echo htmlspecialchars($asset_data['Monitor']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['monitor_status']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['monitor_observations'] ?? ''); ?></td>
                                </tr>
                                <?php endif; ?>
                                
                                <!-- HeadSet (si existe) -->
                                <?php if (!empty($asset_data['HeadSet'])): ?>
                                <tr>
                                    <td><?php echo (!empty($asset_data['Monitor'])) ? '3' : '2'; ?></td>
                                    <td>Head Set</td>
                                    <td><?php echo htmlspecialchars($asset_data['HeadSet']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['headset_status']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['headset_observations'] ?? ''); ?></td>
                                </tr>
                                <?php endif; ?>
                                
                                <!-- Dongle (si existe) -->
                                <?php 
                                $row_num = 2;
                                if (!empty($asset_data['Monitor'])) $row_num++;
                                if (!empty($asset_data['HeadSet'])) $row_num++;
                                ?>
                                <?php if (!empty($asset_data['Dongle'])): ?>
                                <tr>
                                    <td><?php echo $row_num; ?></td>
                                    <td>Dongle</td>
                                    <td><?php echo htmlspecialchars($asset_data['Dongle']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['dongle_status']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['dongle_observations'] ?? ''); ?></td>
                                </tr>
                                <?php endif; ?>
                                
                                <!-- Celular (si existe) -->
                                <?php 
                                if (!empty($asset_data['Dongle'])) $row_num++;
                                ?>
                                <?php if (!empty($asset_data['Celular'])): ?>
                                <tr>
                                    <td><?php echo $row_num; ?></td>
                                    <td>Celular</td>
                                    <td><?php echo htmlspecialchars($asset_data['Celular']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['celular_status']); ?></td>
                                    <td><?php echo htmlspecialchars($asset_data['celular_observations'] ?? ''); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Sección administrativa -->
                        <div class="admin-section">
                            <div class="admin-title">Datos exclusivos Administrativos:</div>
                            <table class="admin-table">
                                <tr>
                                    <td style="width: 40%;">Carne:</td>
                                    <td><?php echo htmlspecialchars($asset_data['Carnet'] ?? 'Pendiente'); ?></td>
                                </tr>
                                <tr>
                                    <td>Llave locker:</td>
                                    <td><?php echo htmlspecialchars($asset_data['LLave'] ?? 'Pendiente'); ?></td>
                                </tr>
                                <tr>
                                    <td>SIM card:</td>
                                    <td><?php echo htmlspecialchars($asset_data['SIMcard'] ?? ''); ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Botón de volver -->
                        <div class="text-center mt-4">
                            <a href="../index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Menu
                            </a>
                        </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts necesarios -->
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
