footer a:hover {
            color: white;
            text-decoration: underline;
        }
        
        /* Spracherkennung Styles - Verbessert */
        #flashcard {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        #flashcard.correct {
            border-color: #28a745;
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
            background: linear-gradient(135deg, #ffffff 0%, #f8fff9 100%);
        }
        
        #flashcard.incorrect {
            border-color: #dc3545;
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.3);
            background: linear-gradient(135deg, #ffffff 0%, #fff8f8 100%);
        }
        
        .recording {
            animation: pulse 1s infinite;
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            position: relative;
        }
        
        .recording::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: blink 0.5s infinite alternate;
        }
        
        @keyframes pulse {
            0% { 
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
                transform: scale(1);
            }
            70% { 
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
                transform: scale(1.05);
            }
            100% { 
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
                transform: scale(1);
            }
        }
        
        @keyframes blink {
            0% { opacity: 1; }
            100% { opacity: 0.3; }
        }
        
        .card-flip-out {
            transform: rotateY(90deg);
            opacity: 0.5;
            transition: all 0.3s ease;
        }
        
        .card-flip-in {
            transform: rotateY(-90deg);
            opacity: 0.5;
            transition: all 0.3s ease;
        }
        
        #recognizedText {
            border-left: 4px solid #6c757d;
            background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 8px;
        }
        
        .progress {
            border-radius: 10px;
            background-color: #e9ecef;
            height: 8px;
            overflow: hidden;
        }
        
        .progress-bar {
            border-radius: 10px;
            transition: width 0.5s ease;
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        }
        
        /* Button Hover Effects */
        .btn-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-lg:active {
            transform: translateY(0);
        }
        
        /* Statistik Cards Animation */
        .card.bg-success, .card.bg-danger {
            transition: all 0.3s ease;
        }
        
        .card.bg-success:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        
        .card.bg-danger:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }
        
        /* Speech Status Icons Animation */
        .alert i.bi-mic {
            animation: breathe 2s infinite;
        }
        
        @keyframes breathe {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Mobile Optimierungen */
        @media (max-width: 768px) {
            .btn-lg {
                padding: 0.6rem 1.2rem;
                font-size: 0.95rem;
                margin: 0.2rem;
            }
            
            #flashcard {
                margin-bottom: 1rem;
            }
            
            .progress {
                height: 6px;
            }
        }
    </style>
    
    <!-- Vokabeln für JavaScript bereitstellen -->
    <script>
        // PHP Vokabeln zu JavaScript konvertieren
        const vokabeln = <?php echo json_encode(array_map(function($word) {
            return [
                'german_word' => $word['german_word'] ?? '',
                'english_word' => $word['english_word'] ?? '',
                'gvocabid' => $word['gvocabid'] ?? 0,
                'evocabid' => $word['evocabid'] ?? 0
            ];
        }, $words)); ?>;
        
        console.log('📚 Vokabeln geladen:', vokabeln.length, 'Einträge');
    </script>
    
    <!-- Speech Recognition Script eingebettet -->
    <script>
        // Globale Variablen
        let currentVokabeln = [];
        let currentIndex = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let recognition = null;
        let isListening = false;
        let currentAnswer = '';

        // DOM Elemente - mit Fehlerprüfung und sicherer Initialisierung
        const elements = {};

        // Sichere DOM-Element-Initialisierung
        function initElements() {
            const elementIds = [
                'germanWord', 'englishWord', 'wordType', 'speechStatus', 
                'recognizedText', 'spokenText', 'englishTranslation', 'flashcard',
                'listenBtn', 'showAnswerBtn', 'nextBtn', 'currentCard', 
                'totalCards', 'correctCount', 'wrongCount', 'progressBar', 'finalScore'
            ];
            
            elementIds.forEach(id => {
                elements[id] = document.getElementById(id);
            });
        }

        // Debug: Prüfe ob alle Elemente vorhanden sind
        function checkElements() {
            const requiredElements = ['germanWord', 'listenBtn', 'showAnswerBtn', 'nextBtn'];
            let missingElements = [];
            
            for (const elementId of requiredElements) {
                if (!elements[elementId]) {
                    missingElements.push(elementId);
                }
            }
            
            if (missingElements.length > 0) {
                console.error('❌ Fehlende DOM-Elemente:', missingElements);
                return false;
            }
            
            console.log('✅ Alle kritischen DOM-Elemente gefunden');
            return true;
        }

        // Sichere Element-Updates
        function safeUpdate(elementId, property, value) {
            if (elements[elementId]) {
                if (property === 'textContent') {
                    elements[elementId].textContent = value;
                } else if (property === 'innerHTML') {
                    elements[elementId].innerHTML = value;
                } else if (property === 'className') {
                    elements[elementId].className = value;
                } else if (property === 'style') {
                    Object.assign(elements[elementId].style, value);
                } else if (property === 'disabled') {
                    elements[elementId].disabled = value;
                }
            }
        }

        // Browser-Kompatibilität prüfen
        function checkBrowserSupport() {
            const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
            const isEdge = /Edg/.test(navigator.userAgent);
            const isOpera = /OPR/.test(navigator.userAgent);
            const isFirefox = /Firefox/.test(navigator.userAgent);
            
            if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
                showBrowserWarning();
                return false;
            }
            
            if (!isChrome && !isEdge && !isOpera && !isFirefox) {
                showBrowserRecommendation();
            }
            
            return true;
        }

        function showBrowserWarning() {
            safeUpdate('speechStatus', 'className', 'alert alert-warning');
            safeUpdate('speechStatus', 'innerHTML', `
                <div>
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Browser nicht unterstützt</strong><br>
                    <small>Bitte verwende Chrome, Edge, Firefox oder Opera für die Spracherkennung.</small>
                </div>
            `);
            safeUpdate('listenBtn', 'disabled', true);
            // Zeige manuelle Kontrollen
            document.getElementById('manualControls').style.display = 'block';
        }

        function showBrowserRecommendation() {
            safeUpdate('speechStatus', 'className', 'alert alert-info');
            safeUpdate('speechStatus', 'innerHTML', `
                <div>
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Für beste Ergebnisse verwende Chrome oder Edge</strong>
                </div>
            `);
        }

        // Speech Recognition Setup mit verbesserter Fehlerbehandlung
        function initSpeechRecognition() {
            if (!checkBrowserSupport()) {
                return false;
            }
            
            try {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                
                if (!SpeechRecognition) {
                    console.error('❌ SpeechRecognition nicht verfügbar');
                    showBrowserWarning();
                    return false;
                }
                
                recognition = new SpeechRecognition();
                
                // Optimierte Einstellungen
                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.lang = 'en-US';
                recognition.maxAlternatives = 3;
                
                recognition.onstart = function() {
                    console.log('🎤 Spracherkennung gestartet');
                    isListening = true;
                    
                    if (elements.listenBtn) {
                        elements.listenBtn.classList.add('recording');
                        elements.listenBtn.innerHTML = '<i class="bi bi-mic-fill me-2"></i>Spreche jetzt...';
                    }
                    
                    safeUpdate('speechStatus', 'className', 'alert alert-warning');
                    safeUpdate('speechStatus', 'innerHTML', '<i class="bi bi-mic me-2"></i>Höre zu... (7 Sekunden)');
                    
                    // Auto-Stop nach 7 Sekunden
                    setTimeout(() => {
                        if (isListening && recognition) {
                            try {
                                recognition.stop();
                            } catch (e) {
                                console.warn('⚠️ Fehler beim Stoppen der Erkennung:', e);
                            }
                        }
                    }, 7000);
                };
                
                recognition.onresult = function(event) {
                    console.log('🗣️ Sprache erkannt:', event.results[0][0].transcript);
                    
                    // Beste Alternative aus mehreren Ergebnissen wählen
                    let bestTranscript = '';
                    let bestConfidence = 0;
                    
                    for (let i = 0; i < event.results[0].length; i++) {
                        const result = event.results[0][i];
                        if (result.confidence > bestConfidence) {
                            bestConfidence = result.confidence;
                            bestTranscript = result.transcript;
                        }
                    }
                    
                    const spokenText = bestTranscript.toLowerCase().trim();
                    const confidence = bestConfidence || 0;
                    
                    safeUpdate('spokenText', 'textContent', `${spokenText} (${Math.round(confidence * 100)}%)`);
                    
                    if (elements.recognizedText) {
                        elements.recognizedText.style.display = 'block';
                    }
                    
                    checkAnswer(spokenText);
                };
                
                recognition.onend = function() {
                    console.log('🔇 Spracherkennung beendet');
                    isListening = false;
                    
                    if (elements.listenBtn) {
                        elements.listenBtn.classList.remove('recording');
                        elements.listenBtn.innerHTML = '<i class="bi bi-mic-fill me-2"></i>Sprechen';
                    }
                };
                
                recognition.onerror = function(event) {
                    console.error('❌ Speech Recognition Fehler:', event.error);
                    isListening = false;
                    
                    if (elements.listenBtn) {
                        elements.listenBtn.classList.remove('recording');
                        elements.listenBtn.innerHTML = '<i class="bi bi-mic-fill me-2"></i>Sprechen';
                    }
                    
                    let errorMessage = 'Unbekannter Fehler';
                    let errorClass = 'alert alert-danger';
                    
                    switch(event.error) {
                        case 'not-allowed':
                            errorMessage = 'Mikrofon-Zugriff verweigert. Bitte erlaube den Zugriff und lade die Seite neu.';
                            break;
                        case 'no-speech':
                            errorMessage = 'Keine Sprache erkannt. Versuche es nochmal und sprich deutlicher.';
                            errorClass = 'alert alert-warning';
                            break;
                        case 'audio-capture':
                            errorMessage = 'Mikrofon nicht verfügbar. Überprüfe deine Geräte-Einstellungen.';
                            break;
                        case 'network':
                            errorMessage = 'Netzwerkfehler. Überprüfe deine Internetverbindung.';
                            break;
                        case 'aborted':
                            errorMessage = 'Spracherkennung wurde abgebrochen.';
                            errorClass = 'alert alert-info';
                            break;
                        case 'service-not-allowed':
                            errorMessage = 'Spracherkennung-Service nicht verfügbar.';
                            break;
                    }
                    
                    safeUpdate('speechStatus', 'className', errorClass);
                    safeUpdate('speechStatus', 'innerHTML', `<i class="bi bi-exclamation-triangle me-2"></i>${errorMessage}`);
                };
                
                console.log('✅ Speech Recognition initialisiert');
                return true;
                
            } catch (error) {
                console.error('❌ Speech Recognition Setup Fehler:', error);
                showBrowserWarning();
                return false;
            }
        }

        // Verbesserte Antwort-Überprüfung
        function checkAnswer(spokenText) {
            console.log('🔍 Überprüfe Antwort:', spokenText, 'vs', currentAnswer);
            
            if (!currentAnswer) {
                console.error('❌ Keine aktuelle Antwort gesetzt');
                return;
            }
            
            const correctAnswer = currentAnswer.toLowerCase().trim();
            const cleanSpoken = spokenText.toLowerCase().trim();
            
            // Mehrere Überprüfungsmethoden
            const exactMatch = cleanSpoken === correctAnswer;
            const similarity = calculateSimilarity(cleanSpoken, correctAnswer);
            const wordsMatch = checkWordMatch(cleanSpoken, correctAnswer);
            
            console.log('📊 Vergleich:', {
                exact: exactMatch,
                similarity: similarity,
                wordsMatch: wordsMatch
            });
            
            // Flexiblere Bewertung
            const isCorrect = exactMatch || similarity > 0.75 || wordsMatch > 0.8;
            
            if (isCorrect) {
                console.log('✅ Richtige Antwort!');
                showCorrectAnswer();
                correctCount++;
                
                if (elements.flashcard) {
                    elements.flashcard.classList.add('correct');
                }
                
                safeUpdate('speechStatus', 'className', 'alert alert-success');
                safeUpdate('speechStatus', 'innerHTML', '<i class="bi bi-check-circle me-2"></i>Richtig! Gut gemacht!');
                
            } else {
                console.log('❌ Falsche Antwort');
                wrongCount++;
                
                if (elements.flashcard) {
                    elements.flashcard.classList.add('incorrect');
                }
                
                safeUpdate('speechStatus', 'className', 'alert alert-danger');
                safeUpdate('speechStatus', 'innerHTML', `
                    <i class="bi bi-x-circle me-2"></i>Fast richtig! 
                    <small class="d-block">Erwartet: "${currentAnswer}" | Erkannt: "${spokenText}"</small>
                `);
                
                // Automatisch nach 3 Sekunden zurücksetzen
                setTimeout(() => {
                    if (elements.flashcard) {
                        elements.flashcard.classList.remove('incorrect');
                    }
                    resetCardForRetry();
                }, 3000);
            }
            
            updateStats();
        }

        // Verbesserte Wort-Übereinstimmung
        function checkWordMatch(spoken, correct) {
            const spokenWords = spoken.split(/\s+/);
            const correctWords = correct.split(/\s+/);
            
            if (spokenWords.length !== correctWords.length) {
                return 0;
            }
            
            let matches = 0;
            for (let i = 0; i < spokenWords.length; i++) {
                const similarity = calculateSimilarity(spokenWords[i], correctWords[i]);
                if (similarity > 0.7) {
                    matches++;
                }
            }
            
            return matches / correctWords.length;
        }

        // Ähnlichkeitsberechnung
        function calculateSimilarity(str1, str2) {
            if (str1 === str2) return 1.0;
            
            const longer = str1.length > str2.length ? str1 : str2;
            const shorter = str1.length > str2.length ? str2 : str1;
            
            if (longer.length === 0) return 1.0;
            
            const distance = levenshteinDistance(longer, shorter);
            return (longer.length - distance) / longer.length;
        }

        function levenshteinDistance(str1, str2) {
            const matrix = Array(str2.length + 1).fill().map(() => Array(str1.length + 1).fill(0));
            
            for (let i = 0; i <= str2.length; i++) matrix[i][0] = i;
            for (let j = 0; j <= str1.length; j++) matrix[0][j] = j;
            
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

        // Array-Shuffle-Funktion
        function shuffleArray(array) {
            const shuffled = [...array];
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }

        // Richtige Antwort anzeigen
        function showCorrectAnswer() {
            safeUpdate('englishWord', 'textContent', currentAnswer);
            
            if (elements.englishTranslation) {
                elements.englishTranslation.style.display = 'block';
            }
            
            // Zeige manuelle Kontrollen für finale Bewertung
            document.getElementById('manualControls').style.display = 'block';
            
            safeUpdate('nextBtn', 'disabled', false);
            safeUpdate('showAnswerBtn', 'disabled', true);
            safeUpdate('listenBtn', 'disabled', true);
        }

        // Karte für Wiederholung zurücksetzen
        function resetCardForRetry() {
            if (elements.recognizedText) {
                elements.recognizedText.style.display = 'none';
            }
            
            safeUpdate('speechStatus', 'className', 'alert alert-info');
            safeUpdate('speechStatus', 'innerHTML', '<i class="bi bi-mic me-2"></i>Bereit für Spracherkennung');
        }

        // Neue Karte laden - VERBESSERT
        function loadCurrentCard() {
            console.log('📋 Lade Karte:', currentIndex, 'von', currentVokabeln.length);
            
            // Validierung
            if (!currentVokabeln || currentVokabeln.length === 0) {
                console.error('❌ Keine Vokabeln vorhanden!');
                safeUpdate('germanWord', 'textContent', 'Fehler: Keine Vokabeln geladen');
                return;
            }
            
            if (currentIndex >= currentVokabeln.length) {
                console.log('🎉 Alle Karten fertig!');
                showCompletionModal();
                return;
            }
            
            const vokabel = currentVokabeln[currentIndex];
            console.log('📖 Aktuelle Vokabel:', vokabel);
            
            if (!vokabel) {
                console.error('❌ Vokabel nicht definiert!');
                return;
            }
            
            // Flexible Eigenschaften-Zugriff
            currentAnswer = vokabel.english_word || vokabel.englisch || vokabel.english || 'Unbekannt';
            
            // Karte zurücksetzen
            if (elements.flashcard) {
                elements.flashcard.classList.remove('correct', 'incorrect');
            }
            
            safeUpdate('germanWord', 'textContent', vokabel.german_word || vokabel.deutsch || vokabel.german || 'Fehler');
            safeUpdate('wordType', 'textContent', 'Spreche die englische Übersetzung');
            safeUpdate('englishWord', 'textContent', currentAnswer);
            
            if (elements.englishTranslation) {
                elements.englishTranslation.style.display = 'none';
            }
            if (elements.recognizedText) {
                elements.recognizedText.style.display = 'none';
            }
            
            // Manuelle Kontrollen verstecken
            document.getElementById('manualControls').style.display = 'none';
            
            // Buttons zurücksetzen
            safeUpdate('nextBtn', 'disabled', true);
            safeUpdate('showAnswerBtn', 'disabled', false);
            safeUpdate('listenBtn', 'disabled', false);
            
            // Status zurücksetzen
            safeUpdate('speechStatus', 'className', 'alert alert-info');
            safeUpdate('speechStatus', 'innerHTML', '<i class="bi bi-mic me-2"></i>Bereit für Spracherkennung');
            
            // UI aktualisieren
            safeUpdate('currentCard', 'textContent', currentIndex + 1);
            updateProgress();
            
            console.log('✅ Karte geladen:', vokabel.german_word || vokabel.deutsch, '->', currentAnswer);
        }

        // Statistiken aktualisieren
        function updateStats() {
            safeUpdate('correctCount', 'textContent', correctCount);
            safeUpdate('wrongCount', 'textContent', wrongCount);
        }

        // Fortschritt aktualisieren
        function updateProgress() {
            if (elements.progressBar) {
                const progress = ((currentIndex) / currentVokabeln.length) * 100;
                elements.progressBar.style.width = progress + '%';
            }
        }

        // Abschluss-Modal anzeigen
        function showCompletionModal() {
            const accuracy = Math.round((correctCount / (correctCount + wrongCount)) * 100) || 0;
            
            safeUpdate('finalScore', 'innerHTML', `
                Du hast <strong>${correctCount}</strong> von <strong>${currentVokabeln.length}</strong> Vokabeln richtig beantwortet!<br>
                <small class="text-muted">Genauigkeit: ${accuracy}%</small>
            `);
            
            const modalElement = document.getElementById('successModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                // Fallback ohne Bootstrap Modal
                alert(`Spiel beendet! Du hast ${correctCount} von ${currentVokabeln.length} Vokabeln richtig beantwortet.`);
            }
        }

        // Spiel neu starten
        function restartGame() {
            console.log('🔄 Starte Spiel neu');
            currentIndex = 0;
            correctCount = 0;
            wrongCount = 0;
            
            // Vokabeln neu mischen
            if (vokabeln && vokabeln.length > 0) {
                currentVokabeln = shuffleArray([...vokabeln]);
                console.log('📚 Vokabeln gemischt:', currentVokabeln.length, 'Einträge');
            } else {
                console.error('❌ Keine Vokabeln verfügbar für Neustart!');
                return;
            }
            
            updateStats();
            loadCurrentCard();
            
            // Modal schließen
            const modalElement = document.getElementById('successModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
        }

        // Hauptinitialisierung
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM geladen, initialisiere Sprach-App...');
            
            // DOM-Elemente initialisieren
            initElements();
            
            // Prüfe DOM-Elemente
            if (!checkElements()) {
                console.error('❌ Kritische DOM-Elemente fehlen!');
                return;
            }
            
            // Vokabeln prüfen
            if (!vokabeln || vokabeln.length === 0) {
                console.error('❌ Vokabeln nicht geladen!');
                safeUpdate('germanWord', 'textContent', 'Fehler: Vokabeln nicht geladen');
                return;
            }
            
            console.log('📚 Vokabeln gefunden:', vokabeln.length, 'Einträge');
            
            // Speech Recognition initialisieren
            const speechSuccess = initSpeechRecognition();
            console.log('🎤 Speech Recognition:', speechSuccess ? 'erfolgreich' : 'fehlgeschlagen');
            
            // Spiel initialisieren
            currentVokabeln = shuffleArray([...vokabeln]);
            safeUpdate('totalCards', 'textContent', currentVokabeln.length);
            
            // Event Listeners hinzufügen
            if (elements.listenBtn) {
                elements.listenBtn.addEventListener('click', function() {
                    console.log('🎤 Sprechen-Button geklickt');
                    if (recognition && !isListening) {
                        try {
                            recognition.start();
                        } catch (error) {
                            console.error('❌ Fehler beim Starten der Spracherkennung:', error);
                            safeUpdate('speechStatus', 'className', 'alert alert-danger');
                            safeUpdate('speechStatus', 'innerHTML', '<i class="bi bi-exclamation-triangle me-2"></i>Spracherkennung konnte nicht gestartet werden');
                        }
                    } else if (!recognition) {
                        showBrowserWarning();
                    } else {
                        console.warn('⚠️ Spracherkennung läuft bereits');
                    }
                });
            }
            
            if (elements.showAnswerBtn) {
                elements.showAnswerBtn.addEventListener('click', function() {
                    console.log('👁️ Antwort-zeigen-Button geklickt');
                    showCorrectAnswer();
                });
            }
            
            if (elements.nextBtn) {
                elements.nextBtn.addEventListener('click', function() {
                    console.log('➡️ Weiter-Button geklickt');
                    currentIndex++;
                    
                    // Karten-Animation mit Fehlerbehandlung
                    if (elements.flashcard) {
                        elements.flashcard.classList.add('card-flip-out');
                        setTimeout(() => {
                            loadCurrentCard();
                            elements.flashcard.classList.remove('card-flip-out');
                            elements.flashcard.classList.add('card-flip-in');
                            setTimeout(() => {
                                elements.flashcard.classList.remove('card-flip-in');
                            }, 300);
                        }, 300);
                    } else {
                        loadCurrentCard();
                    }
                });
            }
            
            // Erste Karte laden
            loadCurrentCard();
            
            console.log('✅ Sprach-App vollständig geladen!');
        });

        // Global verfügbare Funktionen
        window.restartGame = restartGame;
        window.checkAnswer = checkAnswer;
        window.loadCurrentCard = loadCurrentCard;
    </script>
    
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
    </script><?php 
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
$studentid = 1; // Fest für Demo 

// Current Index initialisieren - FIX für undefined key
$current = 0;
if (isset($_GET['current']) && is_numeric($_GET['current'])) {
    $current = intval($_GET['current']);
}

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

// Page Title für Header setzen
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
                            <h2 class="mb-4"><?php echo htmlspecialchars($words[$current]['german_word'] ?? ''); ?></h2>
                            <form method="post">
                                <button type="submit" name="show" class="btn btn-primary">Umdrehen</button>
                                <button type="submit" name="skip" value="1" class="btn btn-secondary mx-2">Überspringen</button>
                                <input type="hidden" name="current" value="<?php echo $current; ?>">
                            </form>
                        <?php 
                        } else { 
                        ?>
                            <h2 class="mb-4"><?php echo htmlspecialchars($words[$current]['english_word'] ?? ''); ?></h2>
                            <form method="post" action="?unit=<?php echo $unitid; ?>&current=<?php echo $current + 1; ?>">
                                <button type="submit" name="answer" value="right" class="btn btn-success mx-2">Richtig gewusst</button>
                                <button type="submit" name="answer" value="wrong" class="btn btn-danger mx-2">Falsch gewusst</button>
                                <input type="hidden" name="gvocabid" value="<?php echo $words[$current]['gvocabid'] ?? 0; ?>">
                                <input type="hidden" name="evocabid" value="<?php echo $words[$current]['evocabid'] ?? 0; ?>">
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

    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Arial, Helvetica, sans-serif;
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
    </style>
    
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