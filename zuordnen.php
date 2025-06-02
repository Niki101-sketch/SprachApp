<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connection.php';
$conn->set_charset("utf8mb4");

// Prüfen, ob Benutzer eingeloggt ist
$isLoggedIn = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$isLoggedIn) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Funktion zur Reparatur von doppelt kodierten UTF-8 Zeichen
function repairUTF8($text) {
    // Häufige doppelt kodierte Zeichen reparieren
    $replacements = [
        'Ã¤' => 'ä',
        'Ã¶' => 'ö', 
        'Ã¼' => 'ü',
        'Ã„' => 'Ä',
        'Ã–' => 'Ö',
        'Ãœ' => 'Ü',
        'ÃŸ' => 'ß',    
        'Ã©' => 'é',
        'Ã¨' => 'è',
        'Ã¡' => 'á',
        'Ã ' => 'à',
        'Ã­' => 'í',
        'Ã¬' => 'ì',
        'Ã³' => 'ó',
        'Ã²' => 'ò',
        'Ãº' => 'ú',
        'Ã¹' => 'ù'
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

// Variablen für Test-Modus
$showTest = false;
$vocabularyPairs = [];
$unitName = "";
$group = 1;

// Prüfen ob Test gestartet werden soll
if (isset($_GET['unit'])) {
    $unitid = intval($_GET['unit']);
    
    // Wenn group Parameter nicht gesetzt ist, starte mit Gruppe 1
    if (isset($_GET['group'])) {
        $group = intval($_GET['group']);
    } else {
        $group = 1;
    }
    
    // Unit-Name holen
    $unitStmt = $conn->prepare("SELECT unitname FROM unit WHERE unitid = ?");
    $unitStmt->bind_param("i", $unitid);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();
    if ($unitRow = $unitResult->fetch_assoc()) {
        $unitName = repairUTF8($unitRow['unitname']);
        
        // Berechne OFFSET für die Vokabeln (max 5 pro Gruppe)
        $offset = ($group - 1) * 5;
        
        // Vokabeln für diese Gruppe holen
        $vocabStmt = $conn->prepare("
            SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
            FROM vocabgerman vg
            JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vg.unitid = ? AND ve.unitid = ?
            ORDER BY vg.gvocabid
            LIMIT 5 OFFSET ?
        ");
        $vocabStmt->bind_param("iii", $unitid, $unitid, $offset);
        $vocabStmt->execute();
        $vocabResult = $vocabStmt->get_result();
        
        while ($row = $vocabResult->fetch_assoc()) {
            $vocabularyPairs[] = [
                'german' => repairUTF8($row['german_word']),
                'english' => repairUTF8($row['english_word']),
                'gvocabid' => $row['gvocabid'],
                'evocabid' => $row['evocabid']
            ];
        }
        $vocabStmt->close();
        
        if (!empty($vocabularyPairs)) {
            $showTest = true;
        }
    }
    $unitStmt->close();
}

// Units laden falls nicht im Test-Modus
$units = [];
if (!$showTest) {
    $stmt = $conn->prepare("
        SELECT 
            u.unitid,
            u.unitname,
            COUNT(vg.gvocabid) as vocab_count
        FROM unit u
        LEFT JOIN vocabgerman vg ON u.unitid = vg.unitid
        GROUP BY u.unitid, u.unitname
        HAVING vocab_count > 0
        ORDER BY u.unitid
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // UTF-8 Reparatur für Unit-Namen anwenden
        $row['unitname'] = repairUTF8($row['unitname']);
        $units[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showTest ? 'Multi-Choice Test - ' . htmlspecialchars($unitName) : 'SprachApp - MultiChoice'; ?></title>
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
            color: white;
        }
        
        .nav-link {
            font-weight: 600;
            text-align: center;
            color: white !important;
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
        
        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }
        
        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
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
        
        .vocab-card {
            cursor: pointer;
            min-height: 70px;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }

        .vocab-card.selected {
            background-color: #e9ecef;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .vocab-card.correct {
            background-color: #d4edda;
            border-color: #28a745;
        }

        .vocab-card.wrong {
            background-color: #f8d7da;
            border-color: #dc3545;
        }

        .result-message {
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }

        .score-display {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e9ecef;
            margin-bottom: 1rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 2rem;
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
                        <a class="nav-link" href="miniTest.php">Grammatiktrainer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="zuordnen.php">MultiChoice</a>
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
        <?php if ($showTest): ?>
            <!-- Test-Modus -->
            <div class="welcome-box">
                <h2>Vokabel-Übung</h2>
                <p>Verbinde die deutschen Wörter mit den entsprechenden englischen Übersetzungen</p>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <a href="zuordnen.php" class="btn btn-outline-secondary mb-3">← Zurück zur Unit-Auswahl</a>
                    <h3><?php echo htmlspecialchars($unitName); ?> - Vokabeln <?php echo ($group - 1) * 5 + 1; ?>-<?php echo ($group - 1) * 5 + count($vocabularyPairs); ?></h3>
                    <div class="alert alert-info" role="alert">
                        Klicke zuerst auf ein deutsches Wort und dann auf die passende englische Übersetzung
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="score-display">
                        Punkte: <span id="score">0</span> / <span id="total-pairs"><?php echo count($vocabularyPairs); ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <h3 class="mb-3 text-center">Deutsch</h3>
                    <div id="german-words" class="d-flex flex-column"></div>
                </div>

                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <div class="d-none d-md-block">
                        <h1 class="display-1">⇔</h1>
                    </div>
                </div>

                <div class="col-md-5">
                    <h3 class="mb-3 text-center">English</h3>
                    <div id="english-words" class="d-flex flex-column"></div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div id="result-message" class="result-message text-center"></div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 text-center">
                    <?php 
                    // Prüfe ob es eine nächste Gruppe gibt
                    $nextGroup = $group + 1;
                    $nextOffset = ($nextGroup - 1) * 5;
                    
                    // Zähle alle Vokabeln in der Unit
                    $totalStmt = $conn->prepare("
                        SELECT COUNT(*) as total_count
                        FROM vocabgerman vg
                        JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid
                        JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
                        WHERE vg.unitid = ? AND ve.unitid = ?
                    ");
                    $totalStmt->bind_param("ii", $unitid, $unitid);
                    $totalStmt->execute();
                    $totalResult = $totalStmt->get_result();
                    $totalVocabs = 0;
                    if ($totalRow = $totalResult->fetch_assoc()) {
                        $totalVocabs = $totalRow['total_count'];
                    }
                    $totalStmt->close();
                    
                    // Berechne ob noch weitere Vokabeln vorhanden sind
                    $currentEndIndex = $group * 5;
                    $hasNextGroup = $totalVocabs > $currentEndIndex;
                    ?>
                    
                    <button id="restart-btn" class="btn btn-primary btn-lg" onclick="restartGame()">
                        Wiederholen
                    </button>
                    
                    <?php if ($hasNextGroup): ?>
                        <a href="zuordnen.php?unit=<?php echo $unitid; ?>&group=<?php echo $nextGroup; ?>" 
                           class="btn btn-success btn-lg ms-2" id="next-btn" style="display: none;">
                            Nächste 5 Vokabeln
                        </a>
                    <?php else: ?>
                        <a href="zuordnen.php" class="btn btn-success btn-lg ms-2" id="finish-btn" style="display: none;">
                            Unit abgeschlossen!
                        </a>
                    <?php endif; ?>
                    
                    <a href="zuordnen.php" class="btn btn-secondary btn-lg ms-2">
                        Andere Unit wählen
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Unit-Auswahl -->
            <div class="welcome-box">
                <h2>Multi-Choice Vokabeltest</h2>
                <p>Wählen Sie eine Unit aus, um den Zuordnungstest zu starten. Verbinden Sie deutsche Wörter mit den entsprechenden englischen Übersetzungen.</p>
            </div>
            
            <?php if (empty($units)): ?>
                <div class="alert alert-info" role="alert">
                    Zurzeit sind keine Units mit Vokabeln verfügbar.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($units as $unit): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="unit-card">
                                <div class="unit-header">
                                    <h5><?php echo htmlspecialchars($unit['unitname']); ?></h5>
                                </div>
                                <div class="unit-body">
                                    <p class="mb-4">
                                        <strong><?php echo $unit['vocab_count']; ?></strong> Vokabel<?php echo $unit['vocab_count'] != 1 ? 'n' : ''; ?>
                                    </p>
                                    <div class="d-grid">
                                        <a href="zuordnen.php?unit=<?php echo $unit['unitid']; ?>" 
                                           class="btn btn-primary w-100">
                                            Zuordnen starten
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

    <?php if ($showTest): ?>
    <script>
        // Vokabeln vom PHP in JavaScript übertragen
        const vocabularyPairs = <?php echo json_encode($vocabularyPairs); ?>;
        let totalPairs = vocabularyPairs.length;

        // Variablen für den Spielstatus
        let selectedGermanCard = null;
        let selectedEnglishCard = null;
        let correctPairs = 0;

        // Funktion zur Anzeige von Fehlermeldungen
        function showError(message) {
            const resultMessage = document.getElementById('result-message');
            resultMessage.textContent = message;
            resultMessage.style.display = 'block';
            resultMessage.className = 'result-message alert alert-danger';
        }

        // Funktion zum Behandeln von Klicks auf Wortkarten
        function handleCardClick(card) {
            // Ignoriere Klicks auf bereits übereinstimmende Karten
            if (card.classList.contains('correct')) {
                return;
            }

            const language = card.dataset.language;

            if (language === 'german') {
                // Wenn bereits eine deutsche Karte ausgewählt ist, diese deselektieren
                if (selectedGermanCard) {
                    selectedGermanCard.classList.remove('selected');
                }

                // Neue deutsche Karte auswählen
                selectedGermanCard = card;
                card.classList.add('selected');
            } else if (language === 'english') {
                // Wenn bereits eine englische Karte ausgewählt ist, diese deselektieren
                if (selectedEnglishCard) {
                    selectedEnglishCard.classList.remove('selected');
                }
                // Neue englische Karte auswählen
                selectedEnglishCard = card;
                card.classList.add('selected');
            }

            // Eine deutsche und englische Karte sind ausgewählt:
            if (selectedGermanCard && selectedEnglishCard) {
                // Überprüfe das Kartenpaar 
                checkMatch();
            }
        }

        // Funktion zum Überprüfen der Übereinstimmung
        function checkMatch() {
            const germanWord = selectedGermanCard.dataset.word;
            const englishWord = selectedEnglishCard.dataset.word;

            // Finde das Paar für das deutsche Wort
            let correctPair = null;
            for (let i = 0; i < vocabularyPairs.length; i++) {
                let pair = vocabularyPairs[i];
                if (pair.german === germanWord) {
                    correctPair = vocabularyPairs[i];
                    break;
                }
            }

            // Wir haben das deutsche Wort gefunden, jetzt überprüfen wir die englische Übersetzung
            if (correctPair && correctPair.english === englishWord) {
                // Richtige Übereinstimmung
                selectedGermanCard.classList.remove('selected');
                selectedEnglishCard.classList.remove('selected');
                selectedGermanCard.classList.add('correct');
                selectedEnglishCard.classList.add('correct');

                // Erhöhe den Punktestand
                correctPairs++;
                document.getElementById('score').textContent = correctPairs;

                // Überprüfe, ob alle Paare gefunden wurden
                if (correctPairs === totalPairs) {
                    const resultMessage = document.getElementById('result-message');
                    resultMessage.textContent = 'Gratulation! Du hast alle Vokabelpaare richtig zugeordnet!';
                    resultMessage.style.display = 'block';
                    resultMessage.className = 'result-message alert alert-success';

                    // Zeige den "Weiter" Button
                    const nextBtn = document.getElementById('next-btn');
                    const finishBtn = document.getElementById('finish-btn');
                    if (nextBtn) {
                        nextBtn.style.display = 'inline-block';
                    }
                    if (finishBtn) {
                        finishBtn.style.display = 'inline-block';
                    }
                }

                // Setze die ausgewählten Karten zurück	
                selectedGermanCard = null;
                selectedEnglishCard = null;
            } else {
                // Falsche Übereinstimmung
                selectedGermanCard.classList.add('wrong');
                selectedEnglishCard.classList.add('wrong');

                // Kurz anzeigen, dass die Zuordnung falsch ist
                setTimeout(() => {
                    selectedGermanCard.classList.remove('selected', 'wrong');
                    selectedEnglishCard.classList.remove('selected', 'wrong');
                    selectedGermanCard = null;
                    selectedEnglishCard = null;
                }, 1000);
            }
        }

        // Fisher-Yates shuffle
        function shuffle(array) {
            let arr = [...array];
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                let helper = arr[i];
                arr[i] = arr[j];    
                arr[j] = helper;
            }
            return arr;
        }

        // JavaScript Funktion zum Erstellen einer Wortkarte
        function createWordCardHTML(word, language, index) {
            let str = "<div " + 
            "class='vocab-card card d-flex align-items-center justify-content-center p-3' " +
            "data-language='" + language + "' " +
            "data-index='" + index + "' " + 
            "data-word='" + word + "' " +
            " onclick='handleCardClick(this)'>" +
            "    <div class='card-body text-center'>" + word + "</div>" +
            "</div>";
            return str;
        }

        // Spiel initialisieren
        function initializeGame() {
            const germanContainer = document.getElementById('german-words');
            const englishContainer = document.getElementById('english-words');

            // Container leeren
            germanContainer.innerHTML = '';
            englishContainer.innerHTML = '';

            // Deutsche Wörter in zufälliger Reihenfolge anzeigen
            const shuffledGerman = shuffle(vocabularyPairs);
            
            for (let index = 0; index < shuffledGerman.length; index++) {
                let pair = shuffledGerman[index];
                let html = createWordCardHTML(pair.german, 'german', index);
                germanContainer.innerHTML += html;
            }

            // Englische Wörter in zufälliger Reihenfolge anzeigen
            const shuffledEnglish = shuffle(vocabularyPairs);
            for (let index = 0; index < shuffledEnglish.length; index++) {
                let pair = shuffledEnglish[index];
                let html = createWordCardHTML(pair.english, 'english', index);
                englishContainer.innerHTML += html;
            }
        }

        // Funktion zum Neustarten des Spiels
        function restartGame() {
            // Zurücksetzen der Spielvariablen
            selectedGermanCard = null;
            selectedEnglishCard = null;
            correctPairs = 0;
            // Zurücksetzen der Anzeige
            document.getElementById('score').textContent = '0';
            document.getElementById('result-message').style.display = 'none';
            
            // Verstecke die Weiter-Buttons
            const nextBtn = document.getElementById('next-btn');
            const finishBtn = document.getElementById('finish-btn');
            if (nextBtn) nextBtn.style.display = 'none';
            if (finishBtn) finishBtn.style.display = 'none';
            
            // Neues Spiel initialisieren
            initializeGame();
        }

        // Initialisiere das Spiel beim Laden der Seite
        window.onload = function() {
            document.getElementById('total-pairs').textContent = totalPairs;
            initializeGame();
        }
    </script>
    <?php endif; ?>

    <script>
        // Script zum Anzeigen der rollenspezifischen Bereiche
        document.addEventListener('DOMContentLoaded', function() {
            var role = "<?php echo $role; ?>";
            
            if (role === 'lehrer' || role === 'admin') {
                var teacherSections = document.querySelectorAll('.teacher-section');
                for (var i = 0; i < teacherSections.length; i++) {
                    teacherSections[i].style.display = 'block';
                }
            }
            
            if (role === 'admin') {
                var adminSections = document.querySelectorAll('.admin-section');
                for (var i = 0; i < adminSections.length; i++) {
                    adminSections[i].style.display = 'block';
                }
            }
        });
    </script>
</body>
</html>