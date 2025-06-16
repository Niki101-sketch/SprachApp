<?php
// zuordnen.php - MultiChoice Test
require_once 'config.php';

// Authentifizierung prüfen
checkAuthentication();

// Datenbankverbindung
$conn = getMySQLiConnection();

// Benutzerinformationen abrufen
$userInfo = getUserInfo();

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

$pageTitle = $showTest ? 'Multi-Choice Test - ' . htmlspecialchars($unitName) : 'SprachApp - MultiChoice';

// Header einbinden
include 'header.php';
?>

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

<?php include 'footer.php'; ?>