<?php 
session_start(); 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL); 
include 'connection.php'; 
$conn->set_charset("utf8mb4");

// Get user info - wichtig für die Anzeige im Header
$username = '';
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

$role = '';
if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
}

$unitid = $_GET['unit']; 
$current = isset($_GET['current']) ? intval($_GET['current']) : 0;
$studentid = 1; // Fest für Demo 

// Direction Parameter (default: german to english)
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'de-en';

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

// Skip Button Logik
if (isset($_POST['skip'])) {
    $current++;
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

// Unit-Name holen
$unitNameSql = "SELECT unitname FROM unit WHERE unitid = $unitid";
$unitNameResult = $conn->query($unitNameSql);
$unitName = "";
if ($unitNameRow = $unitNameResult->fetch_assoc()) {
    $unitName = $unitNameRow['unitname'];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Karteikarten</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome für Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
        /* Speech Recognition Styles */
        .speech-container {
            margin: 2rem 0;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        
        .speech-result {
            background-color: white;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            border-left: 4px solid #0d6efd;
        }
        
        .speech-correct {
            border-left-color: #198754;
            background-color: #f8fff9;
        }
        
        .speech-incorrect {
            border-left-color: #dc3545;
            background-color: #fff8f8;
        }
        
        .microphone-btn {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
            transition: all 0.3s;
        }
        
        .microphone-btn:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
        
        .microphone-btn.listening {
            background-color: #198754;
            border-color: #198754;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
            }
        }
        
        .speech-status {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 1rem;
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
                        <a class="nav-link" href="miniTest.php">Grammatiktrainer</a>
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
            <h2>Karteikarten</h2>
            <p>Lernen Sie Vokabeln mit Karteikarten. Drehen Sie die Karte um und bewerten Sie, ob Sie die Antwort richtig wussten. <strong>Neu:</strong> Nutzen Sie die Spracherkennung zum Antworten!</p>
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
                            <h2 class="mb-4" id="questionWord"><?php echo htmlspecialchars($words[$current]['german_word']); ?></h2>
                            
                            <!-- Spracherkennung Bereich -->
                            <div class="speech-container">
                                <h5 class="mb-3">
                                    <i class="fas fa-microphone"></i> 
                                    Sprechen Sie die englische Übersetzung
                                </h5>
                                
                                <button type="button" id="speechBtn" class="btn microphone-btn mb-3">
                                    <i class="fas fa-microphone"></i>
                                </button>
                                
                                <div id="speechResult" class="speech-result" style="display: none;">
                                    <strong>Sie haben gesagt:</strong> <span id="spokenText"></span>
                                </div>
                                
                                <div id="speechStatus" class="speech-status">
                                    Klicken Sie auf das Mikrofon und sprechen Sie die Antwort
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <form method="post">
                                    <button type="submit" name="show" class="btn btn-primary">Antwort zeigen</button>
                                    <button type="submit" name="skip" value="1" class="btn btn-secondary mx-2">Überspringen</button>
                                    <input type="hidden" name="current" value="<?php echo $current; ?>">
                                </form>
                            </div>
                        <?php 
                        } else { 
                        ?>
                            <h2 class="mb-4">Lösung: <?php echo htmlspecialchars($words[$current]['english_word']); ?></h2>
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

        // Spracherkennung
        let recognition;
        let isListening = false;
        const correctAnswer = "<?php echo isset($words[$current]) ? htmlspecialchars($words[$current]['english_word']) : ''; ?>";
        
        // Prüfen ob Web Speech API unterstützt wird
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US'; // Englisch für die Antworten
            
            const speechBtn = document.getElementById('speechBtn');
            const speechResult = document.getElementById('speechResult');
            const spokenText = document.getElementById('spokenText');
            const speechStatus = document.getElementById('speechStatus');
            
            if (speechBtn) {
                speechBtn.addEventListener('click', function() {
                    if (!isListening) {
                        startListening();
                    } else {
                        stopListening();
                    }
                });
            }
            
            function startListening() {
                isListening = true;
                speechBtn.classList.add('listening');
                speechBtn.innerHTML = '<i class="fas fa-stop"></i>';
                speechStatus.textContent = 'Hören... Sprechen Sie jetzt!';
                speechResult.style.display = 'none';
                
                recognition.start();
            }
            
            function stopListening() {
                isListening = false;
                speechBtn.classList.remove('listening');
                speechBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                speechStatus.textContent = 'Klicken Sie auf das Mikrofon und sprechen Sie die Antwort';
                
                recognition.stop();
            }
            
            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript.toLowerCase().trim();
                spokenText.textContent = transcript;
                speechResult.style.display = 'block';
                
                // Überprüfen ob die Antwort korrekt ist
                const correctAnswerLower = correctAnswer.toLowerCase().trim();
                const similarity = calculateSimilarity(transcript, correctAnswerLower);
                
                if (similarity > 0.8 || transcript === correctAnswerLower) {
                    speechResult.classList.remove('speech-incorrect');
                    speechResult.classList.add('speech-correct');
                    speechStatus.innerHTML = '✅ <strong>Richtig!</strong> "' + correctAnswer + '"';
                } else {
                    speechResult.classList.remove('speech-correct');
                    speechResult.classList.add('speech-incorrect');
                    speechStatus.innerHTML = '❌ <strong>Nicht ganz richtig.</strong> Die korrekte Antwort wäre: "' + correctAnswer + '"';
                }
                
                stopListening();
            };
            
            recognition.onerror = function(event) {
                console.error('Spracherkennungsfehler:', event.error);
                speechStatus.textContent = 'Fehler bei der Spracherkennung. Bitte versuchen Sie es erneut.';
                stopListening();
            };
            
            recognition.onend = function() {
                stopListening();
            };
            
        } else {
            // Fallback wenn Spracherkennung nicht unterstützt wird
            const speechContainer = document.querySelector('.speech-container');
            if (speechContainer) {
                speechContainer.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Spracherkennung wird von Ihrem Browser nicht unterstützt. Bitte verwenden Sie Chrome, Edge oder Safari.</div>';
            }
        }
        
        // Hilfsfunktion zur Berechnung der Ähnlichkeit zwischen zwei Strings
        function calculateSimilarity(str1, str2) {
            const longer = str1.length > str2.length ? str1 : str2;
            const shorter = str1.length > str2.length ? str2 : str1;
            
            if (longer.length === 0) {
                return 1.0;
            }
            
            const editDistance = levenshteinDistance(longer, shorter);
            return (longer.length - editDistance) / longer.length;
        }
        
        // Levenshtein-Distanz Algorithmus
        function levenshteinDistance(str1, str2) {
            const matrix = [];
            
            for (let i = 0; i <= str2.length; i++) {
                matrix[i] = [i];
            }
            
            for (let j = 0; j <= str1.length; j++) {
                matrix[0][j] = j;
            }
            
            for (let i = 1; i <= str2.length; i++) {
                for (let j = 1; j <= str1.length; j++) {
                    if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1,
                            matrix[i][j - 1] + 1,
                            matrix[i - 1][j] + 1
                        );
                    }
                }
            }
            
            return matrix[str2.length][str1.length];
        }
    </script>
</body>
</html>