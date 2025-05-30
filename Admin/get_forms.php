<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
}

.form-control, .form-select {
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    transform: scale(1.02);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.alert {
    animation: slideIn 0.5s ease-out;
}

.form-check {
    transition: background-color 0.3s ease;
}

.form-check:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php
session_start();
include '../Configuration/Connection.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die('Access denied');
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-lg border-primary h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0 fs-4 fw-bold"><i class="bi bi-input-cursor me-2"></i>Add Custom Fields</h3>
                </div>
                <div class="card-body p-4">
                    <form action="./Admin/add_fields.php" id="FormAddForm" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5 mb-3">Data Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg shadow-sm" name="data_type" required>
                                <option value="">Select Data Type</option>
                                <option value="Date">Date</option>
                                <option value="INTEGER">Integer</option>
                                <option value="VARCHAR(255)">Varchar</option>
                                <option value="BOOLEAN">Boolean</option>
                                <option value="BIGINT">BigInteger</option>
                            </select>
                            <div id="boolean" class="alert alert-warning mt-3 shadow-sm" style="display: none;">
                                Warning: BOOLEAN data type selected. This function may not work correctly
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5 mb-3">Field Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg shadow-sm" id="field_name" name="field_name" placeholder="Ex: equipment_model" required onfocus="showNetworkAddressInfo()" oninput="validateInput()">
                            <div id="networkAddressInfo" class="alert alert-info mt-3 shadow-sm" style="display: none;">
                                The field name should start with a letter and can contain letters, numbers, and underscores. No spaces or special characters allowed.
                            </div>
                            <div id="errorMessage" class="alert alert-danger mt-3 shadow-sm" style="display: none;">
                                The field cannot contain only numbers.
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5 mb-3">Target Table <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg shadow-sm" name="table_name" required>
                                <option value="">Select Table</option>
                                <option value="usuarios_equipos">Users</option>
                                <option value="equipos">Assets</option>
                            </select>
                        </div>
                        <div class="d-grid mt-4">
                            <button id="Addfield" type="submit" onclick="return validateForm()" class="btn btn-primary btn-lg px-5 shadow fw-bold">
                                <i class="bi bi-plus-circle me-2"></i>Add Field
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light py-3">
                    <small class="text-muted fw-semibold">Fields marked with <span class="text-danger">*</span> are required</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card shadow-lg border-primary h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0 fs-4 fw-bold"><i class="bi bi-table me-2"></i>Add Fields to the Main Table</h3>
                </div>
                <div class="card-body p-4">
                    <form action="./Admin/add_fieldsTable.php" method="post" id="FormAddTable" class="needs-validation" novalidate>
                        <h4 class="mb-4 fs-5 fw-semibold">Field Name <span class="text-danger">*</span></h4>
                        <?php
                        try {
                            $sql = "SELECT COLUMN_NAME as Field, DATA_TYPE as Type 
                            FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_NAME IN ('equipos', 'usuarios_equipos')
                            AND COLUMN_NAME NOT IN ('assetname', 'serial_number', 'warranty_enddate', 'expired', 'fk_id', 'fk_assetname',
                            'last_user', 'job_title', 'cedula', 'HeadSet', 'fecha_salida','purchase_country','user_status','status_change') 
                            ORDER BY TABLE_NAME, ORDINAL_POSITION";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute();
                            if ($stmt->rowCount() > 0) {
                                echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 mb-4">';
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<div class="col">';
                                    echo '<div class="form-check form-check-lg border rounded p-3 shadow-sm h-100 d-flex align-items-center">';
                                    echo '<input class="form-check-input me-3" type="checkbox" name="addmfields[]" value="' . htmlspecialchars($row['Field']) . '" id="field_' . htmlspecialchars($row['Field']) . '">';
                                    echo '<label class="form-check-label flex-grow-1" for="field_' . htmlspecialchars($row['Field']) . '">';
                                    echo '<div class="d-flex align-items-center flex-wrap gap-2">';
                                    echo '<span class="badge bg-primary">' . htmlspecialchars($row['Type']) . '</span>';
                                    echo '<span class="text-break">' . htmlspecialchars($row['Field']) . '</span>';
                                    echo '</div>';
                                    echo '</label>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p class="alert alert-info">No data found.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        ?>
                        <div id="dynamicInput"></div>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="d-grid mt-4">
                            <button id="AddfieldTable" type="submit" class="btn btn-primary btn-lg px-5 shadow fw-bold">
                                <i class="bi bi-save me-2"></i>Save Fields
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light py-3">
                    <small class="text-muted fw-semibold">Fields marked with <span class="text-danger">*</span> are required</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Validación del campo de nombre
        function showNetworkAddressInfo() {
            document.getElementById("networkAddressInfo").style.display = "block";
        }

        function validateInput() {
            const field = document.getElementById("field_name");
            const errorMessage = document.getElementById("errorMessage");
            if (/^\d+$/.test(field.value)) {
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }
        }

        function validateForm() {
            const field = document.getElementById("field_name").value;
            if (/^\d+$/.test(field)) {
                alert("The field cannot contain only numbers.");
                return false;
            }
            return true;
        }

        // Mostrar advertencia para tipo BOOLEAN
        document.querySelector('select[name="data_type"]').addEventListener('change', function() {
            if (this.value === 'BOOLEAN') {
                document.getElementById('boolean').style.display = 'block';
            } else {
                document.getElementById('boolean').style.display = 'none';
            }
        });
    });
</script>