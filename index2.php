<?php
// Datenbankverbindung
include 'connection.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}

// Session starten
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    $_SESSION['err'] = "Bitte melden Sie sich an, um auf diese Seite zuzugreifen.";
    header("Location: login.php");
    exit();
}

// Benutzerinformationen aus der Session holen
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Arial, Helvetica, sans-serif;
        }
        
        .navbar {
            background-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .nav-link {
            font-weight: 600;
            text-align: center;
        }
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        
        .content {
            flex: 1;
            padding: 2rem 0;
        }
        
        .welcome-box {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #0d6efd;
        }
        
        .welcome-box h2 {
            color: #0d6efd;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .feature-header {
            background-color: #0d6efd;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .feature-header h5 {
            margin: 0;
            font-weight: bold;
            font-size: 1.25rem;
        }
        
        .feature-body {
            padding: 1.5rem;
            flex: 1;
        }
        
        .feature-body ul {
            padding-left: 1.2rem;
            margin-bottom: 0;
        }
        
        .feature-body li {
            margin-bottom: 0.5rem;
        }
        
        .feature-footer {
            padding: 0 1.5rem 1.5rem;
        }
        
        .role-section {
            background-color: white;
            border-radius: 8px;
            margin-top: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }
        
        .role-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .teacher-header {
            background-color: #17a2b8;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .admin-header {
            background-color: #dc3545;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .role-body {
            padding: 1.5rem;
        }
        
        .role-feature {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 6px;
            height: 100%;
            border: 1px solid #e9ecef;
        }
        
        .btn {
            border-radius: 4px;
            font-weight: bold;
            padding: 0.5rem 1.5rem;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .user-info {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-right: 1rem;
            color: white;
        }
        
        .role-badge {
            background-color: white;
            color: #0d6efd;
            font-weight: bold;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
        
        .logout-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
        }
        
        .logout-btn:hover {
            background-color: white;
            color: #0d6efd;
        }
        
        footer {
            margin-top: auto;
            padding: 1rem 0;
            background-color: #212529;
            color: white;
            text-align: center;
        }
        
        footer a {
            color: #f8f9fa;
            text-decoration: none;
            margin: 0 0.5rem;
        }
        
        footer a:hover {
            color: white;
            text-decoration: underline;
        }
        
        /* Admin & Teacher sections hidden by default */
        .admin-section, .teacher-section {
            display: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .user-info {
                margin-bottom: 0.5rem;
                margin-right: 0;
                display: block;
                text-align: center;
            }
            
            .logout-btn {
                display: block;
                width: 100%;
                text-align: center;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index2.php">SprachApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index2.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="einheiten.php">Einheiten</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="miniTest.php">Grammatiktrainer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="zuordnen.php">MultiChoice</a>
                    </li>
                    <li class="nav-item teacher-section">
                        <a class="nav-link" href="schueler_verwalten.php">Schüler verwalten</a>
                    </li>
                    <li class="nav-item admin-section">
                        <a class="nav-link" href="admin_panel.php">Admin-Panel</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center flex-wrap">
                    <span class="user-info">
                        <?php echo htmlspecialchars($username); ?>
                        <span class="role-badge"><?php echo htmlspecialchars($role); ?></span>
                    </span>
                    <a href="logout.php" class="btn logout-btn">Abmelden</a>
                </div>
            </div>
        </div>
    </nav>

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
                        <a href="zuordnen.php" class="btn btn-primary w-100">MultiChoice starten</a>
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
                            <a href="teacherdashboard.php" class="btn btn-info mt-2">Schüler verwalten</a>
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
    
    <footer>
        <div class="container">
            <div class="row py-3">
                <div class="col-md-6 text-md-start text-center mb-2 mb-md-0">
                    <p class="mb-0">&copy; 2025 SprachApp. Alle Rechte vorbehalten.</p>
                </div>
                <div class="col-md-6 text-md-end text-center">
                    <a href="#">Datenschutz</a>
                    <a href="#">Impressum</a>
                    <a href="#">Kontakt</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Script zum Anzeigen der rollenspezifischen Bereiche
        document.addEventListener('DOMContentLoaded', function() {
            var role = "<?php echo $role; ?>";
            
            if (role === 'lehrer' || role === 'admin') {
                // Lehrer-Bereiche anzeigen
                var teacherSections = document.querySelectorAll('.teacher-section');
                for (var i = 0; i < teacherSections.length; i++) {
                    teacherSections[i].style.display = 'block';
                }
            }
            
            if (role === 'admin') {
                // Admin-Bereiche anzeigen
                var adminSections = document.querySelectorAll('.admin-section');
                for (var i = 0; i < adminSections.length; i++) {
                    adminSections[i].style.display = 'block';
                }
            }
        });
    </script>
</body>
</html>