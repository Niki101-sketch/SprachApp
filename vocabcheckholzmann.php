<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vokabel-Übung</title>
    <!-- Bootstrap 5 CSS -->
     <link  href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"  rel="stylesheet">
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

       
     </style>
</head>
<body>
     <div class="container py-4">
         <div class="row mb-4">
             <div class="col-12 text-center">
                 <h1 class="display-5 mb-3">Vokabel-Übung</h1>
                 <p class="lead">Verbinde die deutschen Wörter mit den entsprechenden englischen Übersetzungen</p>
                 <div class="alert alert-info" role="alert">
                     Klicke zuerst auf ein deutsches Wort und dann auf die passende englische Übersetzung
                 </div>
             </div>
         </div>

         <div class="row mb-3">
             <div class="col-12 text-center">
                 <div class="score-display">
                     Punkte: <span id="score">0</span> / <span id="total-pairs">0</span>
                 </div>
             </div>
         </div>

         <div class="row">
             <div class="col-md-5">
                 <h3 class="mb-3 text-center">Deutsch</h3>
                 <div id="german-words" class="d-flex flex-column"></div>
             </div>

             <div class="col-md-2 d-flex align-items-center justify-content-center">
                 <div class="d-none d-md-block ">
                     <h1 class="display-1">X</h1>
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
             </div>
         </div>


     </div>

    

    <script>
/*
LERNSTOFF:
==========================================================================
HTML5 data-* Attribute
==========================================================================
Das data-* Attribut ist ein benutzerdefiniertes Attribut, das in HTML5 verwendet wird, 
um zusätzliche Informationen zu einem Element zu speichern.  
In JavaScript können Sie auf diese Attribute über die dataset-Eigenschaft des Elements 
zugreifen.
==========================================================================
In HTML:
--------
<div id="user" data-id="1234567890" data-user="carinaanand" data-date-of-birth>
  Carina Anand
</div>

In JavaScript:
---------------
const el = document.querySelector("#user");

Attributes can be set and read by the camelCase name/key as an object property of the dataset: 
    element.dataset.keyname.
Attributes can also be set and read using bracket syntax: 
    element.dataset['keyname'].

The in operator can check if a given attribute exists: 
    'keyname' in element.dataset. 
        Note that this will walk the prototype chain of dataset and may be unsafe if
        you have external code that may pollute the prototype chain. 
Several alternatives exist, such as 
    Object.hasOwn(element.dataset, 'keyname'), 
or just checking 
    if element.dataset.keyname !== undefined.

When the attribute is set, its value is always converted to a string. !!!
    ement.dataset.example = null is converted into data-example="null".

To remove an attribute, you can use the delete operator: 
    delete element.dataset.keyname.

// Example:
// el.id === 'user'
// el.dataset.id === '1234567890'
// el.dataset.user === 'carinaanand'
// el.dataset.dateOfBirth === ''

// set a data attribute
el.dataset.dateOfBirth = "1960-10-03";
// Result on JS: el.dataset.dateOfBirth === '1960-10-03'
// Result on HTML: <div id="user" data-id="1234567890" data-user="carinaanand" data-date-of-birth="1960-10-03">Carina Anand</div>

delete el.dataset.dateOfBirth;
// Result on JS: el.dataset.dateOfBirth === undefined
// Result on HTML: <div id="user" data-id="1234567890" data-user="carinaanand">Carina Anand</div>

if (el.dataset.someDataAttr === undefined) {
  el.dataset.someDataAttr = "mydata";
  // Result on JS: 'someDataAttr' in el.dataset === true
  // Result on HTML: <div id="user" data-id="1234567890" data-user="carinaanand" data-some-data-attr="mydata">Carina Anand</div>
}


*/




// Funktion zur Anzeige von Fehlermeldungen
function showError(message) {
    const resultMessage =  document.getElementById('result-message');
    resultMessage.textContent = message;  // Setze den Text im Element der Fehlermeldung
    resultMessage.style.display = 'block';
    resultMessage.className = 'result-message alert alert-danger';
}



// Variablen für das Spiel
// Die Vokabeln werden in einem Array gespeichert,
// wobei jedes Element ein Objekt mit den deutschen und englischen Wörtern ist
let vocabularyPairs = [];
// Die Anzahl der Vokabelpaare wird in der Variable totalPairs gespeichert
let totalPairs = 0;

// Variablen für den Spielstatus
// Diese Variablen speichern die aktuell ausgewählten Karten
let selectedGermanCard = null;
let selectedEnglishCard = null;
// Diese Variable speichert die Anzahl der korrekt zugeordneten Paare
let correctPairs = 0;


// Funktion zum Laden der Vokabeln
//===========================================================================
// AJAX Funktion zum Laden der Vokabeln von der Seite get_vocabulary.php
// Diese Funktion gibt es nicht, es ist nur als Beispiel gedacht wie ihr
// die Vokabeln von der PHP-Seite laden könnt
function loadVocabulary() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_vocabulary.php', true);

    xhr.onload = function() {
        if (this.status === 200) {
            try {
                vocabularyPairs = JSON.parse(this.responseText);
                // Nur die ersten 5 Paare nehmen
                vocabularyPairs = vocabularyPairs.slice(0, 5);
                totalPairs = vocabularyPairs.length;
                    
            } catch (e) {
                showError("Fehler beim Parsen der Vokabeldaten: " + e.message);
                // Fallback: Beispielvokabeln verwenden
                useFallbackVocabulary();
            }
        } else {
            showError("Fehler beim Laden der Vokabeln: "  + this.status);
            // Fallback: Beispielvokabeln verwenden
            useFallbackVocabulary();
        }
        
        document.getElementById('total-pairs').textContent = totalPairs;
        initializeGame();
    };

    xhr.onerror = function() {
        showError("Netzwerkfehler beim Laden der Vokabeln. Bitte überprüfe deine Verbindung.");
        // Fallback: Beispielvokabeln verwenden
        useFallbackVocabulary();
        document.getElementById('total-pairs').textContent = totalPairs;
        initializeGame();
    };
    xhr.send();
}

// Unsere Vokabeln in diesem Beispiel 
//===========================================================================        
// Fallback-Vokabeln, falls die PHP-Datei nicht geladen werden kann
function useFallbackVocabulary() {
    vocabularyPairs = [
        { german: "Haus", english: "house" },
        { german: "Auto", english: "car" },
        { german: "Baum", english: "tree" },
        { german: "Buch", english: "book" },
        { german: "Tisch", english: "table" }
    ];
    totalPairs = vocabularyPairs.length;
}




// Funktion zum Behandeln von Klicks auf Wortkarten
function handleCardClick(card) {
    // Ignoriere Klicks auf bereits übereinstimmende Karten
    if (card.classList.contains('correct')) {
        return;
    }
    // Aus dem Element die Sparache auslesen
    // und die Karte in der Variable speichern
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
    // Wir lesen die information aus den Karten aus
    const germanWord = selectedGermanCard.dataset.word;
    const englishWord = selectedEnglishCard.dataset.word;

    // Finde das Paar für das deutsche Wort mittels einer For-Schleife
    let correctPair = null;
    for (let i = 0; i < vocabularyPairs.length; i++) {
        let pair = vocabularyPairs[i];
        if (pair.german === germanWord) {
            correctPair = vocabularyPairs[i];
            break;
        }
    }
    // Mögliche Kurzform mittels der Methode find():
    //-----------------------------------------------
    // const correctPair = vocabularyPairs.find(pair => pair.german === germanWord);
    // Erklärung: liefere das aktuelle Element (pair) wenn der Code nach dem => true liefert 

    // Wir haben das deutsche Wort gefunden, jetzt überprüfen wir die englische Übersetzung
    if (correctPair && correctPair.english === englishWord) {
        // Richtige Übereinstimmung: Ändern der Kartenfarbe mittels der Class-Einträge
        selectedGermanCard.classList.remove('selected');
        selectedEnglishCard.classList.remove('selected');
        selectedGermanCard.classList.add('correct');
        selectedEnglishCard.classList.add('correct');

        // Erhöhe den Punktestand
        correctPairs++;
        document.getElementById('score').textContent = correctPairs;

        // Überprüfe, ob alle Paare gefunden wurden
        if (correctPairs === totalPairs) {
            const resultMessage =  document.getElementById('result-message');
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

        // Kurz anzeigen, dass die Zuordnung falsch ist:
        // Warte 1 Sekunde und setze die Karten zurück:
        // ------------------------------------------------
        // setTimeout() ist eine JavaScript-Funktion, die eine Funktion nach einer 
        // bestimmten Zeit ausführt
        setTimeout( 
            () => {
            selectedGermanCard.classList.remove('selected', 'wrong');
            selectedEnglishCard.classList.remove('selected', 'wrong');
            selectedGermanCard = null;
            selectedEnglishCard = null;
            }, 
            1000
        );  // Ende von setTimeout
    }
}




// Fisher-Yates shuffle
// ===========================================================================
// Diese Funktion mischt ein Array in zufälliger Reihenfolge
// Sie verwendet den Fisher-Yates-Algorithmus, um eine faire Zufallsanordnung zu gewährleisten
// siehe https://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle
function shuffle(array) {
  // Erstelle eine Kopie des Arrays, um das Original nicht zu verändern
  // [...array] ist eine ES6-Syntax, die ein flaches Kopieren des Arrays ermöglicht
  // Dies ist wichtig, um das Original-Array nicht zu verändern
  let arr = [...array];  // Kopiere das Array
  // Durchlaufe das Array rückwärts und tausche das Element mit einem zufälligen Element
  for (let i = arr.length - 1; i > 0; i--) {
    // Generiere einen zufälligen Index zwischen 0 und i:
    //------------------------------------------------------
    // Math.random() gibt eine Zufallszahl zwischen 0 (inklusive) und 1 (exklusive) zurück
    // Math.floor() rundet die Zahl ab, um einen ganzzahligen Index zu erhalten
    const j = Math.floor(Math.random() * (i + 1));
    // Tausche die Elemente an den Indizes i und j
    let helper = arr[i];
    arr[i] = arr[j];    
    arr[j] = helper;
  }
  return arr;
}



// JavaScript Funktion zum Erstellen einer Wortkarte:
//----------------------------------------------------
// Diese Funktion wird verwendet, um die HTML-Elemente für die Karten zu erstellen
function createWordCardHTML(word, language, index) {
    let str="<div " + 
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
    const germanContainer =  document.getElementById('german-words');
    const englishContainer =  document.getElementById('english-words');

    // Container leeren
    germanContainer.innerHTML = '';
    englishContainer.innerHTML = '';

    // Deutsche Wörter in zufälliger Reihenfolge anzeigen
    const shuffledGerman = shuffle(vocabularyPairs);
    
    for (let index = 0; index < shuffledGerman.length; index++) {
        let pair = shuffledGerman[index];
        let html = createWordCardHTML(pair.german, 'german', index);
        germanContainer.innerHTML += html;
    };

    // Englische Wörter in zufälliger Reihenfolge anzeigen
    const shuffledEnglish = shuffle(vocabularyPairs);
    for (let index = 0; index < shuffledEnglish.length; index++) {
        let pair = shuffledEnglish[index]
        let html = createWordCardHTML(pair.english, 'english', index);
        englishContainer.innerHTML += html;
    };
}


 window.onload = function() {
    // Initialisiere das Spiel, wenn die Seite geladen ist
    loadVocabulary();
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



     </script>
</body>
</html>