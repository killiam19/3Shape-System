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
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    <script src="../Configuration/JQuery/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="../Configuration/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="./Css/dark-mode.css">
    <link rel="stylesheet" href="../Configuration/JQuery/select2.min.css">
    <link rel="stylesheet" href="../Configuration/JQuery/sweetalert2.min.css">
    <script src="../Configuration/JQuery/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../Configuration/JQuery/all.min.css">
    <link rel="stylesheet" href="../View/Css/Form_salida.css">
    <title><?php echo __('record_departure_date', $lang); ?></title>
    <!--Archivo de vista de proceso de salida de activos -->
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div id="FORMoutput" class="col-md-8 shadow p-5 rounded border border-secondary bg-light">
                <form id="updateForm" action="../Controller/act_salida.php" method="post" class="">
                    <div class="p-5 rounded">
                        <h1 id="totalC" class="text-center mb-5 fw-bold"><?php echo __('record_departure', $lang); ?></h1>

                        <h4 class="my-3"><?php echo __('name_person', $lang); ?>:</h4>
                        <!--Consulta de equipos con condicionales basadas en el User Status y last user-->
                        <?php // Consulta de usuario
                        include "../Configuration/Connection.php";
                        $sql = "SELECT * FROM vista_equipos_usuarios
                        WHERE last_user NOT IN ('Stock', 'No user')
                        AND user_status = 'Active User'
                        AND last_user NOT REGEXP '^[0-9]+$';";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        try {
                            if ($stmt->rowCount() > 0) {
                                echo '<select id="equipoSelect" class="shadow form-select text-center shadow select2" name="equipo" aria-label="Default select example" required>';
                                echo '<option value="" selected disabled>Select a Person</option>';
                                echo '<option value="0" selected>Select a Person</option>';

                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . htmlspecialchars($row['assetname']) . '">' . htmlspecialchars($row['last_user']) . '</option>';
                                }
                                echo '</select><br>';
                            } else {
                                echo '<p class="text-center">No data found</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }

                        ?>
                        <br>
                        <h4 class="my-3"><?php echo __('departure_date', $lang); ?>:</h4>
                        <div class="form-group">
                            <input class="shadow-sm form-control" type="date" id="fechaSalida" name="fechaSalida"
                                autocomplete="off" required data-validate="true">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const garantiaInput = document.getElementById('fechaSalida');

                                // Formatear la fecha actual en formato YYYY-MM-DD para el input type="date"
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
                    <br>
                    <div class="text-center">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-upload"></i> <?php echo __('update', $lang); ?>
                        </button>
                        <a href="../../index.php#adjustment" class="btn btn-danger">
                            <i class="fas fa-arrow-left"></i> <?php echo __('return', $lang); ?>
                        </a>
                    </div>

                    <br>
                </form>
                <!--Script Reciclado en base a la funcion de mensaje de confirmacion del formulario-->
                <script>
                    $(document).ready(function() {
                        $('#equipoSelect').select2({
                            placeholder: "Select a person",
                            allowClear: true,
                            minimumResultsForSearch: 5,
                            width: '100%'
                        });
                    });

                    // Validacion para campos vacios
                    document.getElementById('updateForm').addEventListener('submit', function(event) {
                        const select = $('#equipoSelect').select2('data')[0];
                        let errorMessage = "please fix the following errors: \n";
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
                </script>
            </div>
        </div>
    </div>
    <script src="../View/Js/dark-mode-toggle-new.js"></script>   
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
    <script src="../Configuration/JQuery/select2.min.js"></script>
</body>
<!--Footer-->
<?php include './Fragments/footer.php'; ?>
</html>