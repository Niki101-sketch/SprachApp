<?php 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL); 
include 'connection.php'; 

$unitid = $_GET['unit']; 
$studentid = 1; // Fest für Demo 

// Wenn Button geklickt wurde 
if (isset($_POST['answer']) && isset($_POST['gvocabid']) && isset($_POST['evocabid'])) { 
    $gvocabid = intval($_POST['gvocabid']); 
    $evocabid = intval($_POST['evocabid']); 
    $answer = $_POST['answer']; 
    
    if ($answer == 'right') { 
        // Prüfen ob bereits vorhanden und incrementieren oder neu einfügen
        $checkSql = "SELECT correct_answers FROM vocabright WHERE studentid = $studentid AND gvocabid = $gvocabid AND evocabid = $evocabid";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            $row = $checkResult->fetch_assoc();
            $newCount = $row['correct_answers'] + 1;
            $sql = "UPDATE vocabright SET correct_answers = $newCount, last_answered = CURRENT_TIMESTAMP WHERE studentid = $studentid AND gvocabid = $gvocabid AND evocabid = $evocabid";
        } else {
            $sql = "INSERT INTO vocabright (studentid, gvocabid, evocabid, correct_answers) VALUES ($studentid, $gvocabid, $evocabid, 1)";
        }
    } else { 
        // Prüfen ob bereits vorhanden und incrementieren oder neu einfügen
        $checkSql = "SELECT wrong_answers FROM vocabwrong WHERE studentid = $studentid AND gvocabid = $gvocabid AND evocabid = $evocabid";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            $row = $checkResult->fetch_assoc();
            $newCount = $row['wrong_answers'] + 1;
            $sql = "UPDATE vocabwrong SET wrong_answers = $newCount, last_answered = CURRENT_TIMESTAMP WHERE studentid = $studentid AND gvocabid = $gvocabid AND evocabid = $evocabid";
        } else {
            $sql = "INSERT INTO vocabwrong (studentid, gvocabid, evocabid, wrong_answers) VALUES ($studentid, $gvocabid, $evocabid, 1)";
        }
    } 
    if (!$conn->query($sql)) { 
        die("Fehler beim Einfügen: " . $conn->error); 
    } 
}

// Vokabel-Paare holen (alle möglichen Kombinationen aus der Mapping-Tabelle)
$sql = "SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
        FROM vocabgerman vg 
        JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid 
        JOIN vocabenglish ve ON vm.evocabid = ve.evocabid 
        WHERE vg.unitid = $unitid AND ve.unitid = $unitid
        ORDER BY vg.gvocabid, ve.evocabid"; 
$result = $conn->query($sql); 
$words = []; 
while($row = $result->fetch_assoc()) { 
    $words[] = $row; 
} 

$current = $_GET['current'] ?? 0; 

// Unit-Name holen
$unitNameSql = "SELECT unitname FROM unit WHERE unitid = $unitid";
$unitNameResult = $conn->query($unitNameSql);
$unitName = "";
if ($unitNameRow = $unitNameResult->fetch_assoc()) {
    $unitName = $unitNameRow['unitname'];
}

// Zusätzliche Info: Anzahl der verschiedenen deutschen Wörter und englischen Übersetzungen
$germanCountSql = "SELECT COUNT(DISTINCT gvocabid) as german_count FROM vocabgerman WHERE unitid = $unitid";
$englishCountSql = "SELECT COUNT(DISTINCT evocabid) as english_count FROM vocabenglish WHERE unitid = $unitid";
$mappingCountSql = "SELECT COUNT(*) as mapping_count FROM vocabmapping vm 
                    JOIN vocabgerman vg ON vm.gvocabid = vg.gvocabid 
                    JOIN vocabenglish ve ON vm.evocabid = ve.evocabid 
                    WHERE vg.unitid = $unitid AND ve.unitid = $unitid";

$germanCount = $conn->query($germanCountSql)->fetch_assoc()['german_count'];
$englishCount = $conn->query($englishCountSql)->fetch_assoc()['english_count'];
$mappingCount = $conn->query($mappingCountSql)->fetch_assoc()['mapping_count'];

// Header einbinden
include 'header.php';
?>

<!-- Zusätzliche Styles für Karteikarten -->
<style>
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
    
    .stats-box {
        background-color: #e3f2fd;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid #2196f3;
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

<div class="container content">
    <div class="welcome-box">
        <h2>Karteikarten</h2>
        <p>Lernen Sie Vokabeln mit Karteikarten. Drehen Sie die Karte um und bewerten Sie, ob Sie die Antwort richtig wussten.</p>
    </div>
    
    <div class="stats-box">
        <div class="row">
            <div class="col-md-4">
                <strong>Deutsche Wörter:</strong> <?php echo $germanCount; ?>
            </div>
            <div class="col-md-4">
                <strong>Englische Übersetzungen:</strong> <?php echo $englishCount; ?>
            </div>
            <div class="col-md-4">
                <strong>Vokabel-Paare:</strong> <?php echo $mappingCount; ?>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <a href="einheiten.php" class="btn btn-outline-secondary mb-3">← Zurück zur Unit-Auswahl</a>
            <h3>Unit <?php echo $unitid; ?>: <?php echo htmlspecialchars($unitName); ?></h3>
            
            <?php if (!empty($words) && $current < count($words)): ?>
                <div class="card text-center">
                    <div class="card-body">
                        <div class="mb-4">
                            <span class="text-muted">Vokabel-Paar <?php echo ($current + 1); ?> von <?php echo count($words); ?></span>
                            <br>
                            <small class="text-muted">Deutsches Wort → Englische Übersetzung</small>
                        </div>
                        
                        <?php if (!isset($_POST['show'])): ?>
                            <h2 class="mb-4 text-primary"><?php echo htmlspecialchars($words[$current]['german_word']); ?></h2>
                            <p class="text-muted">Klicken Sie auf "Umdrehen", um die englische Übersetzung zu sehen</p>
                            <form method="post">
                                <button type="submit" name="show" class="btn btn-primary">Umdrehen</button>
                                <input type="hidden" name="current" value="<?php echo $current; ?>">
                            </form>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="text-muted">Deutsch:</h4>
                                    <h3 class="text-primary"><?php echo htmlspecialchars($words[$current]['german_word']); ?></h3>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="text-muted">Englisch:</h4>
                                    <h3 class="text-success"><?php echo htmlspecialchars($words[$current]['english_word']); ?></h3>
                                </div>
                            </div>
                            <hr>
                            <p class="text-muted mb-4">Wussten Sie die Übersetzung richtig?</p>
                            <form method="post" action="?unit=<?php echo $unitid; ?>&current=<?php echo $current + 1; ?>">
                                <button type="submit" name="answer" value="right" class="btn btn-success mx-2">✓ Richtig gewusst</button>
                                <button type="submit" name="answer" value="wrong" class="btn btn-danger mx-2">✗ Falsch gewusst</button>
                                <input type="hidden" name="gvocabid" value="<?php echo $words[$current]['gvocabid']; ?>">
                                <input type="hidden" name="evocabid" value="<?php echo $words[$current]['evocabid']; ?>">
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-4">🎉 Glückwunsch!</h4>
                        <p>Sie haben alle <?php echo count($words); ?> Vokabel-Paare dieser Einheit durchgearbeitet.</p>
                        <div class="mt-4">
                            <a href="einheiten.php" class="btn btn-primary mx-2">Zurück zur Übersicht</a>
                            <a href="?unit=<?php echo $unitid; ?>&current=0" class="btn btn-secondary mx-2">Von vorne beginnen</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Script zum Anzeigen der rollenspezifischen Bereiche
    document.addEventListener('DOMContentLoaded', function() {
        var role = "<?php echo $_SESSION['role'] ?? ''; ?>";
        
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

<?php
// Footer einbinden
include 'footer.php';
?>