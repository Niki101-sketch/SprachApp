<?php
// Datenbankverbindung
include 'connection.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}

// Session starten für Benutzerinformationen
session_start();

// Prüfen, ob Benutzer eingeloggt ist
$isLoggedIn = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
$username = $isLoggedIn ? $_SESSION['username'] : '';
$role = $isLoggedIn ? $_SESSION['role'] : '';

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
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Einheiten</title>
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
        
        .unit-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e9ecef;
            margin-bottom: 1.5rem;
        }
        
        .unit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .unit-header {
            background-color: #0d6efd;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .unit-header h5 {
            margin: 0;
            font-weight: bold;
            font-size: 1.25rem;
        }
        
        .unit-body {
            padding: 1.5rem;
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
                        <a class="nav-link" href="index2.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="einheiten.php">Einheiten</a>
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
            <h2>Verfügbare Units</h2>
            <p>Wählen Sie eine Unit aus, um mit dem Lernen zu beginnen.</p>
        </div>
        
        <?php if (empty($units)): ?>
            <div class="alert alert-info" role="alert">
                Zurzeit sind keine Units verfügbar.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($units as $unit): ?>
                    <div class="col-md-4">
                        <div class="unit-card">
                            <div class="unit-header">
                                <h5><?php echo htmlspecialchars($unit['unitname']); ?></h5>
                            </div>
                            <div class="unit-body">
                                <p class="mb-4">
                                    <?php echo $unit['vocab_count']; ?> Vokabel<?php echo $unit['vocab_count'] != 1 ? 'n' : ''; ?>
                                </p>
                                <div class="d-grid">
                                    <a href="karteikarten.php?unit=<?php echo $unit['unitid']; ?>" class="btn btn-primary w-100">
                                        Lernen starten
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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