<?php
session_start();

// Check if there is asset data in the session
if (!isset($_SESSION['asset_data'])) {
    // If no data, redirect to the main page
    header("Location: /3Shape_project/index.php");
    exit();
}

// Configuración de idioma
include '../View/Fragments/idioma.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Capture - Delivery Certificate</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            line-height: 1.8;
            min-height: 100vh;
        }

        .signature-container {
            max-width: 900px;
            margin: 1rem auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .signature-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: #3498db;
            border-radius: 2px;
        }

        .signature-section {
            margin-bottom: 35px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .signature-section:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-3px);
            border-color: #3498db;
        }

        .signature-section h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            letter-spacing: -0.5px;
        }

        .signature-section h2:before {
            content: '✓';
            margin-right: 12px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }

        .signature-pad-container {
            position: relative;
            margin: 25px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background: #fff;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        canvas {
            display: block;
            width: 100%;
            height: 200px;
            border: none;
            border-radius: 10px;
            background-color: #fff;
            transition: all 0.3s ease;
            cursor: crosshair;
        }

        canvas:hover {
            border-color: #3498db;
        }

        .signature-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #2472a4);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.35);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.25);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.2);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }

        .signature-instructions {
            font-size: 0.95rem;
            color: #6c757d;
            margin-top: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(52, 152, 219, 0.05);
            border-left: 4px solid #3498db;
            border-radius: 0 8px 8px 0;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .signature-container {
                margin: 1rem;
                padding: 20px;
            }

            .signature-section {
                padding: 18px;
            }

            .signature-actions {
                flex-direction: column;
                gap: 15px;
            }

            .buttons {
                justify-content: space-between;
            }

            .btn {
                padding: 10px 16px;
                font-size: 14px;
            }
        }

        /* Animation for saving button */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
            }
        }

        #save:hover {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>

<body>
    <div class="signature-container">
        <h1><?php echo __('delivery_certificate', $lang); ?> - <?php echo __('signature_capture', $lang); ?></h1>

        <?php
        //Espacio para la firma del colaborador o empleado al que se le entrega el equipo
        $max_colaboradores = min(count($_SESSION['asset_data']), 10);
        for ($i = 0; $i < $max_colaboradores; $i++): ?>
            <div class="signature-section">
                <h2><?php echo __('employee', $lang); ?> <?php echo __('signature', $lang); ?> - <?php echo $_SESSION['asset_data'][$i]['last_user']; ?></h2>
                <p class="signature-instructions">Please sign in the area below using your mouse or finger on touch devices</p>
                <div class="signature-pad-container">
                    <canvas id="signature-pad-<?php echo $i; ?>"></canvas>
                </div>
                <div class="buttons">
                    <button id="clear-<?php echo $i; ?>" class="btn btn-secondary"><?php echo __('clear_signature', $lang); ?></button>
                    <button id="undo-<?php echo $i; ?>" class="btn btn-danger"><?php echo __('undo', $lang); ?></button>
                </div>
            </div>
        <?php endfor; ?>

        <div class="signature-section">
            <!--Firma de Departamento de IT para aprobar entrega de equipo -->
            <h2><?php echo __('it_department_approval', $lang); ?></h2>
            <p class="signature-instructions">Please sign in the area below as approval from the IT department</p>
            <div class="signature-pad-container">
                <canvas id="vobo-signature-pad"></canvas>
            </div>
            <div class="buttons">
                <button id="clear-vobo" class="btn btn-secondary"><?php echo __('clear_signature', $lang); ?></button>
                <script>
                    document.getElementById('clear-vobo').addEventListener('click', () => {
                        voboSignaturePad.clear();
                    });
                </script>

                <button id="undo-vobo" class="btn btn-danger"><?php echo __('undo', $lang); ?></button>
                <script>
                    document.getElementById('undo-vobo').addEventListener('click', () => {
                        const data = voboSignaturePad.toData();
                        if (data.length > 0) {
                            data.pop(); // remove the last dot or line
                            voboSignaturePad.fromData(data);
                        }
                    });
                </script>
            </div>
        </div>

        <div class="signature-actions">
            <button id="cancel" class="btn btn-secondary" onclick="window.history.back()"><?php echo __('cancel', $lang); ?></button>
            <button id="save" class="btn btn-primary"><?php echo __('save_signatures', $lang); ?></button>
        </div>
    </div>


<script src="Js/signature_pad.umd.min.js"></script>
    <script>
        // Initialize both signature pads with improved configuration
        const signaturePads = [];
        for (let i = 0; i < <?php echo $max_colaboradores; ?>; i++) {
            const canvas = document.getElementById(`signature-pad-${i}`);
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)',
                velocityFilterWeight: 0.7,
                minWidth: 0.5,
                maxWidth: 2.5,
                throttle: 16
            });
            signaturePads.push(signaturePad);

            // Buttons for clearing
            document.getElementById(`clear-${i}`).addEventListener('click', () => {
                signaturePad.clear();
            });

            // Buttons for undoing
            document.getElementById(`undo-${i}`).addEventListener('click', () => {
                const data = signaturePad.toData();
                if (data.length > 0) {
                    data.pop(); // remove the last dot or line
                    signaturePad.fromData(data);
                }
            });
        }

        const voboCanvas = document.getElementById('vobo-signature-pad');
        const voboSignaturePad = new SignaturePad(voboCanvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 0)',
            velocityFilterWeight: 0.7,
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16
        });

        // Function to adjust canvas on high-resolution screens
        function resizeCanvas(canvas, signaturePad) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            // Redraw existing signature if there is one
            if (!signaturePad.isEmpty()) {
                const data = signaturePad.toData();
                signaturePad.clear();
                signaturePad.fromData(data);
            } else {
                signaturePad.clear();
            }
        }

        // Adjust canvas on load and resize
        window.addEventListener('load', () => {
            for (let i = 0; i < signaturePads.length; i++) {
                resizeCanvas(document.getElementById(`signature-pad-${i}`), signaturePads[i]);
            }
            resizeCanvas(voboCanvas, voboSignaturePad);
        });

        window.addEventListener('resize', () => {
            for (let i = 0; i < signaturePads.length; i++) {
                resizeCanvas(document.getElementById(`signature-pad-${i}`), signaturePads[i]);
            }
            resizeCanvas(voboCanvas, voboSignaturePad);
        });

        // Validate before sending
        function validateSignatures() {
            let allFilled = true;
            for (let i = 0; i < signaturePads.length; i++) {
                if (signaturePads[i].isEmpty()) {
                    allFilled = false;
                    break;
                }
            }

            return true;
        }

        // Button to save both signatures
        document.getElementById('save').addEventListener('click', () => {
            if (!validateSignatures()) return;

            // Show processing message
            const saveBtn = document.getElementById('save');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Processing...';
            saveBtn.disabled = true;

            const signatures = signaturePads.map(pad => pad.toDataURL('image/png'));
            const voboDataURL = voboSignaturePad.toDataURL('image/png');

            // Send all signatures to the server
            fetch('../Controller/guardar_firma_entrada.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        signatures: signatures,
                        vobo_signature: voboDataURL
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error in server response');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Redirect to PDF generator
                        window.location.href = '/3Shape_project/Controller/act_indv_ent.php';
                    } else {
                        throw new Error(data.message || 'Error saving signatures');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Error sending signatures: " + error.message);
                    saveBtn.textContent = originalText;
                    saveBtn.disabled = false;
                });
        });

        // Prevent accidental navigation
        window.addEventListener('beforeunload', (e) => {
            if (!signaturePads[0].isEmpty() || !voboSignaturePad.isEmpty()) {
                e.preventDefault();
                e.returnValue = 'You have unsaved signatures. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    </script>
</body>

</html>