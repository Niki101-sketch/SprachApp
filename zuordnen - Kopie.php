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
$unitid = 0;

// Session-Key für korrekte Antworten pro Unit
function getCorrectAnswersKey($unitid) {
    return 'correct_answers_unit_' . $unitid;
}

// Prüfen ob Test gestartet werden soll
if (isset($_GET['unit'])) {
    $unitid = intval($_GET['unit']);
    
    // Unit-Name holen
    $unitStmt = $conn->prepare("SELECT unitname FROM unit WHERE unitid = ?");
    $unitStmt->bind_param("i", $unitid);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();
    if ($unitRow = $unitResult->fetch_assoc()) {
        $unitName = $unitRow['unitname'];
        
        // Bereits korrekt beantwortete Vokabeln aus der Session holen
        $correctAnswersKey = getCorrectAnswersKey($unitid);
        $correctAnswers = isset($_SESSION[$correctAnswersKey]) ? $_SESSION[$correctAnswersKey] : [];
        
        // SQL-Query für ausgeschlossene Vokabeln vorbereiten
        $excludeCondition = "";
        $excludeParams = [];
        if (!empty($correctAnswers)) {
            $placeholders = str_repeat('?,', count($correctAnswers) - 1) . '?';
            $excludeCondition = "AND vg.gvocabid NOT IN ($placeholders)";
            $excludeParams = $correctAnswers;
        }
        
        // Zufällige 4 Vokabeln aus der Unit holen (ohne bereits korrekte)
        $sql = "
            SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
            FROM vocabgerman vg
            JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vg.unitid = ? AND ve.unitid = ? $excludeCondition
            ORDER BY RAND()
            LIMIT 4
        ";
        
        $vocabStmt = $conn->prepare($sql);
        
        // Parameter binden
        $types = "ii" . str_repeat('i', count($excludeParams));
        $params = array_merge([$unitid, $unitid], $excludeParams);
        if (!empty($params)) {
            $vocabStmt->bind_param($types, ...$params);
        }
        
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
        
        // Falls keine neuen Vokabeln mehr vorhanden sind, Cache zurücksetzen
        if (empty($vocabularyPairs) && !empty($correctAnswers)) {
            unset($_SESSION[$correctAnswersKey]);
            // Nochmal versuchen mit allen Vokabeln
            $vocabStmt = $conn->prepare("
                SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
                FROM vocabgerman vg
                JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid
                JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
                WHERE vg.unitid = ? AND ve.unitid = ?
                ORDER BY RAND()
                LIMIT 4
            ");
            $vocabStmt->bind_param("ii", $unitid, $unitid);
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
        }
        
        if (!empty($vocabularyPairs)) {
            $showTest = true;
        }
    }
}

// AJAX-Handler für korrekte Antworten
if (isset($_POST['action']) && $_POST['action'] === 'save_correct_answers') {
    $unitid = intval($_POST['unitid']);
    $correctGvocabIds = json_decode($_POST['correct_gvocab_ids'], true);
    
    if ($unitid && is_array($correctGvocabIds)) {
        $correctAnswersKey = getCorrectAnswersKey($unitid);
        
        // Bestehende korrekte Antworten holen
        $existingCorrect = isset($_SESSION[$correctAnswersKey]) ? $_SESSION[$correctAnswersKey] : [];
        
        // Neue korrekte Antworten hinzufügen
        $updatedCorrect = array_unique(array_merge($existingCorrect, $correctGvocabIds));
        
        // In Session speichern
        $_SESSION[$correctAnswersKey] = $updatedCorrect;
        
        echo json_encode(['success' => true, 'total_correct' => count($updatedCorrect)]);
        exit;
    }
    
    echo json_encode(['success' => false]);
    exit;
}

// Reset-Handler für korrekte Antworten
if (isset($_POST['action']) && $_POST['action'] === 'reset_correct_answers') {
    $unitid = intval($_POST['unitid']);
    if ($unitid) {
        $correctAnswersKey = getCorrectAnswersKey($unitid);
        unset($_SESSION[$correctAnswersKey]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
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

        .next-round-btn {
            display: none;
        }

        .progress-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
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
                    <h3><?php echo htmlspecialchars($unitName); ?></h3>
                    <p class="lead">Verbinde die deutschen Begriffe mit den entsprechenden Antworten</p>
                    
                    <?php 
                    $correctAnswersKey = getCorrectAnswersKey($unitid);
                    $totalCorrect = isset($_SESSION[$correctAnswersKey]) ? count($_SESSION[$correctAnswersKey]) : 0;
                    if ($totalCorrect > 0): 
                    ?>
                    <div class="progress-info">
                        <strong>Fortschritt:</strong> Du hast bereits <?php echo $totalCorrect; ?> Vokabel(n) richtig beantwortet und sie werden nicht mehr angezeigt.
                        <button class="btn btn-sm btn-outline-warning ms-2" onclick="resetProgress()">Fortschritt zurücksetzen</button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info" role="alert">
                        Klicke zuerst auf eine Frage und dann auf die passende Antwort
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
                    <h3 class="mb-3 text-center">Fragen</h3>
                    <div id="german-words" class="d-flex flex-column"></div>
                </div>

                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <div class="d-none d-md-block">
                        <h1 class="display-1">⇔</h1>
                    </div>
                </div>

                <div class="col-md-5">
                    <h3 class="mb-3 text-center">Antworten</h3>
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
                    <button id="next-round-btn" class="btn btn-success btn-lg ms-2 next-round-btn" onclick="nextRound()">
                        Nächste Runde
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
                <p>Wählen Sie eine Unit aus, um den Zuordnungstest zu starten. Verbinden Sie Fragen mit den entsprechenden Antworten.</p>
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
                                    <p class="mb-3">
                                        <strong><?php echo $unit['vocab_count']; ?></strong> Karteikarte<?php echo $unit['vocab_count'] != 1 ? 'n' : ''; ?>
                                    </p>
                                    <?php 
                                    $correctAnswersKey = getCorrectAnswersKey($unit['unitid']);
                                    $unitCorrect = isset($_SESSION[$correctAnswersKey]) ? count($_SESSION[$correctAnswersKey]) : 0;
                                    if ($unitCorrect > 0): 
                                    ?>
                                    <p class="text-muted small mb-3">
                                        <?php echo $unitCorrect; ?> Vokabel(n) bereits gemeistert
                                    </p>
                                    <?php endif; ?>
                                    <div class="d-grid">
                                        <a href="zuordnen.php?unit=<?php echo $unit['unitid']; ?>" 
                                           class="btn btn-primary">
                                            Test starten
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

    <?php include 'footer.php'; ?>

    <?php if ($showTest): ?>
    <script>
        // Vokabeln vom PHP in JavaScript übertragen
        const vocabularyPairs = <?php echo json_encode($vocabularyPairs); ?>;
        const unitid = <?php echo $unitid; ?>;
        let totalPairs = vocabularyPairs.length;

        // Variablen für den Spielstatus
        let selectedGermanCard = null;
        let selectedEnglishCard = null;
        let correctPairs = 0;
        let correctGvocabIds = []; // Array für korrekte gvocabids

        // Funktion zur Anzeige von Fehlermeldungen
        function showError(message) {
            const resultMessage = document.getElementById('result-message');
            resultMessage.textContent = message;
            resultMessage.style.display = 'block';
            resultMessage.className = 'result-message alert alert-danger';
        }

        // Funktion zum Behandeln von Klicks auf Wortkarten
        function handleCardClick(card) {
            if (card.classList.contains('correct')) {
                return;
            }

            const language = card.dataset.language;

            if (language === 'german') {
                if (selectedGermanCard) {
                    selectedGermanCard.classList.remove('selected');
                }
                selectedGermanCard = card;
                card.classList.add('selected');
            } else if (language === 'english') {
                if (selectedEnglishCard) {
                    selectedEnglishCard.classList.remove('selected');
                }
                selectedEnglishCard = card;
                card.classList.add('selected');
            }

            if (selectedGermanCard && selectedEnglishCard) {
                checkMatch();
            }
        }

        // Funktion zum Überprüfen der Übereinstimmung
        function checkMatch() {
            const germanWord = selectedGermanCard.dataset.word;
            const englishWord = selectedEnglishCard.dataset.word;

            let correctPair = null;
            for (let i = 0; i < vocabularyPairs.length; i++) {
                let pair = vocabularyPairs[i];
                if (pair.german === germanWord) {
                    correctPair = vocabularyPairs[i];
                    break;
                }
            }

            if (correctPair && correctPair.english === englishWord) {
                // Richtige Übereinstimmung
                selectedGermanCard.classList.remove('selected');
                selectedEnglishCard.classList.remove('selected');
                selectedGermanCard.classList.add('correct');
                selectedEnglishCard.classList.add('correct');

                // Gvocabid zur Liste der korrekten Antworten hinzufügen
                correctGvocabIds.push(correctPair.gvocabid);

                correctPairs++;
                document.getElementById('score').textContent = correctPairs;

                if (correctPairs === totalPairs) {
                    // Alle Paare gefunden - korrekte Antworten speichern
                    saveCorrectAnswers();
                    
                    const resultMessage = document.getElementById('result-message');
                    resultMessage.textContent = 'Gratulation! Du hast alle Paare richtig zugeordnet!';
                    resultMessage.style.display = 'block';
                    resultMessage.className = 'result-message alert alert-success';
                    
                    document.getElementById('next-round-btn').style.display = 'inline-block';
                }

                selectedGermanCard = null;
                selectedEnglishCard = null;
            } else {
                // Falsche Übereinstimmung
                selectedGermanCard.classList.add('wrong');
                selectedEnglishCard.classList.add('wrong');

                setTimeout(() => {
                    selectedGermanCard.classList.remove('selected', 'wrong');
                    selectedEnglishCard.classList.remove('selected', 'wrong');
                    selectedGermanCard = null;
                    selectedEnglishCard = null;
                }, 1000);
            }
        }

        // Funktion zum Speichern der korrekten Antworten
        function saveCorrectAnswers() {
            if (correctGvocabIds.length === 0) return;

            fetch('zuordnen.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=save_correct_answers&unitid=' + unitid + '&correct_gvocab_ids=' + JSON.stringify(correctGvocabIds)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Korrekte Antworten gespeichert. Total: ' + data.total_correct);
                }
            })
            .catch(error => {
                console.error('Fehler beim Speichern:', error);
            });
        }

        // Funktion zum Zurücksetzen des Fortschritts
        function resetProgress() {
            if (confirm('Möchten Sie wirklich den gesamten Fortschritt für diese Unit zurücksetzen?')) {
                fetch('zuordnen.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reset_correct_answers&unitid=' + unitid
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Fehler beim Zurücksetzen:', error);
                });
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

            germanContainer.innerHTML = '';
            englishContainer.innerHTML = '';

            const shuffledGerman = shuffle(vocabularyPairs);
            
            for (let index = 0; index < shuffledGerman.length; index++) {
                let pair = shuffledGerman[index];
                let html = createWordCardHTML(pair.german, 'german', index);
                germanContainer.innerHTML += html;
            }

            const shuffledEnglish = shuffle(vocabularyPairs);
            for (let index = 0; index < shuffledEnglish.length; index++) {
                let pair = shuffledEnglish[index];
                let html = createWordCardHTML(pair.english, 'english', index);
                englishContainer.innerHTML += html;
            }
        }

        // Funktion zum Neustarten des Spiels
        function restartGame() {
            selectedGermanCard = null;
            selectedEnglishCard = null;
            correctPairs = 0;
            correctGvocabIds = [];
            document.getElementById('score').textContent = '0';
            document.getElementById('result-message').style.display = 'none';
            document.getElementById('next-round-btn').style.display = 'none';
            initializeGame();
        }

        // Funktion für nächste Runde
        function nextRound() {
            window.location.href = 'zuordnen.php?unit=' + unitid;
        }

        // Initialisiere das Spiel beim Laden der Seite
        window.onload = function() {
            document.getElementById('total-pairs').textContent = totalPairs;
            initializeGame();
        }
    </script>
    <?php endif; ?>

    <script>
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