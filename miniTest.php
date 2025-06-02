<?php
// miniTest.php - Grammatiktrainer
require_once 'config.php';

// Authentifizierung prüfen
checkAuthentication();

// Datenbankverbindung
$conn = getMySQLiConnection();

// Benutzerinformationen abrufen
$userInfo = getUserInfo();

// Units aus der Datenbank laden
$unitQuery = "
    SELECT 
        u.unitid,
        u.unitname,
        COUNT(DISTINCT vg.gvocabid) as vocab_count
    FROM unit u
    LEFT JOIN vocabgerman vg ON u.unitid = vg.unitid
    GROUP BY u.unitid, u.unitname
    ORDER BY u.unitid
";
$unitResult = $conn->query($unitQuery);

$units = [];
if ($unitResult->num_rows > 0) {
    while ($row = $unitResult->fetch_assoc()) {
        // UTF-8 Reparatur für Unit-Namen anwenden
        $row['unitname'] = repairUTF8($row['unitname']);
        $units[] = $row;
    }
}

// Vokabeln laden, wenn eine Unit ausgewählt wurde
$vocabs = [];
$selectedUnitId = isset($_GET['unit']) ? intval($_GET['unit']) : null;
$selectedUnitName = '';

if ($selectedUnitId) {
    // Name der ausgewählten Unit abrufen
    $unitStmt = $conn->prepare("SELECT unitname FROM unit WHERE unitid = ?");
    $unitStmt->bind_param("i", $selectedUnitId);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();
    
    if ($unitRow = $unitResult->fetch_assoc()) {
        $selectedUnitName = repairUTF8($unitRow['unitname']);
    } else {
        $selectedUnitName = 'Unbekannte Unit';
    }
    $unitStmt->close();
    
    // Vokabeln für die ausgewählte Unit laden - nur deutsche Wörter
    $vocabStmt = $conn->prepare("
        SELECT DISTINCT
            vg.gvocabid,
            vg.german_word as germanword
        FROM vocabgerman vg
        WHERE vg.unitid = ?
        ORDER BY vg.german_word
    ");
    $vocabStmt->bind_param("i", $selectedUnitId);
    $vocabStmt->execute();
    $vocabResult = $vocabStmt->get_result();
    
    while ($row = $vocabResult->fetch_assoc()) {
        // UTF-8 Reparatur für deutsche Wörter anwenden
        $row['germanword'] = repairUTF8($row['germanword']);
        $vocabs[] = $row;
    }
    $vocabStmt->close();
    
    // Für jede deutsche Vokabel alle möglichen englischen Übersetzungen laden
    foreach ($vocabs as &$vocab) {
        $translationStmt = $conn->prepare("
            SELECT 
                ve.english_word
            FROM vocabmapping vm
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vm.gvocabid = ?
        ");
        $translationStmt->bind_param("i", $vocab['gvocabid']);
        $translationStmt->execute();
        $translationResult = $translationStmt->get_result();
        
        $translations = [];
        while ($transRow = $translationResult->fetch_assoc()) {
            // UTF-8 Reparatur für englische Übersetzungen anwenden
            $translations[] = repairUTF8($transRow['english_word']);
        }
        $vocab['translations'] = $translations;
        $translationStmt->close();
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
        $correctStmt = $conn->prepare("
            SELECT 
                ve.english_word
            FROM vocabmapping vm
            JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
            WHERE vm.gvocabid = ?
        ");
        $correctStmt->bind_param("i", $vocabId);
        $correctStmt->execute();
        $correctResult = $correctStmt->get_result();
        
        $correctAnswers = [];
        while ($correctRow = $correctResult->fetch_assoc()) {
            // UTF-8 Reparatur für korrekte Antworten anwenden
            $correctAnswers[] = repairUTF8($correctRow['english_word']);
        }
        $correctStmt->close();
        
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

// Verbindung schließen
$conn->close();

$pageTitle = "SprachApp - Grammatiktrainer";

// Header einbinden
include 'header.php';
?>

<div class="container content">
    <div class="welcome-box">
        <h2>Grammatiktrainer</h2>
        <p>Wählen Sie eine Unit aus und testen Sie Ihre Kenntnisse.</p>
    </div>
    
    <?php if (!$selectedUnitId): ?>
        <!-- Units Auswahl -->
        <div class="row g-4">
            
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

<?php include 'footer.php'; ?>