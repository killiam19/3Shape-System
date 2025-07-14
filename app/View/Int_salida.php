<?php
session_start();
// Configuración de idioma
include '../View/Fragments/idioma.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/button-styles.css">
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link href="../Configuration/JQuery/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../View/Css/Form_salida.css">
    <center><title><?php echo __('record_departure_date', $lang); ?></title></center>
    <style>
        #FORMoutput {
            background: #2c3644 !important;
            color: #fff !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18) !important;
            padding: 2.5rem 2.5rem 2rem 2.5rem !important;
            max-width: 520px;
            margin: 0 auto;
        }
        #totalC {
            color: #fff !important;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        .subrayado {
            width: 80px;
            height: 4px;
            background: #4a90e2;
            border-radius: 2px;
            margin: 0 auto 2.2rem auto;
        }
        .form-label, h4 {
            color: #fff !important;
            font-weight: 600;
        }
        .form-control, .select2-container--default .select2-selection--single {
            background: #fff !important;
            color: #222 !important;
        }
        .btn-secondary {
            background: #6c757d !important;
            border: none;
        }
        .btn-secondary:hover {
            background: #495057 !important;
        }
        .btn-danger {
            background: #e74c3c !important;
            border: none;
        }
        .btn-danger:hover {
            background: #c0392b !important;
        }
    </style>
</head>

<body style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 100vh;">
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div class="col-12 d-flex justify-content-center">
                <div id="FORMoutput">
                    <form id="updateForm" action="../Controller/act_salida.php" method="post">
                        <center>
                            <h1 id="totalC" class="text-center fw-bold"><?php echo __('record_departure', $lang); ?></h1>
                            <div class="subrayado"></div>
                        </center>
                        <div class="mb-4">
                            <h4 class="mb-2"><?php echo __('name_person', $lang); ?></h4>
                            <?php
                            include "../Configuration/Connection.php";
                            $sql = "SELECT * FROM vista_equipos_usuarios
                                WHERE last_user NOT IN ('Stock', 'No user')
                                AND user_status = 'Active User'
                                AND last_user NOT REGEXP '^[0-9]+$';";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            try {
                                if ($stmt->rowCount() > 0) {
                                    echo '<select id="equipoSelect" class="form-select shadow-sm select2" name="equipo" aria-label="Default select example" required>';
                                    echo '<option value="0" selected>Select a person</option>';
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . htmlspecialchars($row['assetname']) . '">' . htmlspecialchars($row['last_user']) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p class="text-center text-light">No data found</p>';
                                }
                            } catch (PDOException $e) {
                                echo '<p class="text-center text-light">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                            }
                            ?>
                        </div>
                        <div class="mb-4">
                            <h4 class="mb-2"><?php echo __('departure_date', $lang); ?></h4>
                            <input class="shadow-sm form-control" type="date" id="fechaSalida" name="fechaSalida" autocomplete="off" required data-validate="true">
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-secondary me-2">
                                <?php echo __('update', $lang); ?> <i class="fas fa-upload"></i>
                            </button>
                            <a href="../../index.php#adjustment" class="btn btn-danger ms-2">
                                <?php echo __('return', $lang); ?> <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </form>
                    <script>
                        $(document).ready(function() {
                            $('#equipoSelect').select2({
                                placeholder: "Select a person",
                                allowClear: true,
                                width: '100%',
                                language: {
                                    noResults: function() {
                                        return 'No results found';
                                    }
                                },
                                escapeMarkup: function(markup) {
                                    return markup;
                                }
                            });
                        });
                        // Validacion para campos vacios
                        document.getElementById('updateForm').addEventListener('submit', function(event) {
                            const select = $('#equipoSelect').select2('data')[0];
                            let errorMessage = "Please fix the following errors: \n";
                            let haserror = false;
                            if (!select || select.id === '0') {
                                errorMessage += "- Please Select a person before updating: \n";
                                haserror = true;
                            }
                            if (haserror) {
                                event.preventDefault();
                                alert(errorMessage);
                            } else {
                                if (!confirm('Are you sure you want to update this data?')) {
                                    event.preventDefault();
                                }
                            }
                        });
                        // Fecha por defecto hoy
                        document.addEventListener('DOMContentLoaded', function() {
                            const garantiaInput = document.getElementById('fechaSalida');
                            const today = new Date().toISOString().split('T')[0];
                            garantiaInput.value = today;
                            garantiaInput.addEventListener('blur', function(e) {
                                if (!garantiaInput.value) {
                                    garantiaInput.classList.add('is-invalid');
                                } else {
                                    garantiaInput.classList.remove('is-invalid');
                                }
                            });
                            document.getElementById('updateForm').addEventListener('submit', function(event) {
                                if (garantiaInput.classList.contains('is-invalid')) {
                                    event.preventDefault();
                                    alert('Please enter a valid date.');
                                }
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
    <script src="../View/Js/dark-mode-toggle-new.js"></script>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script src="../Configuration/JQuery/select2.min.js"></script>
<?php include './Fragments/footer.php'; ?>
</body>
</html>