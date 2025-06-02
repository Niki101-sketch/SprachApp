<?php
// karteikarten.php - Karteikarten-System
require_once 'config.php';

// Benutzerinformationen abrufen (ohne Authentifizierung zu erzwingen)
$userInfo = getUserInfo();

// Datenbankverbindung
$conn = getMySQLiConnection();

$unitid = $_GET['unit']; 
$studentid = 1; // Fest für Demo 

// Wenn Button geklickt wurde 
if (isset($_POST['answer']) && isset($_POST['gvocabid']) && isset($_POST['evocabid'])) { 
    $gvocabid = intval($_POST['gvocabid']); 
    $evocabid = intval($_POST['evocabid']); 
    $answer = $_POST['answer']; 
    
    if ($answer == 'right') { 
        $sql = "REPLACE INTO vocabright (studentid, gvocabid, evocabid) VALUES ($studentid, $gvocabid, $evocabid)"; 
    } else { 
        $sql = "REPLACE INTO vocabwrong (studentid, gvocabid, evocabid) VALUES ($studentid, $gvocabid, $evocabid)"; 
    } 
    if (!$conn->query($sql)) { 
        die("Fehler beim Einfügen: " . $conn->error); 
    } 
}

// Vokabeln holen 
$sql = "SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
         FROM vocabgerman vg, vocabenglish ve, vocabmapping vm 
         WHERE vg.unitid = $unitid AND ve.unitid = $unitid 
         AND vm.gvocabid = vg.gvocabid AND vm.evocabid = ve.evocabid"; 
$result = $conn->query($sql); 
$words = []; 
while($row = $result->fetch_assoc()) { 
    $words[] = $row; 
} 

// Current Position bestimmen
$current = 0;
if (isset($_GET['current'])) {
    $current = $_GET['current'];
}

// Skip Button behandeln
if (isset($_POST['skip'])) {
    $current = 0;
    if (isset($_POST['current'])) {
        $current = intval($_POST['current']) + 1;
    } else {
        $current = 1;
    }
    header("Location: ?unit=$unitid&current=$current");
    exit;
}

// Unit-Name holen
$unitNameSql = "SELECT unitname FROM unit WHERE unitid = $unitid";
$unitNameResult = $conn->query($unitNameSql);
$unitName = "";
if ($unitNameRow = $unitNameResult->fetch_assoc()) {
    $unitName = $unitNameRow['unitname'];
}

$pageTitle = "SprachApp - Karteikarten";

// Header einbinden
include 'header.php';
?>

<div class="container content">
    <div class="welcome-box">
        <h2>Karteikarten</h2>
        <p>Lernen Sie Vokabeln mit Karteikarten. Drehen Sie die Karte um und bewerten Sie, ob Sie die Antwort richtig wussten.</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <a href="einheiten.php" class="btn btn-outline-secondary mb-3">← Zurück zur Unit-Auswahl</a>
            <h3>Unit <?php echo $unitid; ?>: <?php echo htmlspecialchars($unitName); ?></h3>
            
            <?php 
            // Prüfen ob noch Vokabeln da sind und current kleiner als Anzahl
            $hasWordsLeft = false;
            if (!empty($words) && $current < count($words)) {
                $hasWordsLeft = true;
            }
            
            if ($hasWordsLeft) { 
            ?>
                <div class="card text-center">
                    <div class="card-body">
                        <div class="mb-4">
                            <span class="text-muted">Karte <?php echo ($current + 1); ?> von <?php echo count($words); ?></span>
                        </div>
                        
                    <?php 
                    // Prüfen ob "show" Button geklickt wurde
                    $showAnswer = false;
                    if (isset($_POST['show'])) {
                        $showAnswer = true;
                    }
                    
                    if (!$showAnswer) { 
                    ?>
                        <h2 class="mb-4"><?php echo htmlspecialchars($words[$current]['german_word']); ?></h2>
                        <form method="post">
                            <button type="submit" name="show" class="btn btn-primary">Umdrehen</button>
                            <button type="submit" name="skip" value="1" class="btn btn-secondary mx-2">Überspringen</button>
                            <input type="hidden" name="current" value="<?php echo $current; ?>">
                        </form>
                    <?php 
                    } else { 
                    ?>
                        <h2 class="mb-4"><?php echo htmlspecialchars($words[$current]['english_word']); ?></h2>
                        <form method="post" action="?unit=<?php echo $unitid; ?>&current=<?php echo $current + 1; ?>">
                            <button type="submit" name="answer" value="right" class="btn btn-success mx-2">Richtig gewusst</button>
                            <button type="submit" name="answer" value="wrong" class="btn btn-danger mx-2">Falsch gewusst</button>
                            <input type="hidden" name="gvocabid" value="<?php echo $words[$current]['gvocabid']; ?>">
                            <input type="hidden" name="evocabid" value="<?php echo $words[$current]['evocabid']; ?>">
                        </form>
                    <?php 
                    } 
                    ?>
                    </div>
                </div>
            <?php 
            } else { 
            ?>
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-4">Keine Vokabeln mehr!</h4>
                        <p>Sie haben alle Karteikarten dieser Einheit durchgearbeitet.</p>
                        <div class="mt-4">
                            <a href="einheiten.php" class="btn btn-primary mx-2">Zurück zur Übersicht</a>
                            <a href="?unit=<?php echo $unitid; ?>&current=0" class="btn btn-secondary mx-2">Von vorne beginnen</a>
                        </div>
                    </div>
                </div>
            <?php 
            } 
            ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>