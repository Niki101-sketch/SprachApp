<?php
// header.php - Gemeinsamer Header für alle Seiten
// Prüfen ob getUserInfo() existiert, falls nicht aus config.php laden
if (!function_exists('getUserInfo')) {
    require_once 'config.php';
}

$userInfo = getUserInfo();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Prüfen ob HTML bereits gestartet wurde
if (!headers_sent()) {
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SprachApp'; ?></title>
    <!-- SOFORTIGER ICON-FIX -->
<style>
/* GROßE, SICHTBARE ICONS */
.bi-translate::before { content: "🌍"; font-size: 1.8em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-house-door::before { content: "🏠"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-collection::before { content: "📚"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-pencil-square::before { content: "✏️"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-check2-circle::before { content: "✅"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-people::before { content: "👥"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-gear::before { content: "⚙️"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-person-circle::before { content: "👤"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-mortarboard::before { content: "🎓"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-person-workspace::before { content: "👨‍🏫"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-shield-check::before { content: "🛡️"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-box-arrow-right::before { content: "🚪"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-box-arrow-in-right::before { content: "🔑"; font-size: 1.6em !important; font-family: "Apple Color Emoji", sans-serif !important; }
.bi-person-plus::before { content: "👤➕"; font-size: 1.5em !important; font-family: "Apple Color Emoji", sans-serif !important; }

/* Alle Icons größer machen */
[class*="bi-"], [class*="fa-"] {
    font-size: 1.4em !important;
    margin-right: 0.4em !important;
    display: inline-block !important;
    min-width: 1.3em !important;
    text-align: center !important;
    vertical-align: middle !important;
}

/* Navigation Icons noch größer */
.navbar-brand [class*="bi-"] { font-size: 2em !important; }
.nav-link [class*="bi-"] { font-size: 1.6em !important; }
.btn [class*="bi-"] { font-size: 1.3em !important; }

/* Mobile noch größer */
@media (max-width: 768px) {
    [class*="bi-"] { font-size: 1.8em !important; }
    .navbar-brand [class*="bi-"] { font-size: 2.5em !important; }
    .nav-link [class*="bi-"] { font-size: 2em !important; }
}
</style>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Multiple Bootstrap Icons CDNs für bessere Verfügbarkeit -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" media="print" onload="this.media='all'">
    
    <!-- Font Awesome als Backup -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
    
    <!-- Custom SprachApp Design -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Icon Fix CSS -->
    <link rel="stylesheet" href="assets/css/icon-fix.css">
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">
    
    <!-- Meta Tags für SEO -->
    <meta name="description" content="SprachApp - Die professionelle Plattform zum Erlernen von Sprachen mit interaktiven Übungen und Tests.">
    <meta name="keywords" content="Sprachlernen, Vokabeltrainer, Grammatik, SprachApp, Online Lernen">
    <meta name="author" content="SprachApp">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'SprachApp - Professionelles Sprachlernen'; ?>">
    <meta property="og:description" content="Die professionelle Plattform zum Erlernen von Sprachen mit interaktiven Übungen und Tests.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"></noscript>
    
    <!-- Inline Icon Fix CSS für sofortige Anwendung -->
    <style>
        /* Sofortiger Icon-Fix */
        .bi-translate::before { content: "🌍"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-house-door::before { content: "🏠"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-collection::before { content: "📚"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-pencil-square::before { content: "✏️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-check2-circle::before { content: "✅"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-people::before { content: "👥"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-gear::before { content: "⚙️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-person-circle::before { content: "👤"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-mortarboard::before { content: "🎓"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-person-workspace::before { content: "👨‍🏫"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-shield-check::before { content: "🛡️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-box-arrow-right::before { content: "🚪"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-arrow-up::before { content: "⬆️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-box-arrow-in-right::before { content: "🔑"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-person-plus::before { content: "👤➕"; font-size: 1.3em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-book::before { content: "📖"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-play-fill::before { content: "▶️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-eye::before { content: "👁️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-eye-slash::before { content: "🙈"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-lock::before { content: "🔒"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-envelope::before { content: "✉️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-send::before { content: "📤"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-arrow-left::before { content: "⬅️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-exclamation-triangle::before { content: "⚠️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-check-circle::before, .bi-check-circle-fill::before { content: "✅"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-x-circle::before { content: "❌"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-info-circle::before { content: "ℹ️"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-star::before { content: "⭐"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        .bi-rocket-takeoff::before { content: "🚀"; font-size: 1.4em !important; font-family: "Apple Color Emoji", sans-serif !important; }
        
        /* Icon-Container für bessere Ausrichtung */
        [class*="bi-"], [class*="fa-"] {
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            margin-right: 0.3em;
            min-width: 1.2em;
        }
        
        /* Spezielle Größen */
        .navbar-brand [class*="bi-"] { font-size: 1.8em !important; margin-right: 0.2em; }
        .nav-link [class*="bi-"] { font-size: 1.3em !important; margin-right: 0.4em; }
        .btn [class*="bi-"] { font-size: 1.1em !important; margin-right: 0.3em; }
        .role-badge [class*="bi-"] { font-size: 1em !important; margin-right: 0.2em; }
        
        /* Responsive */
        @media (max-width: 768px) {
            [class*="bi-"] { font-size: 1.6em !important; }
            .navbar-brand [class*="bi-"] { font-size: 2em !important; }
            .nav-link [class*="bi-"] { font-size: 1.5em !important; }
        }
        
        /* Fallback wenn Emojis nicht funktionieren */
        @supports not (font-family: "Apple Color Emoji") {
            .bi-translate::before { content: "WEB"; }
            .bi-house-door::before { content: "HOME"; }
            .bi-collection::before { content: "BOOK"; }
            .bi-pencil-square::before { content: "EDIT"; }
            .bi-check2-circle::before { content: "OK"; }
            .bi-people::before { content: "USER"; }
            .bi-gear::before { content: "SET"; }
            .bi-person-circle::before { content: "ME"; }
            .bi-box-arrow-right::before { content: "EXIT"; }
        }
    </style>
</head><?php } ?>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand animate-fade-in" href="index2.php">
                <i class="bi bi-translate me-2"></i>SprachApp
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'index2') ? 'active' : ''; ?>" 
                           href="index2.php" aria-current="<?php echo ($currentPage == 'index2') ? 'page' : 'false'; ?>">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'einheiten') ? 'active' : ''; ?>" 
                           href="einheiten.php" aria-current="<?php echo ($currentPage == 'einheiten') ? 'page' : 'false'; ?>">
                            <i class="bi bi-collection me-1"></i>Einheiten
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'miniTest') ? 'active' : ''; ?>" 
                           href="miniTest.php" aria-current="<?php echo ($currentPage == 'miniTest') ? 'page' : 'false'; ?>">
                            <i class="bi bi-pencil-square me-1"></i>Grammatiktrainer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'zuordnen') ? 'active' : ''; ?>" 
                           href="zuordnen.php" aria-current="<?php echo ($currentPage == 'zuordnen') ? 'page' : 'false'; ?>">
                            <i class="bi bi-check2-circle me-1"></i>MultiChoice
                        </a>
                    </li>
                    <li class="nav-item teacher-section">
                        <a class="nav-link <?php echo ($currentPage == 'schueler_verwalten') ? 'active' : ''; ?>" 
                           href="schueler_verwalten.php" aria-current="<?php echo ($currentPage == 'schueler_verwalten') ? 'page' : 'false'; ?>">
                            <i class="bi bi-people me-1"></i>Schüler verwalten
                        </a>
                    </li>
                    <li class="nav-item admin-section">
                        <a class="nav-link <?php echo ($currentPage == 'admin_panel') ? 'active' : ''; ?>" 
                           href="admin_panel.php" aria-current="<?php echo ($currentPage == 'admin_panel') ? 'page' : 'false'; ?>">
                            <i class="bi bi-gear me-1"></i>Admin-Panel
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center flex-wrap">
                    <div class="user-info animate-slide-right">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($userInfo['username']); ?>
                        <span class="role-badge">
                            <?php 
                            $roleIcon = [
                                'schueler' => 'bi-mortarboard',
                                'lehrer' => 'bi-person-workspace',
                                'admin' => 'bi-shield-check'
                            ];
                            $roleText = [
                                'schueler' => 'Schüler',
                                'lehrer' => 'Lehrer',
                                'admin' => 'Admin'
                            ];
                            ?>
                            <i class="<?php echo $roleIcon[$userInfo['role']] ?? 'bi-person'; ?> me-1"></i>
                            <?php echo $roleText[$userInfo['role']] ?? ucfirst($userInfo['role']); ?>
                        </span>
                    </div>
                    <a href="logout.php" class="btn logout-btn ms-3" onclick="return confirm('Möchten Sie sich wirklich abmelden?')">
                        <i class="bi bi-box-arrow-right me-1"></i>Abmelden
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
         style="background: rgba(255, 255, 255, 0.9); z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Lädt...</span>
        </div>
    </div>

    <!-- Skip Navigation Link for Accessibility -->
    <a class="visually-hidden-focusable position-absolute top-0 start-0 p-3 bg-primary text-white text-decoration-none" 
       href="#main-content">Zum Hauptinhalt springen</a>

    <!-- Main Content Container -->
    <main id="main-content" role="main">

    <!-- Icon Fix JavaScript -->
    <script>
        // Sofortige Icon-Fixes
        document.addEventListener('DOMContentLoaded', function() {
            // Icon-Mapping für automatische Ersetzung
            const iconMapping = {
                'bi-translate': '🌍',
                'bi-house-door': '🏠',
                'bi-collection': '📚',
                'bi-pencil-square': '✏️',
                'bi-check2-circle': '✅',
                'bi-people': '👥',
                'bi-gear': '⚙️',
                'bi-person-circle': '👤',
                'bi-mortarboard': '🎓',
                'bi-person-workspace': '👨‍🏫',
                'bi-shield-check': '🛡️',
                'bi-box-arrow-right': '🚪',
                'bi-arrow-up': '⬆️',
                'bi-box-arrow-in-right': '🔑',
                'bi-person-plus': '👤➕',
                'bi-book': '📖',
                'bi-play-fill': '▶️',
                'bi-eye': '👁️',
                'bi-eye-slash': '🙈',
                'bi-lock': '🔒',
                'bi-envelope': '✉️',
                'bi-send': '📤',
                'bi-arrow-left': '⬅️',
                'bi-exclamation-triangle': '⚠️',
                'bi-check-circle': '✅',
                'bi-x-circle': '❌',
                'bi-info-circle': 'ℹ️',
                'bi-star': '⭐',
                'bi-rocket-takeoff': '🚀'
            };

            // Funktion zum Prüfen ob Icon geladen ist
            function hasValidIcon(element) {
                const computed = window.getComputedStyle(element, '::before');
                const content = computed.getPropertyValue('content');
                return content && content !== 'none' && content !== '""' && content !== "''";
            }

            // Icons ersetzen falls nicht geladen
            function replaceIcons() {
                Object.keys(iconMapping).forEach(iconClass => {
                    const elements = document.querySelectorAll('.' + iconClass);
                    elements.forEach(element => {
                        if (!hasValidIcon(element) && !element.dataset.iconFixed) {
                            element.dataset.iconFixed = 'true';
                            element.style.setProperty('--icon-content', '"' + iconMapping[iconClass] + '"');
                            element.classList.add('icon-emoji-fallback');
                        }
                    });
                });
            }

            // CSS für Emoji-Fallback hinzufügen
            const style = document.createElement('style');
            style.textContent = `
                .icon-emoji-fallback::before {
                    content: var(--icon-content) !important;
                    font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif !important;
                    font-size: 1.4em !important;
                    display: inline-block !important;
                    width: 1.2em !important;
                    text-align: center !important;
                    vertical-align: middle !important;
                    margin-right: 0.3em !important;
                }
                .navbar-brand .icon-emoji-fallback::before { font-size: 1.8em !important; }
                .nav-link .icon-emoji-fallback::before { font-size: 1.3em !important; }
                .btn .icon-emoji-fallback::before { font-size: 1.1em !important; }
            `;
            document.head.appendChild(style);

            // Icons beim Laden ersetzen
            replaceIcons();
            
            // Nochmal nach kurzer Verzögerung
            setTimeout(replaceIcons, 500);
            setTimeout(replaceIcons, 1000);

            // Script zum Anzeigen der rollenspezifischen Bereiche
            const role = "<?php echo $userInfo['role']; ?>";
            
            if (role === 'lehrer' || role === 'admin') {
                const teacherSections = document.querySelectorAll('.teacher-section');
                teacherSections.forEach(section => {
                    section.style.display = 'block';
                    section.classList.add('animate-fade-in');
                });
            }
            
            if (role === 'admin') {
                const adminSections = document.querySelectorAll('.admin-section');
                adminSections.forEach(section => {
                    section.style.display = 'block';
                    section.classList.add('animate-fade-in');
                });
            }

            // Navigation Hover Effects
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateY(-2px)';
                    }
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateY(0)';
                    }
                });
            });

            // Loading state management
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    showLoadingIndicator();
                });
            });

            // Smooth scroll for anchor links
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add animation classes to cards
            const cards = document.querySelectorAll('.feature-card, .unit-card, .card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-fade-in');
                }, index * 100);
            });

            // Debug-Funktion für Icons
            window.debugIcons = function() {
                const allIcons = document.querySelectorAll('[class*="bi-"]');
                console.log('Icons found:', allIcons.length);
                allIcons.forEach((icon, i) => {
                    const classes = Array.from(icon.classList).filter(c => c.startsWith('bi-'));
                    console.log(`Icon ${i+1}:`, classes, hasValidIcon(icon) ? 'Loaded' : 'Missing');
                });
            };
        });

        // Loading indicator functions
        function showLoadingIndicator() {
            const indicator = document.getElementById('loading-indicator');
            if (indicator) {
                indicator.classList.remove('d-none');
            }
        }

        function hideLoadingIndicator() {
            const indicator = document.getElementById('loading-indicator');
            if (indicator) {
                indicator.classList.add('d-none');
            }
        }

        // Hide loading indicator when page loads
        window.addEventListener('load', hideLoadingIndicator);

        // Toast notification system
        function showToast(message, type = 'info') {
            const toastContainer = getOrCreateToastContainer();
            const toast = createToast(message, type);
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        function getOrCreateToastContainer() {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'position-fixed top-0 end-0 p-3';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
            return container;
        }

        function createToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show animate-slide-right`;
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            `;
            return toast;
        }

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
        });

        // Performance monitoring
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData && perfData.loadEventEnd - perfData.loadEventStart > 3000) {
                        console.warn('Page load time exceeds 3 seconds');
                    }
                }, 0);
            });
        }
    </script><?php
// header.php - Gemeinsamer Header für alle Seiten
// Prüfen ob getUserInfo() existiert, falls nicht aus config.php laden
if (!function_exists('getUserInfo')) {
    require_once 'config.php';
}

$userInfo = getUserInfo();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Prüfen ob HTML bereits gestartet wurde
if (!headers_sent()) {
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SprachApp'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons - Aktualisierte Version für alle Symbole -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome als Backup für fehlende Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom SprachApp Design -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">
    
    <!-- Meta Tags für SEO -->
    <meta name="description" content="SprachApp - Die professionelle Plattform zum Erlernen von Sprachen mit interaktiven Übungen und Tests.">
    <meta name="keywords" content="Sprachlernen, Vokabeltrainer, Grammatik, SprachApp, Online Lernen">
    <meta name="author" content="SprachApp">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'SprachApp - Professionelles Sprachlernen'; ?>">
    <meta property="og:description" content="Die professionelle Plattform zum Erlernen von Sprachen mit interaktiven Übungen und Tests.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"></noscript>
    
    <!-- CSS Fixes für Icons -->
    <style>
        /* Backup CSS für fehlende Icons */
        .bi-translate::before, .fa-language::before { content: "🌐"; }
        .bi-house-door::before, .fa-home::before { content: "🏠"; }
        .bi-collection::before, .fa-th-large::before { content: "📚"; }
        .bi-pencil-square::before, .fa-edit::before { content: "✏️"; }
        .bi-check2-circle::before, .fa-check-circle::before { content: "✅"; }
        .bi-people::before, .fa-users::before { content: "👥"; }
        .bi-gear::before, .fa-cog::before { content: "⚙️"; }
        .bi-person-circle::before, .fa-user-circle::before { content: "👤"; }
        .bi-mortarboard::before, .fa-graduation-cap::before { content: "🎓"; }
        .bi-person-workspace::before, .fa-chalkboard-teacher::before { content: "👨‍🏫"; }
        .bi-shield-check::before, .fa-shield-alt::before { content: "🛡️"; }
        .bi-box-arrow-right::before, .fa-sign-out-alt::before { content: "🚪"; }
        .bi-arrow-up::before, .fa-arrow-up::before { content: "⬆️"; }
        .bi-star::before, .fa-star::before { content: "⭐"; }
        .bi-info-circle::before, .fa-info-circle::before { content: "ℹ️"; }
        .bi-envelope::before, .fa-envelope::before { content: "✉️"; }
        .bi-box-arrow-in-right::before, .fa-sign-in-alt::before { content: "🔑"; }
        .bi-person-plus::before, .fa-user-plus::before { content: "👤➕"; }
        .bi-rocket-takeoff::before, .fa-rocket::before { content: "🚀"; }
        .bi-collection-fill::before, .fa-th::before { content: "📋"; }
        .bi-mortarboard-fill::before, .fa-graduation-cap::before { content: "🎓"; }
        .bi-trophy-fill::before, .fa-trophy::before { content: "🏆"; }
        .bi-lightning-charge::before, .fa-bolt::before { content: "⚡"; }
        .bi-headset::before, .fa-headphones::before { content: "🎧"; }
        .bi-graph-up-arrow::before, .fa-chart-line::before { content: "📈"; }
        .bi-check-circle-fill::before, .fa-check-circle::before { content: "✅"; }
        
        /* Sicherstellen dass Icons die richtige Größe haben */
        i[class*="bi-"], i[class*="fa-"] {
            display: inline-block;
            width: 1em;
            height: 1em;
            vertical-align: -0.125em;
        }
        
        /* Navbar Brand Icon Fix */
        .navbar-brand i {
            font-size: 1.5rem;
        }
        
        /* Navigation Icon Fix */
        .nav-link i {
            font-size: 1rem;
        }
    </style>
</head><?php } ?>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand animate-fade-in" href="index2.php">
                <i class="bi bi-translate me-2"></i>SprachApp
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'index2') ? 'active' : ''; ?>" 
                           href="index2.php" aria-current="<?php echo ($currentPage == 'index2') ? 'page' : 'false'; ?>">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'einheiten') ? 'active' : ''; ?>" 
                           href="einheiten.php" aria-current="<?php echo ($currentPage == 'einheiten') ? 'page' : 'false'; ?>">
                            <i class="bi bi-collection me-1"></i>Einheiten
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'miniTest') ? 'active' : ''; ?>" 
                           href="miniTest.php" aria-current="<?php echo ($currentPage == 'miniTest') ? 'page' : 'false'; ?>">
                            <i class="bi bi-pencil-square me-1"></i>Grammatiktrainer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'zuordnen') ? 'active' : ''; ?>" 
                           href="zuordnen.php" aria-current="<?php echo ($currentPage == 'zuordnen') ? 'page' : 'false'; ?>">
                            <i class="bi bi-check2-circle me-1"></i>MultiChoice
                        </a>
                    </li>
                    <li class="nav-item teacher-section">
                        <a class="nav-link <?php echo ($currentPage == 'schueler_verwalten') ? 'active' : ''; ?>" 
                           href="schueler_verwalten.php" aria-current="<?php echo ($currentPage == 'schueler_verwalten') ? 'page' : 'false'; ?>">
                            <i class="bi bi-people me-1"></i>Schüler verwalten
                        </a>
                    </li>
                    <li class="nav-item admin-section">
                        <a class="nav-link <?php echo ($currentPage == 'admin_panel') ? 'active' : ''; ?>" 
                           href="admin_panel.php" aria-current="<?php echo ($currentPage == 'admin_panel') ? 'page' : 'false'; ?>">
                            <i class="bi bi-gear me-1"></i>Admin-Panel
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center flex-wrap">
                    <div class="user-info animate-slide-right">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($userInfo['username']); ?>
                        <span class="role-badge">
                            <?php 
                            $roleIcon = [
                                'schueler' => 'bi-mortarboard',
                                'lehrer' => 'bi-person-workspace',
                                'admin' => 'bi-shield-check'
                            ];
                            $roleText = [
                                'schueler' => 'Schüler',
                                'lehrer' => 'Lehrer',
                                'admin' => 'Admin'
                            ];
                            ?>
                            <i class="<?php echo $roleIcon[$userInfo['role']] ?? 'bi-person'; ?> me-1"></i>
                            <?php echo $roleText[$userInfo['role']] ?? ucfirst($userInfo['role']); ?>
                        </span>
                    </div>
                    <a href="logout.php" class="btn logout-btn ms-3" onclick="return confirm('Möchten Sie sich wirklich abmelden?')">
                        <i class="bi bi-box-arrow-right me-1"></i>Abmelden
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
         style="background: rgba(255, 255, 255, 0.9); z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Lädt...</span>
        </div>
    </div>

    <!-- Skip Navigation Link for Accessibility -->
    <a class="visually-hidden-focusable position-absolute top-0 start-0 p-3 bg-primary text-white text-decoration-none" 
       href="#main-content">Zum Hauptinhalt springen</a>

    <!-- Main Content Container -->
    <main id="main-content" role="main">

    <script>
        // Script zum Anzeigen der rollenspezifischen Bereiche
        document.addEventListener('DOMContentLoaded', function() {
            const role = "<?php echo $userInfo['role']; ?>";
            
            // Lehrer-Bereiche anzeigen
            if (role === 'lehrer' || role === 'admin') {
                const teacherSections = document.querySelectorAll('.teacher-section');
                teacherSections.forEach(section => {
                    section.style.display = 'block';
                    section.classList.add('animate-fade-in');
                });
            }
            
            // Admin-Bereiche anzeigen
            if (role === 'admin') {
                const adminSections = document.querySelectorAll('.admin-section');
                adminSections.forEach(section => {
                    section.style.display = 'block';
                    section.classList.add('animate-fade-in');
                });
            }

            // Navigation Hover Effects
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateY(-2px)';
                    }
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateY(0)';
                    }
                });
            });

            // Loading state management
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    showLoadingIndicator();
                });
            });

            // Smooth scroll for anchor links
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add animation classes to cards
            const cards = document.querySelectorAll('.feature-card, .unit-card, .card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-fade-in');
                }, index * 100);
            });
        });

        // Loading indicator functions
        function showLoadingIndicator() {
            const indicator = document.getElementById('loading-indicator');
            if (indicator) {
                indicator.classList.remove('d-none');
            }
        }

        function hideLoadingIndicator() {
            const indicator = document.getElementById('loading-indicator');
            if (indicator) {
                indicator.classList.add('d-none');
            }
        }

        // Hide loading indicator when page loads
        window.addEventListener('load', hideLoadingIndicator);

        // Toast notification system
        function showToast(message, type = 'info') {
            const toastContainer = getOrCreateToastContainer();
            const toast = createToast(message, type);
            toastContainer.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        function getOrCreateToastContainer() {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'position-fixed top-0 end-0 p-3';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
            return container;
        }

        function createToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show animate-slide-right`;
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            `;
            return toast;
        }

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            // Optionally show user-friendly error message
        });

        // Performance monitoring
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData && perfData.loadEventEnd - perfData.loadEventStart > 3000) {
                        console.warn('Page load time exceeds 3 seconds');
                    }
                }, 0);
            });
        }
    </script>