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
    <link rel="stylesheet" href="../View/Css/Form_cambio.css">

    <center><title>Record of Entry</title></center>
</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center vh-100">
            <div class="col-md-6 shadow p-5 rounded border-0 bg-white">
                <div class="">
                    <div id="FORMinput" class="card-body">
                        <form id="updateForm" action="../Controller/act_entrada.php" method="post">
                            <center>
                <h1 id="totalC" class="text-center mb-5 fw-bold"><?php echo __('change_user_equipment', $lang); ?></h1>
            </center>

                            <!-- Selección del número de serie -->
                            <div class="mb-3">
                                <h4 class="mb-2"><?php echo __('computer_serial', $lang); ?>:</h4>
                                <?php
                                include "../Configuration/Connection.php";

                                try {
                                    // Seleccionar todos los equipos que no estén en uso, los que se encuentran en estado de Stock
                                    $sql = "SELECT * FROM vista_equipos_usuarios
                            WHERE serial_number IS NOT NULL AND serial_number != '' 
                            ORDER BY serial_number ASC;";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="equipoSelect" class="shadow-sm form-select select2" name="serial_number">';
                                        echo '<option value="0" selected>Select a computer</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['serial_number']) . '">' . htmlspecialchars($row['serial_number']) . '</option>';
                                        }

                                        echo '</select>';
                                    } else {
                                        echo '<p>No data found.</p>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                                }
                                ?>
                            </div>

                            <!-- Selección del nombre de la persona -->
                            <br>
                            <div class="mb-3">
                                <h4 class="mb-2"><?php echo __('name_person', $lang); ?> </h4>
                                <?php
                                try {
                                    $sql = "SELECT * FROM vista_equipos_usuarios
                            WHERE last_user NOT IN ('Stock', 'No user')
                            AND user_status = 'Active User'
                            AND last_user NOT REGEXP '^[0-9]+$'
                            ORDER BY last_user ASC
                            ;";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        echo '<select id="equipoSelect2" class="form-select shadow-sm select2" name="assetname">';
                                        echo '<option value="0" selected>Select a person</option>';

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['assetname']) . '">' . htmlspecialchars($row['last_user']) . '</option>';
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<p>No data found</p>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                                }
                                ?>
                            </div>
                            <br>

                            <!-- Botones -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-secondary"><?php echo __('register', $lang); ?>
                                <i class="fas fa-clipboard-check"></i> 
                                </button>                               
                         <a href="../../index.php#adjustment" class="btn btn-danger ms-2"><?php echo __('return', $lang); ?>
                                <i class="fas fa-arrow-left"></i>
                                </a>
                            </div>
                        </form>
                        <script>
                            $(document).ready(function() {
                                $('#equipoSelect, #equipoSelect2').select2({
                                    placeholder: function() {
                                        return $(this).attr('placeholder');
                                    },
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

                            //Validacion de select vacio
                            document.getElementById('updateForm').addEventListener('submit', function(event) {
                                const select1 = $('#equipoSelect').select2('data')[0];
                                const select2 = $('#equipoSelect2').select2('data')[0];

                                let errorMessage = "Please fix the following errors: \n";
                                let haserror = false;

                                // Validar select1
                                if (!select1 || select1.id === '0') {
                                    errorMessage += "- Select a computer.\n";
                                    haserror = true;
                                }

                                // Validar select2
                                if (!select2 || select2.id === '0') {
                                    errorMessage += "- Select a person.\n";
                                    haserror = true;
                                }

                                // Manejar errores
                                if (haserror) {
                                    event.preventDefault(); // Detener el envío del formulario
                                    alert(errorMessage); // Mostrar los errores
                                } else {
                                    // Confirmación si no hay errores
                                    if (!confirm('Are you sure you want to update this data?')) {
                                        event.preventDefault(); // Detener el envío si el usuario no confirma
                                    }
                                }
                            });
                        </script>

                    </div>
                </div>
            </div>
        </div>
        
        <script src="../View/Js/dark-mode-toggle-new.js"></script>                                           
        <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
        <script src="../Configuration/JQuery/select2.min.js"></script>

</body>
<?php include './Fragments/footer.php'; ?>

</html>