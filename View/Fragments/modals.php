<!-- Admin Modal -->
<?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="adminModalLabel"><?php echo __('admin_tools', $lang); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dynamicForms">
                    <!-- Los formularios se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
    <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 56px; height: 56px;" id="adminButton">
        <i class="fas fa-plus"></i>
    </button>
</div>
<?php endif; ?>

<!-- Process Selection Modal -->
<div id="modalConfirmacion" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; display: none;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);">
        <button id="btnCerrarModal" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
        <p id="modalMensaje" style="color: black;"></p>
        <button id="btnAceptar" class="btn btn-secondary"><?php echo __('option_generate_departure', $lang); ?></button>
        <button id="btnCancelar" class="btn btn-secondary my-2"><?php echo __('option_create_entry', $lang); ?></button>
        <button id="btnOpcion3" class="btn btn-secondary my-2"><?php echo __('option_asset_information', $lang); ?></button>
        <button id="btnQR" class="btn btn-secondary my-2"><?php echo __('option_generate_qr', $lang); ?></button>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="notificationModalLabel">
                    <i class="bi bi-bell-fill me-2"></i><?php echo __('notification_history', $lang); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="notificationFilter" class="form-control mb-3" placeholder="<?php echo __('filter_notifications', $lang); ?>...">
                <div class="notification-container">
                    <?php
                    $jsonFilePath = './Model/Logs/session_messages.json';
                    $alerts = [];
                    $maxMessages = 8;

                    if (file_exists($jsonFilePath)) {
                        $cacheKey = 'notification_' . filemtime($jsonFilePath);
                        $logData = isset($_SESSION[$cacheKey]) ? $_SESSION[$cacheKey] : null;

                        if (!$logData) {
                            $logData = json_decode(file_get_contents($jsonFilePath), true);
                            $_SESSION[$cacheKey] = $logData;
                        }

                        foreach (['success', 'error', 'error_message', 'warnings'] as $type) {
                            if (!empty($logData[$type])) {
                                $count = 0;
                                foreach ($logData[$type] as $entry) {
                                    $message = is_array($entry) ? $entry['message'] : $entry;
                                    $timestamp = is_array($entry) ? " ({$entry['timestamp']})" : '';
                                    $alerts[] = "<div class='notification-item p-3 mb-2 bg-white rounded shadow-sm border-start border-4 border-primary'>{$message} - {$timestamp}</div>";
                                    $count++;
                                    if ($count >= $maxMessages) break;
                                }
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($alerts)): ?>
                        <div class="notification-list">
                            <?= implode('', $alerts) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="bi bi-inbox-fill fs-1 text-muted"></i>
                            <p class="mt-3 text-muted"><?php echo __('no_notifications', $lang); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="clearLogs()">
                    <i class="bi bi-trash-fill me-1"></i>
                    <?php echo __('clean_history', $lang); ?>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>
                    <?php echo __('close', $lang); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Login Modal -->
<div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminLoginModalLabel"><?php echo __('admin_login', $lang); ?> <i class="fas fa-user-alt"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="./Admin/token.php" method="POST">
                    <?php
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    ?>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group mb-3">
                        <input type="password" id="passwordadmin" name="passwordadmin" placeholder="<?php echo __('enter_password', $lang); ?>" class="form-control shadow-lg border border-secondary" required autocomplete="off">
                        <div class="invalid-feedback">Please enter a valid password.</div>
                    </div>
                    <button type="submit" class="btn btn-secondary w-100 border border-secondary"><?php echo __('login', $lang); ?> <i class="fas fa-key"></i></button>
                    <div class="text-center mt-2">
                        <a href="./View/Int_forgot_pasword.php" class="text-decoration-none"><?php echo __('forgot_password', $lang); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Clear logs function
function clearLogs() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '<?php echo __('are_you_sure', $lang); ?>',
            text: "<?php echo __('delete_all_confirm', $lang); ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<?php echo __('yes_delete_all', $lang); ?>',
            cancelButtonText: '<?php echo __('cancel', $lang); ?>'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('./Model/clear_logs.php', {
                    method: 'POST'
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        title: 'Success!',
                        text: data,
                        icon: 'success',
                        confirmButtonColor: '#0d6efd'
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while clearing logs.',
                        icon: 'error',
                        confirmButtonColor: '#0d6efd'
                    });
                });
            }
        });
    } else {
        if (confirm('<?php echo __('are_you_sure', $lang); ?>')) {
            fetch('./Model/clear_logs.php', {
                method: 'POST'
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while clearing logs.');
            });
        }
    }
}

// Show notification modal
function showModal() {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
}

// Admin modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const showAdminModal = document.getElementById('showAdminModal');
    if (showAdminModal) {
        showAdminModal.addEventListener('click', function(event) {
            event.preventDefault();
            const adminLoginModal = new bootstrap.Modal(document.getElementById('adminLoginModal'));
            adminLoginModal.show();
        });
    }

    // Keyboard shortcut for admin login
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && (event.key === 'q' || event.key === 'Q')) {
            event.preventDefault();
            const adminLoginModal = new bootstrap.Modal(document.getElementById('adminLoginModal'));
            adminLoginModal.show();
        }
    });

    // Notification filter
    const notificationFilter = document.getElementById('notificationFilter');
    if (notificationFilter) {
        notificationFilter.addEventListener('keyup', function() {
            const filterValue = this.value.toLowerCase();
            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach(function(notification) {
                const text = notification.textContent.toLowerCase();
                notification.style.display = text.includes(filterValue) ? 'block' : 'none';
            });
        });
    }
});
</script>
