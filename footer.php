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
                        <a href="#" class="me-3" data-bs-toggle="tooltip" title="Datenschutz">
                            <i class="bi bi-shield-check me-1"></i>Datenschutz
                        </a>
                        <a href="#" class="me-3" data-bs-toggle="tooltip" title="Impressum">
                            <i class="bi bi-file-text me-1"></i>Impressum
                        </a>
                        <a href="#" data-bs-toggle="tooltip" title="Kontakt">
                            <i class="bi bi-envelope me-1"></i>Kontakt
                        </a>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            Version 2.0 | 
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="bi bi-info-circle me-1"></i>Hilfe
                            </a>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Performance und Status Indikatoren (nur für Entwicklung) -->
            <?php if (isset($_GET['debug']) && $_GET['debug'] === 'true'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="border-top border-secondary pt-3 mt-3">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    Ladezeit: <span id="load-time">-</span>ms
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="bi bi-server me-1"></i>
                                    PHP: <?php echo PHP_VERSION; ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="bi bi-memory me-1"></i>
                                    Memory: <?php echo round(memory_get_peak_usage(true) / 1024 / 1024, 2); ?>MB
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    Status: <span class="text-success">Online</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle d-none" 
            style="width: 50px; height: 50px; z-index: 1000;" title="Nach oben scrollen">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional JavaScript for enhanced functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Back to top button functionality
            const backToTopButton = document.getElementById('back-to-top');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.remove('d-none');
                    backToTopButton.classList.add('animate-fade-in');
                } else {
                    backToTopButton.classList.add('d-none');
                    backToTopButton.classList.remove('animate-fade-in');
                }
            });

            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Performance monitoring for debug mode
            <?php if (isset($_GET['debug']) && $_GET['debug'] === 'true'): ?>
            if ('performance' in window) {
                const loadTime = Math.round(performance.now());
                const loadTimeElement = document.getElementById('load-time');
                if (loadTimeElement) {
                    loadTimeElement.textContent = loadTime;
                }
            }
            <?php endif; ?>

            // Keyboard navigation improvements
            document.addEventListener('keydown', function(e) {
                // ESC key to close modals or go back
                if (e.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal.show');
                    if (modals.length === 0 && window.history.length > 1) {
                        // Only go back if no modals are open
                        const backButtons = document.querySelectorAll('[href*="zurück"], [onclick*="history.back"]');
                        if (backButtons.length > 0) {
                            e.preventDefault();
                            window.history.back();
                        }
                    }
                }
            });

            // Enhanced form validation
            const forms = document.querySelectorAll('form[novalidate]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Focus first invalid field
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    form.classList.add('was-validated');
                });
            });

            // Auto-save functionality for forms (optional)
            const autoSaveForms = document.querySelectorAll('[data-auto-save="true"]');
            autoSaveForms.forEach(form => {
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('input', debounce(function() {
                        saveFormData(form);
                    }, 1000));
                });
                
                // Load saved data on page load
                loadFormData(form);
            });

            // AJAX error handling
            window.addEventListener('unhandledrejection', function(event) {
                console.error('Unhandled promise rejection:', event.reason);
                // You can show a user-friendly error message here
            });

            // Connection status monitoring
            window.addEventListener('online', function() {
                showToast('Internetverbindung wiederhergestellt', 'success');
            });

            window.addEventListener('offline', function() {
                showToast('Keine Internetverbindung', 'warning');
            });

            // Accessibility improvements
            improveAccessibility();
        });

        // Utility functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function saveFormData(form) {
            if (!form.id) return;
            
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            try {
                sessionStorage.setItem(`form_${form.id}`, JSON.stringify(data));
            } catch (e) {
                console.warn('Could not save form data:', e);
            }
        }

        function loadFormData(form) {
            if (!form.id) return;
            
            try {
                const savedData = sessionStorage.getItem(`form_${form.id}`);
                if (savedData) {
                    const data = JSON.parse(savedData);
                    Object.keys(data).forEach(key => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input && input.type !== 'password') {
                            input.value = data[key];
                        }
                    });
                }
            } catch (e) {
                console.warn('Could not load form data:', e);
            }
        }

        function clearSavedFormData(formId) {
            try {
                sessionStorage.removeItem(`form_${formId}`);
            } catch (e) {
                console.warn('Could not clear form data:', e);
            }
        }

        function improveAccessibility() {
            // Add ARIA labels to elements that need them
            const buttons = document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])');
            buttons.forEach(button => {
                if (button.textContent.trim() === '') {
                    const icon = button.querySelector('i[class*="bi-"]');
                    if (icon) {
                        const iconClass = Array.from(icon.classList).find(cls => cls.startsWith('bi-'));
                        if (iconClass) {
                            const label = iconClass.replace('bi-', '').replace('-', ' ');
                            button.setAttribute('aria-label', label);
                        }
                    }
                }
            });

            // Ensure all images have alt text
            const images = document.querySelectorAll('img:not([alt])');
            images.forEach(img => {
                img.setAttribute('alt', 'Bild');
            });

            // Add keyboard navigation for custom interactive elements
            const customInteractive = document.querySelectorAll('.vocab-card, .feature-card, .unit-card');
            customInteractive.forEach(element => {
                if (!element.hasAttribute('tabindex')) {
                    element.setAttribute('tabindex', '0');
                }
                
                element.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        element.click();
                    }
                });
            });
        }

        // Global utility functions available to all pages
        window.SprachApp = {
            showToast: showToast,
            showLoadingIndicator: showLoadingIndicator,
            hideLoadingIndicator: hideLoadingIndicator,
            clearSavedFormData: clearSavedFormData,
            debounce: debounce
        };

        // Service Worker registration (for future PWA capabilities)
        if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }

        // Analytics tracking (example)
        function trackEvent(category, action, label = '') {
            // Replace with your analytics solution
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: category,
                    event_label: label
                });
            }
            console.log(`Analytics: ${category} - ${action} - ${label}`);
        }

        // Track navigation clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (link && !link.href.startsWith('javascript:')) {
                const href = link.getAttribute('href');
                if (href.startsWith('#')) {
                    trackEvent('Navigation', 'anchor_click', href);
                } else if (!href.startsWith('http') || href.includes(window.location.hostname)) {
                    trackEvent('Navigation', 'internal_link', href);
                } else {
                    trackEvent('Navigation', 'external_link', href);
                }
            }
        });

        // Error reporting
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            trackEvent('Error', 'javascript_error', e.message);
        });

        // Performance metrics
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData) {
                        trackEvent('Performance', 'page_load_time', Math.round(perfData.loadEventEnd - perfData.loadEventStart));
                    }
                }, 1000);
            });
        }
    </script>

    <!-- Custom page-specific scripts can be included here -->
    <?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($inlineScripts) && !empty($inlineScripts)): ?>
        <script>
            <?php echo $inlineScripts; ?>
        </script>
    <?php endif; ?>

</body>
</html>