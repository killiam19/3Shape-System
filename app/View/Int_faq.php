<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de idioma
$default_lang = 'en';
$available_langs = ['en', 'es', 'pl'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $available_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $default_lang;
}

$lang_file = '../lang/' . $_SESSION['lang'] . '.json';
if (file_exists($lang_file)) {
    $lang = json_decode(file_get_contents($lang_file), true);
} else {
    $lang = json_decode(file_get_contents('../lang/en.json'), true);
}

function __($key, $lang) {
    return $lang[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('faq', $lang); ?> - <?php echo __('page_title', $lang); ?></title>
    <link rel="shortcut icon" href="../Configuration/3shape-intraoral-logo.png" type="image/x-icon">
    
    <!-- CSS Dependencies -->
    <link href="../Configuration/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Configuration/bootstrap/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../View/Css/dashboard.css">
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/button-styles.css">
    
    <style>
        .faq-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .faq-search {
            margin-bottom: 2rem;
        }
        .faq-category {
            margin-bottom: 2rem;
        }
        .faq-item {
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
        .faq-question {
            padding: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fa;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .faq-answer {
            padding: 1rem;
            display: none;
            border-top: 1px solid #dee2e6;
        }
        .faq-answer.active {
            display: block;
        }
        .faq-icon {
            transition: transform 0.3s ease;
        }
        .faq-question.active .faq-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="faq-container">
        <div class="text-center mb-4">
            <h1><?php echo __('faq', $lang); ?></h1>
            <p class="text-muted"><?php echo __('frequently_asked_questions', $lang); ?></p>
        </div>

        <!-- Search Box -->
        <div class="faq-search">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="faqSearch" placeholder="<?php echo __('search_faq', $lang); ?>">
            </div>
        </div>

        <!-- FAQ Categories -->
        <div class="faq-categories">
            <!-- General Questions -->
            <div class="faq-category">
                <h3 class="mb-3"><?php echo __('general_questions', $lang); ?></h3>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_what_is_system', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_what_is_system_answer', $lang); ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_how_to_access', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_how_to_access_answer', $lang); ?>
                    </div>
                </div>
            </div>

            <!-- Asset Management -->
            <div class="faq-category">
                <h3 class="mb-3"><?php echo __('asset_management', $lang); ?></h3>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_how_register_asset', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_how_register_asset_answer', $lang); ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_how_change_status', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_how_change_status_answer', $lang); ?>
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div class="faq-category">
                <h3 class="mb-3"><?php echo __('reports', $lang); ?></h3>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_how_generate_report', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_how_generate_report_answer', $lang); ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_what_reports_available', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_what_reports_available_answer', $lang); ?>
                    </div>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div class="faq-category">
                <h3 class="mb-3"><?php echo __('troubleshooting', $lang); ?></h3>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_forgot_password', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_forgot_password_answer', $lang); ?>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo __('faq_contact_support', $lang); ?></span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?php echo __('faq_contact_support_answer', $lang); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../Configuration/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle FAQ items
            const questions = document.querySelectorAll('.faq-question');
            questions.forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    const icon = question.querySelector('.faq-icon');
                    
                    question.classList.toggle('active');
                    answer.classList.toggle('active');
                });
            });

            // Search functionality
            const searchInput = document.getElementById('faqSearch');
            const faqItems = document.querySelectorAll('.faq-item');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 