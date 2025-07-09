<link rel="stylesheet" href="/3Shape_project/View/Css/footer.css">
<footer class="container-fluid py-5 bg-light" style="padding-top: 4rem !important; padding-bottom: 4rem !important;">
    <div class="container">
        <div class="row g-5">
            <!-- Sección Legal -->
            <div class="col-lg-4 col-md-6 mb-5">
                <div class="footer-section h-100 p-4 bg-white rounded shadow-sm">
                    <h6 class="text-uppercase fw-bold mb-4 d-flex align-items-center footer-title">
                        <i class="fas fa-balance-scale me-3 fs-5"></i><?php echo __('legal_information', $lang); ?>
                    </h6>
                    <div class="ps-4 ms-2">
                        <p class="mb-3"><i class="far fa-copyright me-2"></i><?= date("Y"); ?> - <?php echo __('all_rights_reserved', $lang); ?>.</p>
                        <p class="text-muted small mb-0"><i class="fas fa-info-circle me-2"></i><?php echo __('notice', $lang); ?>: <?php echo __('notice_info', $lang); ?>.</p>
                    </div>
                </div>
            </div>

            <!-- Sección Contactos -->
            <div class="col-lg-4 col-md-6 mb-5">
                <div class="footer-section h-100 p-4 bg-white rounded shadow-sm">
                    <h6 class="text-uppercase fw-bold mb-4 d-flex align-items-center footer-title">
                        <i class="fas fa-address-card me-3 fs-5"></i><?php echo __('development_team', $lang); ?>
                    </h6>
                    <div class="row g-4">
                        <!-- Daniel Patiño -->
                        <div class="col-md-6">
                            <div class="developer-card ps-3">
                                <p class="mb-2 fw-bold text-primary"><i class="fas fa-user me-2"></i>Daniel Patiño</p>
                                <div class="ms-3 mb-3">
                                    <p class="mb-2"><a href="mailto:Daniel.Cortes@3Shape.com" class="text-decoration-none d-flex align-items-center" target="_blank">
                                        <i class="fas fa-envelope me-2 text-muted"></i>Email
                                    </a></p>
                                    <p class="mb-2"><a href="https://github.com/pati1005986" class="text-decoration-none d-flex align-items-center" target="_blank">
                                        <i class="fab fa-github me-2 text-muted"></i>GitHub
                                    </a></p>
                                    <p class="mb-0"><a href="https://linkedin.com/in/daniel-patiño" class="text-decoration-none d-flex align-items-center" target="_blank">
                                        <i class="fab fa-linkedin me-2 text-muted"></i>LinkedIn
                                    </a></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Killiam González -->
                        <div class="col-md-6">
                            <div class="developer-card ps-3">
                                <p class="mb-2 fw-bold text-primary"><i class="fas fa-user me-2"></i>Killiam González</p>
                                <div class="ms-3 mb-3">
                                    <p class="mb-2"><a href="mailto:Killiam.Cruz@3shape.com" class="text-decoration-none d-flex align-items-center" target="_blank">
                                        <i class="fas fa-envelope me-2 text-muted"></i>Email
                                    </a></p>
                                    <p class="mb-2"><a href="https://github.com/killiam19" class="text-decoration-none d-flex align-items-center" target="_blank">
                                        <i class="fab fa-github me-2 text-muted"></i>GitHub
                                    </a></p>
                                    <p class="mb-0"><a href="https://www.linkedin.com/in/killiam-gonzalez-22b708312/" class="text-decoration-none d-flex align-items-center" target="_blank">
                                        <i class="fab fa-linkedin me-2 text-muted"></i>LinkedIn
                                    </a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-2 border-top">
                        <a href="/3Shape_project/app/View/Int_manual.html" class="text-decoration-none d-flex align-items-center ps-3">
                            <i class="fas fa-book me-2"></i><?php echo __('user_manual', $lang); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sección Técnica -->
            <div class="col-lg-4 col-md-12 mb-5">
                <div class="footer-section h-100 p-4 bg-white rounded shadow-sm">
                    <h6 class="text-uppercase fw-bold mb-4 d-flex align-items-center footer-title">
                        <i class="fas fa-cogs me-3 fs-5"></i><?php echo __('technical_details', $lang); ?>
                    </h6>
                    <div class="ps-4 ms-2">
                        <p class="text-muted mb-3"><i class="fas fa-code-branch me-2"></i><?php echo __('version', $lang); ?>: 6.0.0</p>
                        <p class="text-muted mb-3"><i class="fas fa-calendar-plus me-2"></i><?php echo __('created', $lang); ?>: <?= date("F j, Y", filectime($_SERVER['SCRIPT_FILENAME'])); ?></p>
                        <p class="text-muted mb-0"><i class="fas fa-calendar-check me-2"></i><?php echo __('last_updated', $lang); ?>: <?= date("F j, Y", filemtime($_SERVER['SCRIPT_FILENAME'])); ?></p>
                    </div>
                    <!-- Debug Tools -->
<div class="text-center mt-3">
    <a href="diagnostic.php" target="_blank" class="btn btn-outline-secondary">
        <i class="bi bi-tools me-2"></i><?php echo __('run_diagnostic', $lang); ?><!-- Run Diagnostic -->
    </a>
    <?php
    $current_url = $_SERVER['REQUEST_URI'];
    $debug_param = (strpos($current_url, '?') !== false) ? '&debug=1' : '?debug=1';
    $debug_url = $current_url . $debug_param;
    echo '<a href="' . htmlspecialchars($debug_url) . '" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-bug me-2"></i>Debug Mode
          </a>';
    ?>
</div>
                </div>
            </div>
        </div>
    </div>
</footer>