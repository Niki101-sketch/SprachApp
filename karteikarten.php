<?php
// karteikarten.php - Modernes Karteikarten-System mit Speech-to-Text
require_once 'config.php';

// Benutzerinformationen abrufen
$userInfo = getUserInfo();

// Datenbankverbindung
$conn = getMySQLiConnection();

$unitid = $_GET['unit']; 
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

$pageTitle = "SprachApp - Karteikarten";

// Header einbinden
include 'header.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        }

        #flashcard {
            transition: all 0.3s ease;
            min-height: 400px;
        }

        #flashcard.correct {
            border: 3px solid #28a745;
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
        }

        #flashcard.incorrect {
            border: 3px solid #dc3545;
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.3);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .btn {
            transition: all 0.2s ease;
            border-radius: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        #listenBtn.recording {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            border: none;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .card-flip-out {
            animation: flipOut 0.3s ease-in;
        }

        .card-flip-in {
            animation: flipIn 0.3s ease-out;
        }

        @keyframes flipOut {
            from { transform: rotateY(0deg); opacity: 1; }
            to { transform: rotateY(90deg); opacity: 0; }
        }

        @keyframes flipIn {
            from { transform: rotateY(90deg); opacity: 0; }
            to { transform: rotateY(0deg); opacity: 1; }
        }

        @media (max-width: 768px) {
            #questionWord {
                font-size: 2rem !important;
            }
            
            .btn-lg {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
            
            .card-body {
                padding: 2rem 1.5rem !important;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid vh-100 d-flex flex-column">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-book me-2"></i>Vokabel Trainer - <?php echo htmlspecialchars($unitName); ?>
            </span>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="bi bi-card-text me-1"></i>
                    Karte <span id="currentCard">1</span> von <span id="totalCards"><?php echo count($words); ?></span>
                </span>
            </div>
        </div>
    </nav>

    <!-- Direction Toggle -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-auto">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="direction" id="de-en" value="de-en" <?php echo $direction == 'de-en' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-primary" for="de-en">Deutsch → Englisch</label>

                    <input type="radio" class="btn-check" name="direction" id="en-de" value="en-de" <?php echo $direction == 'en-de' ? 'checked' : ''; ?>>
                    <label class="btn btn-outline-primary" for="en-de">Englisch → Deutsch</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container flex-grow-1 d-flex align-items-center justify-content-center py-4">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-8 col-xl-6">
                
                <?php if (!empty($words)) { ?>
                <!-- Karteikarte -->
                <div id="flashcard" class="card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient text-white text-center py-3">
                        <h4 class="mb-0" id="cardHeader">
                            <i class="bi bi-translate me-2"></i>
                            <span id="headerText">Spreche das englische Wort aus</span>
                        </h4>
                    </div>
                    
                    <div class="card-body text-center p-5">
                        <!-- Frage Vokabel -->
                        <div class="mb-4">
                            <h2 id="questionWord" class="display-4 fw-bold text-primary mb-3">
                                Laden...
                            </h2>
                        </div>

                        <!-- Speech Recognition Status -->
                        <div id="speechStatus" class="alert alert-info mb-4">
                            <i class="bi bi-mic me-2"></i>
                            Bereit für Spracherkennung
                        </div>

                        <!-- Erkannter Text -->
                        <div id="recognizedText" class="mb-4" style="display: none;">
                            <h5>Erkannt:</h5>
                            <p class="fs-4 fw-bold" id="spokenText"></p>
                        </div>

                        <!-- Antwort (versteckt) -->
                        <div id="answerTranslation" class="mb-4" style="display: none;">
                            <div class="alert alert-success">
                                <h5 class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Richtige Antwort:
                                </h5>
                                <p class="fs-4 fw-bold mb-0" id="answerWord"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="card-footer bg-light p-4">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <button id="listenBtn" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-mic-fill me-2"></i>
                                    Sprechen
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button id="showAnswerBtn" class="btn btn-outline-secondary btn-lg w-100">
                                    <i class="bi bi-eye me-2"></i>
                                    Antwort zeigen
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button id="correctBtn" class="btn btn-success btn-lg w-100" style="display: none;">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Gewusst
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button id="wrongBtn" class="btn btn-danger btn-lg w-100" style="display: none;">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Nicht gewusst
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fortschritt -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Fortschritt</small>
                            <small class="text-muted">
                                <span id="correctCount">0</span> richtig, 
                                <span id="wrongCount">0</span> falsch
                            </small>
                        </div>
                        <div class="progress">
                            <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <?php } else { ?>
                <!-- Keine Vokabeln -->
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-4">Keine Vokabeln gefunden!</h4>
                        <p>Für diese Einheit sind keine Vokabeln verfügbar.</p>
                        <a href="einheiten.php" class="btn btn-primary">Zurück zur Übersicht</a>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<!-- Erfolgs Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-trophy me-2"></i>
                    Gratulation!
                </h5>
            </div>
            <div class="modal-body text-center">
                <h4>Du hast alle Vokabeln durchgearbeitet!</h4>
                <p class="mb-0">
                    <strong id="finalScore"></strong>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="restartCards()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Nochmal lernen
                </button>
                <a href="einheiten.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Zurück zur Übersicht
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// PHP Daten in JavaScript übertragen
const vocabularyData = <?php echo json_encode($words); ?>;
const unitId = <?php echo $unitid; ?>;

// Globale Variablen
let currentWords = [];
let currentIndex = 0;
let correctCount = 0;
let wrongCount = 0;
let recognition = null;
let isListening = false;
let currentDirection = '<?php echo $direction; ?>';
let currentAnswer = '';
let showingAnswer = false;

// DOM Elemente
const elements = {
    questionWord: document.getElementById('questionWord'),
    answerWord: document.getElementById('answerWord'),
    speechStatus: document.getElementById('speechStatus'),
    recognizedText: document.getElementById('recognizedText'),
    spokenText: document.getElementById('spokenText'),
    answerTranslation: document.getElementById('answerTranslation'),
    flashcard: document.getElementById('flashcard'),
    listenBtn: document.getElementById('listenBtn'),
    showAnswerBtn: document.getElementById('showAnswerBtn'),
    correctBtn: document.getElementById('correctBtn'),
    wrongBtn: document.getElementById('wrongBtn'),
    currentCard: document.getElementById('currentCard'),
    correctCountEl: document.getElementById('correctCount'),
    wrongCountEl: document.getElementById('wrongCount'),
    progressBar: document.getElementById('progressBar'),
    finalScore: document.getElementById('finalScore'),
    headerText: document.getElementById('headerText')
};

// Speech Recognition Setup
function initSpeechRecognition() {
    if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
        showBrowserWarning();
        return false;
    }
    
    try {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = currentDirection === 'de-en' ? 'en-US' : 'de-DE';
        recognition.maxAlternatives = 1;
        
        recognition.onstart = function() {
            isListening = true;
            elements.listenBtn.classList.add('recording');
            elements.listenBtn.innerHTML = '<i class="bi bi-mic-fill me-2"></i>Spreche jetzt...';
            elements.speechStatus.className = 'alert alert-warning';
            elements.speechStatus.innerHTML = '<i class="bi bi-mic me-2"></i>Höre zu... (5 Sekunden)';
            
            setTimeout(() => {
                if (isListening && recognition) {
                    recognition.stop();
                }
            }, 5000);
        };
        
        recognition.onresult = function(event) {
            const spokenText = event.results[0][0].transcript.toLowerCase().trim();
            const confidence = event.results[0][0].confidence || 0;
            
            elements.spokenText.textContent = `${spokenText} (${Math.round(confidence * 100)}%)`;
            elements.recognizedText.style.display = 'block';
            
            checkAnswer(spokenText);
        };
        
        recognition.onend = function() {
            isListening = false;
            elements.listenBtn.classList.remove('recording');
            elements.listenBtn.innerHTML = '<i class="bi bi-mic-fill me-2"></i>Sprechen';
        };
        
        recognition.onerror = function(event) {
            isListening = false;
            elements.listenBtn.classList.remove('recording');
            elements.listenBtn.innerHTML = '<i class="bi bi-mic-fill me-2"></i>Sprechen';
            
            let errorMessage = 'Unbekannter Fehler';
            switch(event.error) {
                case 'not-allowed':
                    errorMessage = 'Mikrofon-Zugriff verweigert.';
                    break;
                case 'no-speech':
                    errorMessage = 'Keine Sprache erkannt.';
                    break;
                case 'audio-capture':
                    errorMessage = 'Mikrofon nicht verfügbar.';
                    break;
                case 'network':
                    errorMessage = 'Netzwerkfehler.';
                    break;
            }
            
            elements.speechStatus.className = 'alert alert-danger';
            elements.speechStatus.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${errorMessage}`;
        };
        
        return true;
        
    } catch (error) {
        showBrowserWarning();
        return false;
    }
}

function showBrowserWarning() {
    elements.speechStatus.className = 'alert alert-warning';
    elements.speechStatus.innerHTML = `
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Browser nicht unterstützt</strong><br>
        <small>Bitte verwende Chrome, Edge oder Opera für die Spracherkennung.</small>
    `;
    elements.listenBtn.disabled = true;
}

// Antwort überprüfen
function checkAnswer(spokenText) {
    const correctAnswer = currentAnswer.toLowerCase();
    const similarity = calculateSimilarity(spokenText, correctAnswer);
    
    if (similarity > 0.7) {
        showCorrectFeedback();
    } else {
        showIncorrectFeedback();
    }
}

function showCorrectFeedback() {
    elements.flashcard.classList.add('correct');
    elements.speechStatus.className = 'alert alert-success';
    elements.speechStatus.innerHTML = '<i class="bi bi-check-circle me-2"></i>Richtig! Gut gemacht!';
    showAnswerButtons();
}

function showIncorrectFeedback() {
    elements.flashcard.classList.add('incorrect');
    elements.speechStatus.className = 'alert alert-danger';
    elements.speechStatus.innerHTML = '<i class="bi bi-x-circle me-2"></i>Fast! Versuche es nochmal oder zeige die Antwort.';
    
    setTimeout(() => {
        elements.flashcard.classList.remove('incorrect');
        resetForRetry();
    }, 2000);
}

function resetForRetry() {
    elements.speechStatus.className = 'alert alert-info';
    elements.speechStatus.innerHTML = '<i class="bi bi-mic me-2"></i>Bereit für Spracherkennung';
}

// Ähnlichkeitsberechnung
function calculateSimilarity(str1, str2) {
    const longer = str1.length > str2.length ? str1 : str2;
    const shorter = str1.length > str2.length ? str2 : str1;
    
    if (longer.length === 0) return 1.0;
    
    const distance = levenshteinDistance(longer, shorter);
    return (longer.length - distance) / longer.length;
}

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

// Karte laden
function loadCurrentCard() {
    if (currentIndex >= currentWords.length) {
        showCompletionModal();
        return;
    }
    
    const vocab = currentWords[currentIndex];
    showingAnswer = false;
    
    // Richtung bestimmen
    let questionText, answerText;
    if (currentDirection === 'de-en') {
        questionText = vocab.german_word;
        answerText = vocab.english_word;
        elements.headerText.textContent = 'Spreche das englische Wort aus';
        if (recognition) recognition.lang = 'en-US';
    } else {
        questionText = vocab.english_word;
        answerText = vocab.german_word;
        elements.headerText.textContent = 'Spreche das deutsche Wort aus';
        if (recognition) recognition.lang = 'de-DE';
    }
    
    currentAnswer = answerText;
    
    // UI zurücksetzen
    elements.flashcard.classList.remove('correct', 'incorrect');
    elements.questionWord.textContent = questionText;
    elements.answerWord.textContent = answerText;
    elements.answerTranslation.style.display = 'none';
    elements.recognizedText.style.display = 'none';
    
    // Buttons zurücksetzen
    elements.showAnswerBtn.style.display = 'block';
    elements.listenBtn.disabled = false;
    hideAnswerButtons();
    
    // Status zurücksetzen
    elements.speechStatus.className = 'alert alert-info';
    elements.speechStatus.innerHTML = '<i class="bi bi-mic me-2"></i>Bereit für Spracherkennung';
    
    // UI aktualisieren
    elements.currentCard.textContent = currentIndex + 1;
    updateProgress();
}

function showAnswer() {
    elements.answerTranslation.style.display = 'block';
    showAnswerButtons();
    showingAnswer = true;
}

function showAnswerButtons() {
    elements.showAnswerBtn.style.display = 'none';
    elements.correctBtn.style.display = 'block';
    elements.wrongBtn.style.display = 'block';
    elements.listenBtn.disabled = true;
}

function hideAnswerButtons() {
    elements.correctBtn.style.display = 'none';
    elements.wrongBtn.style.display = 'none';
}

// Antwort verarbeiten
function handleAnswer(isCorrect) {
    const vocab = currentWords[currentIndex];
    
    // AJAX Request an Server
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'answer': isCorrect ? 'right' : 'wrong',
            'gvocabid': vocab.gvocabid,
            'evocabid': vocab.evocabid
        })
    });
    
    // Statistiken aktualisieren
    if (isCorrect) {
        correctCount++;
        if (!showingAnswer) { // Nur wenn durch Sprache erkannt
            elements.flashcard.classList.add('correct');
        }
    } else {
        wrongCount++;
    }
    
    updateStats();
    
    // Nächste Karte nach kurzer Verzögerung
    setTimeout(() => {
        nextCard();
    }, 1000);
}

function nextCard() {
    currentIndex++;
    
    // Animation
    elements.flashcard.classList.add('card-flip-out');
    setTimeout(() => {
        loadCurrentCard();
        elements.flashcard.classList.remove('card-flip-out');
        elements.flashcard.classList.add('card-flip-in');
        setTimeout(() => {
            elements.flashcard.classList.remove('card-flip-in');
        }, 300);
    }, 300);
}

// Statistiken und Fortschritt
function updateStats() {
    elements.correctCountEl.textContent = correctCount;
    elements.wrongCountEl.textContent = wrongCount;
}

function updateProgress() {
    const progress = (currentIndex / currentWords.length) * 100;
    elements.progressBar.style.width = progress + '%';
}

// Abschluss Modal
function showCompletionModal() {
    const total = correctCount + wrongCount;
    const accuracy = total > 0 ? Math.round((correctCount / total) * 100) : 0;
    elements.finalScore.innerHTML = `
        Du hast <strong>${correctCount}</strong> von <strong>${currentWords.length}</strong> Vokabeln richtig beantwortet!<br>
        <small class="text-muted">Genauigkeit: ${accuracy}%</small>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}

// Neustart
function restartCards() {
    currentIndex = 0;
    correctCount = 0;
    wrongCount = 0;
    currentWords = shuffleArray([...vocabularyData]);
    updateStats();
    loadCurrentCard();
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
    if (modal) modal.hide();
}

// Array mischen
function shuffleArray(array) {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    if (vocabularyData.length === 0) {
        return;
    }
    
    // Speech Recognition initialisieren
    initSpeechRecognition();
    
    // Daten vorbereiten
    currentWords = shuffleArray([...vocabularyData]);
    
    // Event Listeners
    elements.listenBtn.addEventListener('click', function() {
        if (recognition && !isListening) {
            recognition.start();
        }
    });
    
    elements.showAnswerBtn.addEventListener('click', showAnswer);
    elements.correctBtn.addEventListener('click', () => handleAnswer(true));
    elements.wrongBtn.addEventListener('click', () => handleAnswer(false));
    
    // Direction Toggle
    document.querySelectorAll('input[name="direction"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const newDirection = this.value;
            window.location.href = `?unit=${unitId}&direction=${newDirection}`;
        });
    });
    
    // Erste Karte laden
    loadCurrentCard();
});

// Global verfügbare Funktionen
window.restartCards = restartCards;
</script>

</body>
<br>
<br>
<br>
<br>
<br>
<br>
</html>


<?php include 'footer.php'; ?>