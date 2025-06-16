<?php 
// Start the session: must be the first command 
session_start(); 

// Check if the form was submitted
if (isset($_POST['login'])) {
    // Process login form
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $_SESSION['err'] = "Username or password is missing";
    } else {
        $user = $_POST['username'];
        $pass = $_POST['password'];
        
        // Validate input
        if (empty($user) || empty($pass)) {
            $_SESSION['err'] = "Username or password is empty";
        } else {
            // Database connection settings
            $servername = "sql108.infinityfree.com";
            $username = "if0_38905283";
            $password = "ewgjt0aaksuC";
            $dbname = "if0_38905283_sprachapp";
            
            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            // Check connection
            if ($conn->connect_error) {
                $_SESSION['err'] = "Database connection failed: " . $conn->connect_error;
            } else {
                // First try student table
                $found = false;
                
                // Check in student table
                $sql = "SELECT studentid, studentname, password FROM student WHERE studentname = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user);
                $stmt->execute();
                
                if (!$stmt->error) {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        if (password_verify($pass, $row['password'])) {
                            $_SESSION['authenticated'] = true;
                            $_SESSION['username'] = $user;
                            $_SESSION['role'] = 'schueler';
                            $_SESSION['userid'] = $row['studentid'];
                            $found = true;
                        }
                    }
                }
                $stmt->close();
                
                // If not found in student table, check teacher table
                if (!$found) {
                    $sql = "SELECT teacherid, teachername, password FROM teacher WHERE teachername = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $user);
                    $stmt->execute();
                    
                    if (!$stmt->error) {
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            if (password_verify($pass, $row['password'])) {
                                $_SESSION['authenticated'] = true;
                                $_SESSION['username'] = $user;
                                $_SESSION['role'] = 'lehrer';
                                $_SESSION['userid'] = $row['teacherid'];
                                $found = true;
                            }
                        }
                    }
                    $stmt->close();
                }
                
                // If not found in teacher table, check admin table
                if (!$found) {
                    $sql = "SELECT adminid, adminname, password FROM admin WHERE adminname = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $user);
                    $stmt->execute();
                    
                    if (!$stmt->error) {
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            if (password_verify($pass, $row['password'])) {
                                $_SESSION['authenticated'] = true;
                                $_SESSION['username'] = $user;
                                $_SESSION['role'] = 'admin';
                                $_SESSION['userid'] = $row['adminid'];
                                $found = true;
                            }
                        }
                    }
                    $stmt->close();
                }
                
                if ($found) {
                    // Redirect to index2.php instead of index.php
                    header("Location: index2.php");
                    exit();
                } else {
                    // Login failed
                    $_SESSION['err'] = "Ungültiger Benutzername oder Passwort";
                }
                
                // Close connection
                $conn->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden - SprachApp</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom SprachApp Design -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    
    <!-- Meta Tags -->
    <meta name="description" content="Melden Sie sich bei SprachApp an und setzen Sie Ihr Sprachlernen fort.">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Preload fonts -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"></noscript>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="60" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="40" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            z-index: -1;
        }
    </style>
</head>
<body>
    <!-- Loading Indicator -->
    <div id="loading-indicator" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
         style="background: rgba(255, 255, 255, 0.9); z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Wird geladen...</span>
        </div>
    </div>

    <div class="login-container animate-fade-in">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <i class="bi bi-translate fs-1 text-primary me-2"></i>
                <h1 class="mb-0">SprachApp</h1>
            </div>
            <p class="text-muted">Willkommen zurück! Melden Sie sich an, um fortzufahren.</p>
        </div>
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['err'])): ?>
            <div class="alert alert-danger d-flex align-items-center animate-slide-left" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?php echo htmlspecialchars($_SESSION['err']); ?></div>
            </div>
            <?php unset($_SESSION['err']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success d-flex align-items-center animate-slide-left" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="loginForm" novalidate>
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="bi bi-person me-1"></i>Benutzername
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control border-start-0" 
                           placeholder="Ihr Benutzername"
                           required 
                           autocomplete="username"
                           aria-describedby="usernameHelp">
                </div>
                <div class="invalid-feedback">
                    Bitte geben Sie Ihren Benutzername ein.
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Passwort
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control border-start-0 border-end-0" 
                           placeholder="Ihr Passwort"
                           required 
                           autocomplete="current-password"
                           aria-describedby="passwordHelp">
                    <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" aria-label="Passwort anzeigen/verbergen">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Bitte geben Sie Ihr Passwort ein.
                </div>
            </div>
            
            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                    <label class="form-check-label small text-muted" for="rememberMe">
                        Angemeldet bleiben
                    </label>
                </div>
                <a href="#" class="small text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    Passwort vergessen?
                </a>
            </div>
            
            <!-- Submit Button -->
            <div class="d-grid mb-4">
                <button type="submit" name="login" class="btn btn-primary btn-lg" id="loginButton">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Anmelden
                </button>
            </div>
            
            <!-- Divider -->
            <div class="text-center mb-4">
                <hr class="my-4">
                <small class="text-muted bg-white px-3">oder</small>
            </div>
            
            <!-- Links -->
            <div class="text-center">
                <p class="mb-3">
                    <span class="text-muted">Noch kein Konto?</span>
                    <a href="registrieren.php" class="text-decoration-none fw-bold">
                        <i class="bi bi-person-plus me-1"></i>Jetzt registrieren
                    </a>
                </p>
                <p class="mb-0">
                    <a href="index.php" class="text-muted text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Zurück zur Startseite
                    </a>
                </p>
            </div>
        </form>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="bi bi-key me-2"></i>Passwort zurücksetzen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">
                        Geben Sie Ihre E-Mail-Adresse ein und wir senden Ihnen einen Link zum Zurücksetzen Ihres Passworts.
                    </p>
                    <form id="forgotPasswordForm">
                        <div class="form-group">
                            <label for="resetEmail" class="form-label">E-Mail-Adresse</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="resetEmail" placeholder="ihre@email.de" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" id="sendResetLink">
                        <i class="bi bi-send me-1"></i>Link senden
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form functionality
            initializeForm();
            
            // Initialize password toggle
            initializePasswordToggle();
            
            // Initialize forgot password functionality
            initializeForgotPassword();
            
            // Focus first input
            document.getElementById('username').focus();
        });

        function initializeForm() {
            const form = document.getElementById('loginForm');
            const submitButton = document.getElementById('loginButton');
            
            // Real-time validation
            const inputs = form.querySelectorAll('input[required]');
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
            
            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                    return;
                }
                
                // Show loading state
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Wird angemeldet...';
                submitButton.disabled = true;
                
                // Show loading indicator
                document.getElementById('loading-indicator').classList.remove('d-none');
            });
        }

        function validateField(field) {
            const isValid = field.checkValidity();
            field.classList.toggle('is-valid', isValid);
            field.classList.toggle('is-invalid', !isValid);
            return isValid;
        }

        function initializePasswordToggle() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            
            toggleButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
                
                // Update aria-label
                this.setAttribute('aria-label', type === 'password' ? 'Passwort anzeigen' : 'Passwort verbergen');
            });
        }

        function initializeForgotPassword() {
            const sendButton = document.getElementById('sendResetLink');
            const emailInput = document.getElementById('resetEmail');
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            
            sendButton.addEventListener('click', function() {
                const email = emailInput.value.trim();
                
                if (!email || !isValidEmail(email)) {
                    emailInput.classList.add('is-invalid');
                    return;
                }
                
                emailInput.classList.remove('is-invalid');
                
                // Simulate sending reset link
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Wird gesendet...';
                this.disabled = true;
                
                setTimeout(() => {
                    modal.hide();
                    showToast('Reset-Link wurde an Ihre E-Mail-Adresse gesendet.', 'success');
                    
                    // Reset button
                    this.innerHTML = '<i class="bi bi-send me-1"></i>Link senden';
                    this.disabled = false;
                    emailInput.value = '';
                }, 2000);
            });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

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
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            `;
            return toast;
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC to close modal
            if (e.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                if (modal) {
                    modal.hide();
                }
            }
            
            // Enter in modal to submit
            if (e.key === 'Enter' && document.getElementById('forgotPasswordModal').classList.contains('show')) {
                e.preventDefault();
                document.getElementById('sendResetLink').click();
            }
        });

        // Auto-fill detection for better UX
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            // Check for autofill after a delay
            setTimeout(() => {
                if (input.value) {
                    input.classList.add('is-valid');
                }
            }, 500);
        });

        // Analytics tracking
        function trackEvent(action, category = 'Login') {
            console.log(`Track: ${category} - ${action}`);
            // Replace with your analytics solution
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: category
                });
            }
        }

        // Track interactions
        document.getElementById('togglePassword').addEventListener('click', () => trackEvent('toggle_password'));
        document.querySelector('[data-bs-target="#forgotPasswordModal"]').addEventListener('click', () => trackEvent('forgot_password_click'));
        document.querySelector('a[href="registrieren.php"]').addEventListener('click', () => trackEvent('register_link_click'));
    </script>

    <!-- Performance monitoring -->
    <script>
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData) {
                        const loadTime = Math.round(perfData.loadEventEnd - perfData.loadEventStart);
                        console.log(`Login page load time: ${loadTime}ms`);
                    }
                }, 1000);
            });
        }
    </script>

</body>
</html>