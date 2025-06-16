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
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome als Backup -->
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
        /* Icon Fixes mit Emojis als Fallback */
        .bi-translate::before { content: "🌐"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-house-door::before { content: "🏠"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-collection::before { content: "📚"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-pencil-square::before { content: "✏️"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-check2-circle::before { content: "✅"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-people::before { content: "👥"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-gear::before { content: "⚙️"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-person-circle::before { content: "👤"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-mortarboard::before { content: "🎓"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-person-workspace::before { content: "👨‍🏫"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-shield-check::before { content: "🛡️"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-box-arrow-right::before { content: "🚪"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-book::before { content: "📖"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-play-fill::before { content: "▶️"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-exclamation-triangle::before { content: "⚠️"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-info-circle::before { content: "ℹ️"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        .bi-graph-up::before { content: "📈"; font-family: "Apple Color Emoji", sans-serif; font-size: 1.4em; }
        
        /* Allgemeine Icon Größen - deutlich vergrößert */
        i[class*="bi-"], i[class*="fa-"] {
            display: inline-block;
            width: 1.6em !important;
            height: 1.6em !important;
            vertical-align: -0.125em;
            margin-right: 0.4em !important;
            font-size: 1.4em !important;
        }
        
        /* Spezifische Bereiche noch größer */
        .navbar-brand i { 
            font-size: 2.2rem !important; 
            width: 1.8em !important;
            height: 1.8em !important;
        }
        .nav-link i { 
            font-size: 1.6rem !important; 
            width: 1.4em !important;
            height: 1.4em !important;
        }
        .btn i { 
            font-size: 1.3rem !important; 
            width: 1.2em !important;
            height: 1.2em !important;
        }
        
        /* User Info Styling */
        .user-info {
            display: flex;
            align-items: center;
            color: #fff;
            font-weight: 500;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
        }
        
        .role-badge i {
            font-size: 1rem !important;
            width: 1em !important;
            height: 1em !important;
            margin-right: 0.3em !important;
        }
        
        /* Mobile noch größer */
        @media (max-width: 768px) {
            i[class*="bi-"], i[class*="fa-"] {
                font-size: 1.8em !important;
                width: 1.8em !important;
                height: 1.8em !important;
            }
            .navbar-brand i { 
                font-size: 2.5rem !important; 
            }
            .nav-link i { 
                font-size: 2rem !important; 
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
                padding: 0.3rem 0.8rem;
            }
            
            .role-badge {
                margin-top: 0.2rem;
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<?php } ?>

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

                    <li class="nav-item teacher-section" style="display: none;">
                        <a class="nav-link <?php echo ($currentPage == 'teacherdashboard') ? 'active' : ''; ?>" 
                        href="teacherdashboard.php" aria-current="<?php echo ($currentPage == 'teacherdashboard') ? 'page' : 'false'; ?>">
                            <i class="bi bi-people me-1"></i>TeacherDashboard
                        </a>
                    </li>

                    <li class="nav-item admin-section" style="display: none;">
                        <a class="nav-link <?php echo ($currentPage == 'admin_panel') ? 'active' : ''; ?>" 
                           href="admin_panel.php" aria-current="<?php echo ($currentPage == 'admin_panel') ? 'page' : 'false'; ?>">
                            <i class="bi bi-gear me-1"></i>Admin-Panel
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="user-info animate-slide-right me-3">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($userInfo['username']); ?>
                        <span class="role-badge ms-2">
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
                    <a href="logout.php" class="btn logout-btn" onclick="return confirm('Möchten Sie sich wirklich abmelden?')">
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

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
    </script>