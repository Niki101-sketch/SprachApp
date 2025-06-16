<?php
// Start the session
session_start();

// Check if user is already logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header("Location: index2.php");
    exit();
}

$pageTitle = "SprachApp - Professionelles Sprachlernen";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom SprachApp Design -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    
    <!-- Meta Tags für SEO -->
    <meta name="description" content="SprachApp - Die professionelle Plattform zum Erlernen von Sprachen. Interaktive Übungen, Grammatiktrainer und MultiChoice-Tests für effektives Sprachlernen.">
    <meta name="keywords" content="Sprachlernen, Vokabeltrainer, Grammatik, Online Lernen, Sprachkurs, Bildung">
    <meta name="author" content="SprachApp">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="SprachApp - Professionelles Sprachlernen">
    <meta property="og:description" content="Die professionelle Plattform zum Erlernen von Sprachen mit interaktiven Übungen und Tests.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="assets/img/og-image.jpg">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Structured Data for SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "SprachApp",
        "description": "Professionelle Plattform zum Erlernen von Sprachen",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
        "applicationCategory": "EducationApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "EUR"
        }
    }
    </script>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"></noscript>
</head>
<body>

    <!-- Navigation Bar for Landing Page -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand animate-fade-in" href="index.php">
                <i class="bi bi-translate me-2"></i>SprachApp
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Navigation umschalten">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#features">
                            <i class="bi bi-star me-1"></i>Funktionen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">
                            <i class="bi bi-info-circle me-1"></i>Über uns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">
                            <i class="bi bi-envelope me-1"></i>Kontakt
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <a href="login.php" class="btn btn-outline-light me-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Anmelden
                    </a>
                    <a href="registrieren.php" class="btn btn-warning">
                        <i class="bi bi-person-plus me-1"></i>Registrieren
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Skip Navigation Link for Accessibility -->
    <a class="visually-hidden-focusable position-absolute top-0 start-0 p-3 bg-primary text-white text-decoration-none" 
       href="#main-content">Zum Hauptinhalt springen</a>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <div class="container content py-5">
            <!-- Hero Section -->
            <section class="hero-section text-center animate-fade-in" role="banner">
                <div class="position-relative">
                    <h1 class="display-4 fw-bold mb-4">
                        Willkommen zur <span class="text-gradient">SprachApp</span>
                    </h1>
                    <p class="lead mb-5">
                        Die umfassende und professionelle Plattform zum Erlernen und Verbessern Ihrer Sprachkenntnisse.
                        Nutzen Sie modernste Lernmethoden für maximalen Erfolg.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                        <a href="registrieren.php" class="btn btn-warning btn-lg px-5 animate-slide-left">
                            <i class="bi bi-rocket-takeoff me-2"></i>Kostenlos starten
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg px-5 animate-slide-right">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Bereits Mitglied?
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="row mt-5 text-center">
                        <div class="col-md-4">
                            <div class="h4 text-white fw-bold">1000+</div>
                            <small class="text-light">Aktive Nutzer</small>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 text-white fw-bold">50+</div>
                            <small class="text-light">Lerneinheiten</small>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 text-white fw-bold">95%</div>
                            <small class="text-light">Erfolgsrate</small>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Features Section -->
            <section id="features" class="my-5" role="region" aria-labelledby="features-heading">
                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <h2 id="features-heading" class="display-5 fw-bold mb-3">Unsere Premium-Funktionen</h2>
                        <p class="text-muted fs-5">
                            Entdecken Sie, warum SprachApp die erste Wahl für erfolgreiches Sprachlernen ist
                        </p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-box h-100">
                            <div class="feature-icon text-center">
                                <i class="bi bi-collection-fill"></i>
                            </div>
                            <h3 class="h4">Strukturierte Lerneinheiten</h3>
                            <p>
                                Lernen Sie systematisch mit unseren professionell entwickelten, thematisch organisierten 
                                Lerneinheiten. Jede Einheit ist didaktisch optimiert für maximalen Lernerfolg.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Themenbezogene Lektionen</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Stufenweiser Aufbau</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Adaptive Schwierigkeit</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Fortschrittstracking</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-box h-100">
                            <div class="feature-icon text-center">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <h3 class="h4">KI-Powered Grammatiktrainer</h3>
                            <p>
                                Meistern Sie die Grammatik mit unserem intelligenten Trainer. Personalisierte Übungen 
                                passen sich Ihrem Lernstand an und fokussieren auf Ihre Schwachstellen.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Intelligente Fehleranalyse</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Personalisierte Übungen</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Sofortiges Feedback</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Lernpfad-Optimierung</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-box h-100">
                            <div class="feature-icon text-center">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <h3 class="h4">Gamified MultiChoice</h3>
                            <p>
                                Lernen Sie spielerisch mit unserem innovativen MultiChoice-System. Sammeln Sie Punkte, 
                                erreichen Sie Meilensteine und bleiben Sie motiviert.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Punktesystem & Belohnungen</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Leaderboards</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Achievement-System</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Wettkampf-Modus</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Additional Features Section -->
            <section class="my-5 py-5 bg-light rounded-3" role="region" aria-labelledby="additional-features">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <h2 id="additional-features" class="display-6 fw-bold mb-4">
                                Warum SprachApp wählen?
                            </h2>
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-primary rounded-circle p-2 me-3">
                                            <i class="bi bi-lightning-charge text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold">Schneller Fortschritt</h5>
                                            <p class="text-muted mb-0">Wissenschaftlich bewährte Methoden für optimalen Lernerfolg</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success rounded-circle p-2 me-3">
                                            <i class="bi bi-shield-check text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold">Sichere Daten</h5>
                                            <p class="text-muted mb-0">DSGVO-konform und höchste Sicherheitsstandards</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-info rounded-circle p-2 me-3">
                                            <i class="bi bi-people text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold">Community</h5>
                                            <p class="text-muted mb-0">Lernen Sie gemeinsam mit anderen Sprachbegeisterten</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-warning rounded-circle p-2 me-3">
                                            <i class="bi bi-headset text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold">24/7 Support</h5>
                                            <p class="text-muted mb-0">Professioneller Support wann immer Sie ihn brauchen</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="text-center">
                                <div class="bg-gradient-primary rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" 
                                     style="width: 200px; height: 200px;">
                                    <i class="bi bi-graph-up-arrow text-white" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="fw-bold">Über 10.000 erfolgreiche Lernstunden</h4>
                                <p class="text-muted">Täglich vertrauen Hunderte von Nutzern auf SprachApp</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Call to Action Section -->
            <section class="text-center py-5" role="region" aria-labelledby="cta-heading">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-lg bg-gradient-primary text-white">
                            <div class="card-body p-5">
                                <h2 id="cta-heading" class="display-6 fw-bold mb-4">
                                    Bereit für Ihren Lernerfolg?
                                </h2>
                                <p class="fs-5 mb-4">
                                    Schließen Sie sich tausenden zufriedenen Nutzern an und starten Sie noch heute 
                                    Ihre Sprachlern-Reise mit SprachApp.
                                </p>
                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                                    <a href="registrieren.php" class="btn btn-warning btn-lg px-5">
                                        <i class="bi bi-person-plus me-2"></i>Jetzt kostenlos registrieren
                                    </a>
                                    <a href="login.php" class="btn btn-outline-light btn-lg px-5">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Bereits Mitglied? Anmelden
                                    </a>
                                </div>
                                
                                <div class="mt-4">
                                    <small class="opacity-75">
                                        <i class="bi bi-shield-check me-1"></i>Kostenlos • Keine Kreditkarte erforderlich • Jederzeit kündbar
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row py-4 align-items-center">
                <div class="col-md-6 text-md-start text-center mb-3 mb-md-0">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                        <i class="bi bi-translate me-2 fs-4"></i>
                        <div>
                            <h5 class="mb-1 text-white fw-bold">SprachApp</h5>
                            <p class="mb-0 small">&copy; <?php echo date('Y'); ?> SprachApp. Alle Rechte vorbehalten.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <div class="footer-links">
                        <a href="#" class="me-3">
                            <i class="bi bi-shield-check me-1"></i>Datenschutz
                        </a>
                        <a href="#" class="me-3">
                            <i class="bi bi-file-text me-1"></i>Impressum
                        </a>
                        <a href="#" class="me-3">
                            <i class="bi bi-envelope me-1"></i>Kontakt
                        </a>
                        <a href="#">
                            <i class="bi bi-info-circle me-1"></i>Hilfe
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle d-none" 
            style="width: 50px; height: 50px; z-index: 1000;" title="Nach oben scrollen">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Landing Page -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize animations
            initializeAnimations();
            
            // Smooth scrolling for navigation links
            initializeSmoothScrolling();
            
            // Back to top button functionality
            initializeBackToTop();
            
            // Form validation enhancement
            enhanceFormValidation();
            
            // Performance tracking
            trackPerformance();
        });

        function initializeAnimations() {
            // Intersection Observer for scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe elements for animation
            const animateElements = document.querySelectorAll('.feature-box, .card, section');
            animateElements.forEach(el => {
                observer.observe(el);
            });

            // Staggered animation for feature boxes
            const featureBoxes = document.querySelectorAll('.feature-box');
            featureBoxes.forEach((box, index) => {
                setTimeout(() => {
                    box.style.opacity = '1';
                    box.style.transform = 'translateY(0)';
                }, index * 200);
            });
        }

        function initializeSmoothScrolling() {
            const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
            smoothScrollLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        const offsetTop = targetElement.offsetTop - 80; // Account for fixed navbar
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                        
                        // Update URL without jumping
                        history.pushState(null, null, `#${targetId}`);
                    }
                });
            });
        }

        function initializeBackToTop() {
            const backToTopButton = document.getElementById('back-to-top');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.remove('d-none');
                } else {
                    backToTopButton.classList.add('d-none');
                }
            });

            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        function enhanceFormValidation() {
            // Add real-time validation to any forms on the page
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });
                    
                    input.addEventListener('input', function() {
                        if (this.classList.contains('is-invalid')) {
                            validateField(this);
                        }
                    });
                });
            });
        }

        function validateField(field) {
            const isValid = field.checkValidity();
            field.classList.toggle('is-valid', isValid);
            field.classList.toggle('is-invalid', !isValid);
            
            // Show/hide custom error message
            const errorElement = field.parentNode.querySelector('.invalid-feedback');
            if (errorElement) {
                errorElement.style.display = isValid ? 'none' : 'block';
            }
        }

        function trackPerformance() {
            // Track page load performance
            if ('performance' in window) {
                window.addEventListener('load', function() {
                    setTimeout(() => {
                        const perfData = performance.getEntriesByType('navigation')[0];
                        if (perfData) {
                            const loadTime = Math.round(perfData.loadEventEnd - perfData.loadEventStart);
                            console.log(`Page load time: ${loadTime}ms`);
                            
                            // Track slow loading
                            if (loadTime > 3000) {
                                console.warn('Page load time exceeds 3 seconds');
                            }
                        }
                    }, 1000);
                });
            }
        }

        // Utility functions for interaction tracking
        function trackClick(element, category = 'Landing Page') {
            console.log(`Track: ${category} - ${element} clicked`);
            // Replace with your analytics solution
            if (typeof gtag !== 'undefined') {
                gtag('event', 'click', {
                    event_category: category,
                    event_label: element
                });
            }
        }

        // Track CTA button clicks
        document.addEventListener('click', function(e) {
            const button = e.target.closest('a[href*="registrieren"], a[href*="login"]');
            if (button) {
                const action = button.href.includes('registrieren') ? 'register_click' : 'login_click';
                trackClick(action, 'CTA');
            }
        });

        // Easter egg - Konami code
        let konamiCode = [];
        const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65]; // ↑↑↓↓←→←→BA
        
        document.addEventListener('keydown', function(e) {
            konamiCode.push(e.keyCode);
            if (konamiCode.length > konamiSequence.length) {
                konamiCode.shift();
            }
            
            if (konamiCode.length === konamiSequence.length && 
                konamiCode.every((code, index) => code === konamiSequence[index])) {
                showEasterEgg();
            }
        });

        function showEasterEgg() {
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success animate-fade-in';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-emoji-wink me-2 fs-4"></i>
                    <div>
                        <strong>Glückwunsch!</strong><br>
                        <small>Sie haben den Konami-Code entdeckt! 🎉</small>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
            
            // Add some fun effects
            document.body.style.animation = 'rainbow 2s infinite';
            setTimeout(() => {
                document.body.style.animation = '';
            }, 2000);
        }

        // Add rainbow animation for easter egg
        const style = document.createElement('style');
        style.textContent = `
            @keyframes rainbow {
                0% { filter: hue-rotate(0deg); }
                25% { filter: hue-rotate(90deg); }
                50% { filter: hue-rotate(180deg); }
                75% { filter: hue-rotate(270deg); }
                100% { filter: hue-rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        // Progressive Web App hints
        if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('SW registered'))
                    .catch(error => console.log('SW registration failed'));
            });
        }

        // Preload important pages
        function preloadPage(url) {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        }

        // Preload registration and login pages
        setTimeout(() => {
            preloadPage('registrieren.php');
            preloadPage('login.php');
        }, 2000);
    </script>

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "SprachApp",
        "description": "Professionelle Plattform zum Erlernen von Sprachen mit interaktiven Übungen und Tests",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
        "logo": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>/assets/img/logo.png",
        "sameAs": [
            "https://facebook.com/sprachapp",
            "https://twitter.com/sprachapp",
            "https://linkedin.com/company/sprachapp"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+49-xxx-xxxxxxx",
            "contactType": "customer service",
            "availableLanguage": ["German", "English"]
        }
    }
    </script>

</body>
</html>