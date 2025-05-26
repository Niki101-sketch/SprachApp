<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    $_SESSION['err'] = "Bitte melden Sie sich an, um auf diese Seite zuzugreifen.";
    header("Location: login.php");
    exit();
}

include "header.php";
?>


    <div class="container content">
        <div class="welcome-box">
            <h2>Willkommen zurück, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Wählen Sie unten eine der Übungsoptionen aus, um Ihre Sprachkenntnisse zu verbessern.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-header">
                        <h5>Einheiten üben</h5>
                    </div>
                    <div class="feature-body">
                        <p>Lernen Sie mit thematisch organisierten Lerneinheiten, die speziell auf Ihr Niveau zugeschnitten sind.</p>
                        <ul>
                            <li>Themenbasierte Lektionen</li>
                            <li>Interaktive Übungen</li>
                            <li>Fortschrittsverfolgung</li>
                        </ul>
                    </div>
                    <div class="feature-footer">
                        <a href="einheiten.php" class="btn btn-primary w-100">Zu den Einheiten</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-header">
                        <h5>Grammatiktrainer</h5>
                    </div>
                    <div class="feature-body">
                        <p>Verbessern Sie Ihre Grammatikkenntnisse mit gezielten Übungen zu Zeiten, Präpositionen und mehr.</p>
                        <ul>
                            <li>Personalisierte Übungen</li>
                            <li>Direktes Feedback</li>
                            <li>Verschiedene Schwierigkeitsgrade</li>
                        </ul>
                    </div>
                    <div class="feature-footer">
                        <a href="miniTest.php" class="btn btn-primary w-100">Grammatik üben</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-header">
                        <h5>MultiChoice</h5>
                    </div>
                    <div class="feature-body">
                        <p>Testen Sie Ihr Wissen mit unterhaltsamen Multiple-Choice-Fragen zu Vokabeln und Sprachverständnis.</p>
                        <ul>
                            <li>Vielfältige Fragetypen</li>
                            <li>Punktesystem</li>
                            <li>Lernstatistiken</li>
                        </ul>
                    </div>
                    <div class="feature-footer">
                        <a href="konjugationstrainer.php" class="btn btn-primary w-100">MultiChoice starten</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bereich nur für Lehrer -->
        <div class="teacher-section role-section">
            <div class="role-header teacher-header">
                <h4 class="mb-0">Lehrer-Bereich</h4>
            </div>
            <div class="role-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="role-feature">
                            <h5>Schülerverwaltung</h5>
                            <p>Verwalten Sie Ihre Schüler, sehen Sie deren Fortschritte ein und erstellen Sie personalisierte Übungen.</p>
                            <a href="schueler_verwalten.php" class="btn btn-info mt-2">Schüler verwalten</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="role-feature">
                            <h5>Übungen erstellen</h5>
                            <p>Erstellen Sie eigene Übungen und Tests für Ihre Kurse und Schüler.</p>
                            <a href="uebungen_erstellen.php" class="btn btn-info mt-2">Übungen erstellen</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bereich nur für Admins -->
        <div class="admin-section role-section">
            <div class="role-header admin-header">
                <h4 class="mb-0">Administrator-Bereich</h4>
            </div>
            <div class="role-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="role-feature">
                            <h5>Benutzerverwaltung</h5>
                            <p>Verwalten Sie alle Benutzerkonten der Plattform.</p>
                            <a href="benutzer_verwalten.php" class="btn btn-danger mt-2">Benutzer verwalten</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="role-feature">
                            <h5>Inhalte verwalten</h5>
                            <p>Bearbeiten und verwalten Sie Lerneinheiten und Übungen.</p>
                            <a href="inhalte_verwalten.php" class="btn btn-danger mt-2">Inhalte verwalten</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="role-feature">
                            <h5>System-Einstellungen</h5>
                            <p>Konfigurieren Sie die Plattform und sehen Sie Systemstatistiken ein.</p>
                            <a href="system_einstellungen.php" class="btn btn-danger mt-2">Einstellungen</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<?php include "footer.php"; ?>