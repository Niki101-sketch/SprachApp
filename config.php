<?php
// config.php - Zentrale Konfigurationsdatei

// Session starten
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting für Entwicklung
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Datenbankeinstellungen
$servername = "sql108.infinityfree.com";
$db_username = "if0_38905283";
$db_password = "ewgjt0aaksuC";
$dbname = "if0_38905283_sprachapp";

// Funktion zur Reparatur von doppelt kodierten UTF-8 Zeichen
function repairUTF8($text) {
    $replacements = [
        'Ã¤' => 'ä', 'Ã¶' => 'ö', 'Ã¼' => 'ü',
        'Ã„' => 'Ä', 'Ã–' => 'Ö', 'Ãœ' => 'Ü',
        'ÃŸ' => 'ß', 'Ã©' => 'é', 'Ã¨' => 'è',
        'Ã¡' => 'á', 'Ã ' => 'à', 'Ã­' => 'í',
        'Ã¬' => 'ì', 'Ã³' => 'ó', 'Ã²' => 'ò',
        'Ãº' => 'ú', 'Ã¹' => 'ù'
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

// Funktion zur Überprüfung der Authentifizierung
function checkAuthentication($redirectToLogin = true) {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        if ($redirectToLogin) {
            $_SESSION['err'] = "Bitte melden Sie sich an, um auf diese Seite zuzugreifen.";
            header("Location: login.php");
            exit();
        }
        return false;
    }
    return true;
}

// Benutzerinformationen abrufen
function getUserInfo() {
    return [
        'username' => isset($_SESSION['username']) ? $_SESSION['username'] : '',
        'role' => isset($_SESSION['role']) ? $_SESSION['role'] : '',
        'isLoggedIn' => checkAuthentication(false)
    ];
}

// Datenbankverbindung mit MySQLi
function getMySQLiConnection() {
    global $servername, $db_username, $db_password, $dbname;
    
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    
    if ($conn->connect_error) {
        die("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// PDO Verbindung
function getPDOConnection() {
    global $servername, $db_username, $db_password, $dbname;
    
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Verbindung fehlgeschlagen: " . $e->getMessage());
    }
}
?>