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

// Variablen für Test-Modus
$showTest = false;
$vocabularyPairs = [];
$unitName = "";
$group = 1;

// Prüfen ob Test gestartet werden soll
if (isset($_GET['unit']) && isset($_GET['group'])) {
    $unitid = intval($_GET['unit']);
    $group = intval($_GET['group']);
    
    // Unit-Name holen
    $unitStmt = $conn->prepare("SELECT unitname FROM unit WHERE unitid = ?");
    $unitStmt->bind_param("i", $unitid);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();
    if ($unitRow = $unitResult->fetch_assoc()) {
        $unitName = $unitRow['unitname'];
        
        // Berechne OFFSET für die Vokabeln (max 10 pro Gruppe)
        $offset = ($group - 1) * 10;
        
        // Vokabeln für diese Gruppe holen
        $vocabStmt = $conn->prepare("
            SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
            FROM vocabgerman vg
            JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vg.unitid = ? AND ve.unitid = ?
            ORDER BY vg.gvocabid
            LIMIT 10 OFFSET ?
        ");
        $vocabStmt->bind_param("iii", $unitid, $unitid, $offset);
        $vocabStmt->execute();
        $vocabResult = $vocabStmt->get_result();
        
        while ($row = $vocabResult->fetch_assoc()) {
            $vocabularyPairs[] = [
                'german' => $row['german_word'],
                'english' => $row['english_word'],
                'gvocabid' => $row['gvocabid'],
                'evocabid' => $row['evocabid']
            ];
        }
        
        if (!empty($vocabularyPairs)) {
            $showTest = true;
        }
    }
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
        $units[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showTest ? 'Multi-Choice Test - ' . htmlspecialchars($unitName) : 'Multi-Choice - Unit Auswahl'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <style>
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
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <?php if ($showTest): ?>
            <!-- Test-Modus -->
            <div class="row mb-4">
                <div class="col-12">
                    <a href="zuordnen.php" class="btn btn-outline-secondary mb-3">← Zurück zur Unit-Auswahl</a>
                    <h1 class="display-5 mb-3">Vokabel-Übung</h1>
                    <h3><?php echo htmlspecialchars($unitName); ?> - Gruppe <?php echo $group; ?></h3>
                    <p class="lead">Verbinde die deutschen Wörter mit den entsprechenden englischen Übersetzungen</p>
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
                    <button id="restart-btn" class="btn btn-primary btn-lg" onclick="restartGame()">
                        Wiederholen
                    </button>
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
                        <?php 
                        // Berechne wie viele Testgruppen es geben wird (max 10 Vokabeln pro Test)
                        $testGroups = ceil($unit['vocab_count'] / 10);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="unit-card">
                                <div class="unit-header">
                                    <h5><?php echo htmlspecialchars($unit['unitname']); ?></h5>
                                </div>
                                <div class="unit-body">
                                    <p class="mb-3">
                                        <strong><?php echo $unit['vocab_count']; ?></strong> Vokabel<?php echo $unit['vocab_count'] != 1 ? 'n' : ''; ?>
                                    </p>
                                    <?php if ($testGroups > 1): ?>
                                        <p class="text-muted mb-3">
                                            <small>Aufgeteilt in <?php echo $testGroups; ?> Tests (max. 10 Vokabeln pro Test)</small>
                                        </p>
                                        
                                        <?php for ($i = 1; $i <= $testGroups; $i++): ?>
                                            <?php
                                            $startVocab = ($i - 1) * 10 + 1;
                                            $endVocab = min($i * 10, $unit['vocab_count']);
                                            ?>
                                            <div class="d-grid mb-2">
                                                <a href="zuordnen.php?unit=<?php echo $unit['unitid']; ?>&group=<?php echo $i; ?>" 
                                                   class="btn btn-outline-primary">
                                                    Test <?php echo $i; ?> (Vokabeln <?php echo $startVocab; ?>-<?php echo $endVocab; ?>)
                                                </a>
                                            </div>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <div class="d-grid">
                                            <a href="zuordnen.php?unit=<?php echo $unit['unitid']; ?>&group=1" 
                                               class="btn btn-primary">
                                                Test starten
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

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