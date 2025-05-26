<?php
// Starte Session für Benutzerauthentifizierung
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prüfe, ob Benutzer eingeloggt ist
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Weiterleitung zur Login-Seite
    $_SESSION['err'] = "Bitte melden Sie sich an, um auf diese Seite zuzugreifen.";
    header("Location: login.php");
    exit();
}

// Get user info - wichtig für die Anzeige im Header
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Manuelle Datenbankverbindung
$servername = "sql108.infinityfree.com";
$dbusername = "if0_38905283";
$dbpassword = "ewgjt0aaksuC";
$dbname = "if0_38905283_sprachapp";

// Verbindung mit PDO
try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4", // <-- charset ergänzt
        $dbusername,
        $dbpassword
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}


// Units aus der Datenbank laden
$stmt = $pdo->prepare("
    SELECT 
        u.unitid,
        u.unitname,
        COUNT(DISTINCT vg.gvocabid) as vocab_count
    FROM unit u
    LEFT JOIN vocabgerman vg ON u.unitid = vg.unitid
    GROUP BY u.unitid, u.unitname
    ORDER BY u.unitid
");
$stmt->execute();
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vokabeln laden, wenn eine Unit ausgewählt wurde
$vocabs = [];
$selectedUnitId = isset($_GET['unit']) ? $_GET['unit'] : null;
$selectedUnitName = '';

if ($selectedUnitId) {
    // Name der ausgewählten Unit abrufen
    $unitStmt = $pdo->prepare("SELECT unitname FROM unit WHERE unitid = ?");
    $unitStmt->execute([$selectedUnitId]);
    $unitResult = $unitStmt->fetch(PDO::FETCH_ASSOC);
    $selectedUnitName = $unitResult ? $unitResult['unitname'] : 'Unbekannte Unit';
    
    // Vokabeln für die ausgewählte Unit laden - nur deutsche Wörter
    $vocabStmt = $pdo->prepare("
        SELECT DISTINCT
            vg.gvocabid,
            vg.german_word as germanword
        FROM vocabgerman vg
        WHERE vg.unitid = ?
        ORDER BY vg.german_word
    ");
    $vocabStmt->execute([$selectedUnitId]);
    $vocabs = $vocabStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Für jede deutsche Vokabel alle möglichen englischen Übersetzungen laden
    foreach ($vocabs as &$vocab) {
        $translationStmt = $pdo->prepare("
            SELECT 
                ve.english_word
            FROM vocabmapping vm
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vm.gvocabid = ?
        ");
        $translationStmt->execute([$vocab['gvocabid']]);
        $translations = $translationStmt->fetchAll(PDO::FETCH_COLUMN);
        $vocab['translations'] = $translations;
    }
}

// Verarbeitung des Test-Formulars
$results = [];
$correctCount = 0;
$totalCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test'])) {
    $totalCount = count($_POST['vocab_id']);
    
    foreach ($_POST['vocab_id'] as $index => $vocabId) {
        $userAnswer = trim($_POST['user_answer'][$index]);
        $germanWord = $_POST['german_word'][$index];
        
        // Alle möglichen korrekten Antworten für dieses deutsche Wort holen
        $correctStmt = $pdo->prepare("
            SELECT 
                ve.english_word
            FROM vocabmapping vm
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vm.gvocabid = ?
        ");
        $correctStmt->execute([$vocabId]);
        $correctAnswers = $correctStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Prüfen, ob die Antwort korrekt ist (case-insensitive)
        $isCorrect = false;
        foreach ($correctAnswers as $correctAnswer) {
            if (strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer))) {
                $isCorrect = true;
                break;
            }
        }
        
        if ($isCorrect) {
            $correctCount++;
        }
        
        $results[] = [
            'german' => $germanWord,
            'userAnswer' => $userAnswer,
            'correctAnswers' => $correctAnswers,
            'isCorrect' => $isCorrect
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Grammatiktrainer</title>
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
        
        .vocab-form {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 1rem;
        }
        
        .vocab-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .vocab-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .result-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }
        
        .result-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
        }
        
        .result-correct {
            background-color: #d1e7dd;
        }
        
        .result-incorrect {
            background-color: #f8d7da;
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
        
        .synonyms-info {
            font-size: 0.875rem;
            color: #6c757d;
            font-style: italic;
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
                    <a class="nav-link" href="einheiten.php">Einheiten</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="miniTest.php">Grammatiktrainer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="konjugationstrainer.php">MultiChoice</a>
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
            <h2>Grammatiktrainer</h2>
            <p>Wählen Sie eine Unit aus und testen Sie Ihre Kenntnisse.</p>
        </div>
        
        <?php if (!$selectedUnitId): ?>
            <!-- Units Auswahl -->
            <div class="row g-4">
                <div class="col-12">
                    <h3 class="mb-4">Verfügbare Units</h3>
                </div>
                
                <?php if (empty($units)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Zurzeit sind keine Units verfügbar.
                        </div>
                    </div>
                <?php else: ?>
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
                                    <?php if ($unit['vocab_count'] > 0): ?>
                                        <div class="d-grid">
                                            <a href="miniTest.php?unit=<?php echo $unit['unitid']; ?>" class="btn btn-primary w-100">
                                                Test starten
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-grid">
                                            <button class="btn btn-secondary w-100" disabled>Keine Vokabeln</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Test für ausgewählte Unit anzeigen -->
            <div class="row mb-4">
                <div class="col-12">
                    <a href="miniTest.php" class="btn btn-outline-secondary mb-3">← Zurück zur Unit-Auswahl</a>
                    <h3>Vokabeltest: <?php echo htmlspecialchars($selectedUnitName); ?></h3>
                    
                    <?php if (empty($vocabs)): ?>
                        <div class="alert alert-info mt-3">
                            Diese Unit enthält keine Vokabeln.
                        </div>
                    <?php else: ?>
                        <div class="vocab-form">
                            <p class="mb-4">Geben Sie die richtige Übersetzung für die deutschen Wörter ein:</p>
                            
                            <form method="post" action="">
                                <?php foreach ($vocabs as $index => $vocab): ?>
                                    <div class="vocab-item">
                                        <div class="row align-items-center">
                                            <div class="col-md-4 mb-2 mb-md-0">
                                                <strong><?php echo htmlspecialchars($vocab['germanword']); ?></strong>
                                                <?php if (count($vocab['translations']) > 1): ?>
                                                    <div class="synonyms-info">
                                                        (<?php echo count($vocab['translations']); ?> mögliche Antworten)
                                                    </div>
                                                <?php endif; ?>
                                                <input type="hidden" name="vocab_id[]" value="<?php echo $vocab['gvocabid']; ?>">
                                                <input type="hidden" name="german_word[]" value="<?php echo htmlspecialchars($vocab['germanword']); ?>">
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" name="user_answer[]" class="form-control" 
                                                    placeholder="Geben Sie die Übersetzung ein" required>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="mt-4 d-grid">
                                    <button type="submit" name="submit_test" class="btn btn-primary">Test abschließen</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($results)): ?>
            <!-- Testergebnisse anzeigen -->
            <div class="result-card">
                <h3 class="mb-3">Testergebnis</h3>
                <p class="mb-4">Sie haben <?php echo $correctCount; ?> von <?php echo $totalCount; ?> Vokabeln richtig beantwortet.</p>
                
                <?php foreach ($results as $result): ?>
                    <div class="result-item <?php echo $result['isCorrect'] ? 'result-correct' : 'result-incorrect'; ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <strong><?php echo htmlspecialchars($result['german']); ?></strong>
                            </div>
                            <div class="col-md-3">
                                <span>Ihre Antwort: <?php echo htmlspecialchars($result['userAnswer']); ?></span>
                            </div>
                            <div class="col-md-6">
                                <?php if (!$result['isCorrect']): ?>
                                    <span>Mögliche Antworten: 
                                        <?php echo htmlspecialchars(implode(', ', $result['correctAnswers'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span>Korrekt!</span>
                                    <?php if (count($result['correctAnswers']) > 1): ?>
                                        <div class="synonyms-info">
                                            Weitere mögliche Antworten: 
                                            <?php 
                                            $otherAnswers = array_filter($result['correctAnswers'], function($answer) use ($result) {
                                                return strtolower(trim($answer)) !== strtolower(trim($result['userAnswer']));
                                            });
                                            echo htmlspecialchars(implode(', ', $otherAnswers)); 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-4 d-flex gap-2">
                    <a href="miniTest.php?unit=<?php echo $selectedUnitId; ?>" class="btn btn-primary">Erneut versuchen</a>
                    <a href="miniTest.php" class="btn btn-outline-secondary">Andere Unit wählen</a>
                </div>
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