<?php
// einheiten.php - Einheiten-Übersicht
require_once 'config.php';

// Benutzerinformationen abrufen
$userInfo = getUserInfo();

// PDO Datenbankverbindung
$pdo = getPDOConnection();

// Units aus der Datenbank laden
$stmt = $pdo->prepare("
    SELECT 
        u.unitid,
        u.unitname,
        COUNT(vg.gvocabid) as vocab_count
    FROM unit u
    LEFT JOIN vocabgerman vg ON u.unitid = vg.unitid
    GROUP BY u.unitid, u.unitname
    ORDER BY u.unitid
");
$stmt->execute();
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "SprachApp - Einheiten";

// Header einbinden
include 'header.php';
?>

<body>
   

    <!-- Skip Navigation Link for Accessibility -->
    <a class="visually-hidden-focusable position-absolute top-0 start-0 p-3 bg-primary text-white text-decoration-none" 
       href="#main-content">Zum Hauptinhalt springen</a>

    <!-- Main Content Container -->
    <main id="main-content" role="main">
        <div class="container content">
            <div class="welcome-box animate-fade-in">
                <h2><i class="bi bi-collection me-2"></i>Verfügbare Lerneinheiten</h2>
                <p>Wählen Sie eine Einheit aus, um mit dem strukturierten Lernen zu beginnen. Jede Einheit enthält thematisch organisierte Vokabeln und Übungen.</p>
            </div>
            
            <?php if (empty($units)): ?>
                <div class="alert alert-info animate-slide-left" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Keine Einheiten verfügbar</strong><br>
                    Zurzeit sind keine Lerneinheiten verfügbar. Bitte wenden Sie sich an Ihren Administrator.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($units as $index => $unit): ?>
                        <div class="col-xl-4 col-lg-6 col-md-6">
                            <div class="unit-card animate-fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="unit-header">
                                    <h5><i class="bi bi-book me-2"></i><?php echo htmlspecialchars($unit['unitname']); ?></h5>
                                </div>
                                <div class="unit-body">
                                    <div class="text-center mb-4">
                                        <div class="display-6 fw-bold text-primary"><?php echo $unit['vocab_count']; ?></div>
                                        <p class="text-muted mb-0">
                                            Vokabel<?php echo $unit['vocab_count'] != 1 ? 'n' : ''; ?> verfügbar
                                        </p>
                                    </div>
                                    
                                    <div class="progress mb-4" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo min(100, ($unit['vocab_count'] / 50) * 100); ?>%" 
                                             aria-valuenow="<?php echo $unit['vocab_count']; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="50">
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <?php if ($unit['vocab_count'] > 0): ?>
                                            <a href="karteikarten.php?unit=<?php echo $unit['unitid']; ?>" 
                                               class="btn btn-primary">
                                                <i class="bi bi-play-fill me-2"></i>Lernen starten
                                            </a>
                                            <div class="btn-group" role="group" aria-label="Lernmodi">
                                                <a href="miniTest.php?unit=<?php echo $unit['unitid']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil me-1"></i>Test
                                                </a>
                                                <a href="zuordnen.php?unit=<?php echo $unit['unitid']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-check2-circle me-1"></i>Quiz
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="bi bi-exclamation-triangle me-2"></i>Keine Vokabeln
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Statistiken -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-gradient-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-graph-up me-2"></i>Lern-Statistiken
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <div class="h3 text-primary fw-bold"><?php echo count($units); ?></div>
                                            <small class="text-muted">Verfügbare Einheiten</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <div class="h3 text-success fw-bold">
                                                <?php echo array_sum(array_column($units, 'vocab_count')); ?>
                                            </div>
                                            <small class="text-muted">Gesamt Vokabeln</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <div class="h3 text-info fw-bold">
                                                <?php echo round(array_sum(array_column($units, 'vocab_count')) / max(1, count($units))); ?>
                                            </div>
                                            <small class="text-muted">Ø Vokabeln pro Einheit</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <div class="h3 text-warning fw-bold">0%</div>
                                            <small class="text-muted">Fortschritt gesamt</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        // Zusätzliche JavaScript-Funktionalität für Einheiten
        document.addEventListener('DOMContentLoaded', function() {
            // Script zum Anzeigen der rollenspezifischen Bereiche
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

            // Hover-Effekte für Unit-Cards
            const unitCards = document.querySelectorAll('.unit-card');
            unitCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Tooltips für Progress Bars
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.setAttribute('title', `Fortschritt: ${width}`);
            });

            // Smooth scroll für interne Links
            const internalLinks = document.querySelectorAll('a[href^="#"]');
            internalLinks.forEach(link => {
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
        });

        // Funktion zum Tracken von Unit-Auswahlen
        function trackUnitSelection(unitId, unitName, action) {
            console.log(`Unit ${action}: ${unitName} (ID: ${unitId})`);
            // Hier könnte Analytics-Code eingefügt werden
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: 'Units',
                    event_label: unitName,
                    value: unitId
                });
            }
        }

        // Event Listener für Unit-Links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href*="karteikarten.php"], a[href*="miniTest.php"], a[href*="zuordnen.php"]');
            if (link) {
                const url = new URL(link.href);
                const unitId = url.searchParams.get('unit');
                const unitCard = link.closest('.unit-card');
                const unitName = unitCard ? unitCard.querySelector('h5').textContent.trim() : 'Unknown';
                
                let action = 'unknown';
                if (link.href.includes('karteikarten.php')) action = 'flashcards_start';
                else if (link.href.includes('miniTest.php')) action = 'test_start';
                else if (link.href.includes('zuordnen.php')) action = 'quiz_start';
                
                trackUnitSelection(unitId, unitName, action);
            }
        });
    </script>
</body>
</html>