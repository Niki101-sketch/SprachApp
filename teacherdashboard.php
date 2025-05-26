<?php
// Datenbankverbindung
include 'connection.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}

// Session starten
session_start();

// Überprüfen ob Benutzer angemeldet und Lehrer ist
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    $_SESSION['err'] = "Bitte melden Sie sich an, um auf diese Seite zuzugreifen.";
    header("Location: login.php");
    exit();
}
/*
if ($_SESSION['role'] !== 'teacher') {
    header("Location: index2.php");
    exit();
}
    keine Ahnung warum das nicht funktioniert*/ 

// Benutzerinformationen aus der Session holen
$teachername = $_SESSION['username'];
$teacherid = $_SESSION['userid'];
$role = $_SESSION['role'];

// AJAX-Handler für verschiedene Aktionen
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_group':
            $groupname = $_POST['groupname'];
            $grouppassword = $_POST['grouppassword'];
            
            try {
                $stmt = $pdo->prepare("INSERT INTO `group` (groupname, teacherid, password) VALUES (?, ?, ?)");
                $stmt->execute([$groupname, $teacherid, password_hash($grouppassword, PASSWORD_DEFAULT)]);
                echo json_encode(['success' => true, 'message' => 'Gruppe erfolgreich erstellt']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Erstellen der Gruppe']);
            }
            exit();
            
        case 'get_groups':
            try {
                $stmt = $pdo->prepare("SELECT g.*, COUNT(DISTINCT s.studentid) as student_count, COUNT(DISTINCT u.unitid) as unit_count 
                                     FROM `group` g 
                                     LEFT JOIN student s ON g.groupid = s.groupid 
                                     LEFT JOIN unit u ON g.groupid = u.groupid 
                                     WHERE g.teacherid = ? 
                                     GROUP BY g.groupid");
                $stmt->execute([$teacherid]);
                $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'groups' => $groups]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Laden der Gruppen']);
            }
            exit();
            
        case 'get_group_details':
            $groupid = $_POST['groupid'];
            try {
                // Gruppe laden
                $stmt = $pdo->prepare("SELECT * FROM `group` WHERE groupid = ? AND teacherid = ?");
                $stmt->execute([$groupid, $teacherid]);
                $group = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Schüler laden
                $stmt = $pdo->prepare("SELECT * FROM student WHERE groupid = ?");
                $stmt->execute([$groupid]);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Units laden
                $stmt = $pdo->prepare("SELECT * FROM unit WHERE groupid = ?");
                $stmt->execute([$groupid]);
                $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'group' => $group, 'students' => $students, 'units' => $units]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Laden der Gruppendetails']);
            }
            exit();
            
        case 'create_unit':
            $groupid = $_POST['groupid'];
            $unitname = $_POST['unitname'];
            
            try {
                $stmt = $pdo->prepare("INSERT INTO unit (groupid, unitname) VALUES (?, ?)");
                $stmt->execute([$groupid, $unitname]);
                echo json_encode(['success' => true, 'message' => 'Unit erfolgreich erstellt']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Erstellen der Unit']);
            }
            exit();
            
        case 'delete_unit':
            $unitid = $_POST['unitid'];
            
            try {
                $pdo->beginTransaction();
                
                // Erst alle Vokabeln löschen
                $stmt = $pdo->prepare("DELETE FROM vocabmapping WHERE gvocabid IN (SELECT gvocabid FROM vocabgerman WHERE unitid = ?)");
                $stmt->execute([$unitid]);
                
                $stmt = $pdo->prepare("DELETE FROM vocabmapping WHERE evocabid IN (SELECT evocabid FROM vocabenglish WHERE unitid = ?)");
                $stmt->execute([$unitid]);
                
                $stmt = $pdo->prepare("DELETE FROM vocabgerman WHERE unitid = ?");
                $stmt->execute([$unitid]);
                
                $stmt = $pdo->prepare("DELETE FROM vocabenglish WHERE unitid = ?");
                $stmt->execute([$unitid]);
                
                // Dann die Unit löschen
                $stmt = $pdo->prepare("DELETE FROM unit WHERE unitid = ?");
                $stmt->execute([$unitid]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Unit erfolgreich gelöscht']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen der Unit']);
            }
            exit();
            
        case 'get_vocab':
            $unitid = $_POST['unitid'];
            
            try {
                $stmt = $pdo->prepare("
                    SELECT vg.gvocabid, vg.german_word, ve.evocabid, ve.english_word 
                    FROM vocabmapping vm
                    JOIN vocabgerman vg ON vm.gvocabid = vg.gvocabid
                    JOIN vocabenglish ve ON vm.evocabid = ve.evocabid
                    WHERE vg.unitid = ?
                ");
                $stmt->execute([$unitid]);
                $vocab = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'vocab' => $vocab]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Laden der Vokabeln']);
            }
            exit();
            
        case 'add_vocab':
            $unitid = $_POST['unitid'];
            $german = $_POST['german'];
            $english = $_POST['english'];
            
            try {
                $pdo->beginTransaction();
                
                // Deutsche Vokabel einfügen
                $stmt = $pdo->prepare("INSERT INTO vocabgerman (unitid, german_word) VALUES (?, ?)");
                $stmt->execute([$unitid, $german]);
                $gvocabid = $pdo->lastInsertId();
                
                // Englische Vokabel einfügen
                $stmt = $pdo->prepare("INSERT INTO vocabenglish (unitid, english_word) VALUES (?, ?)");
                $stmt->execute([$unitid, $english]);
                $evocabid = $pdo->lastInsertId();
                
                // Mapping erstellen
                $stmt = $pdo->prepare("INSERT INTO vocabmapping (gvocabid, evocabid) VALUES (?, ?)");
                $stmt->execute([$gvocabid, $evocabid]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Vokabel erfolgreich hinzugefügt']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Fehler beim Hinzufügen der Vokabel']);
            }
            exit();
            
        case 'delete_vocab':
            $gvocabid = $_POST['gvocabid'];
            $evocabid = $_POST['evocabid'];
            
            try {
                $pdo->beginTransaction();
                
                // Mapping löschen
                $stmt = $pdo->prepare("DELETE FROM vocabmapping WHERE gvocabid = ? AND evocabid = ?");
                $stmt->execute([$gvocabid, $evocabid]);
                
                // Deutsche Vokabel löschen
                $stmt = $pdo->prepare("DELETE FROM vocabgerman WHERE gvocabid = ?");
                $stmt->execute([$gvocabid]);
                
                // Englische Vokabel löschen
                $stmt = $pdo->prepare("DELETE FROM vocabenglish WHERE evocabid = ?");
                $stmt->execute([$evocabid]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Vokabel erfolgreich gelöscht']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen der Vokabel']);
            }
            exit();
            
        case 'remove_student':
            $studentid = $_POST['studentid'];
            
            try {
                $stmt = $pdo->prepare("UPDATE student SET groupid = NULL WHERE studentid = ?");
                $stmt->execute([$studentid]);
                echo json_encode(['success' => true, 'message' => 'Schüler erfolgreich aus der Gruppe entfernt']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Entfernen des Schülers']);
            }
            exit();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Lehrer Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome für Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        }
        
        .nav-link {
            font-weight: 600;
            text-align: center;
        }
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        
        .content {
            flex: 1;
            padding: 2rem 0;
        }
        
        .dashboard-header {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #17a2b8;
        }
        
        .group-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .group-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .group-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .modal-header {
            background-color: #17a2b8;
            color: white;
        }
        
        .tab-content {
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        
        .student-item, .unit-item {
            background-color: white;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .vocab-table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index2.php">SprachApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index2.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="teacherdashboard.php">Lehrer-Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schueler_verwalten.php">Schüler verwalten</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="user-info">
                        <?php echo htmlspecialchars($teachername); ?>
                        <span class="role-badge"><?php echo htmlspecialchars($role); ?></span>
                    </span>
                    <a href="logout.php" class="btn logout-btn">Abmelden</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container content">
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Lehrer-Dashboard</h2>
                    <p class="mb-0">Verwalten Sie Ihre Gruppen, Units und Schüler</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                    <i class="fas fa-plus"></i> Neue Gruppe erstellen
                </button>
            </div>
        </div>
        
        <div id="groupsContainer">
            <!-- Gruppen werden hier dynamisch geladen -->
        </div>
    </div>
    
    <!-- Modal für neue Gruppe -->
    <div class="modal fade" id="createGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Neue Gruppe erstellen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createGroupForm">
                        <div class="mb-3">
                            <label for="groupname" class="form-label">Gruppenname</label>
                            <input type="text" class="form-control" id="groupname" required>
                            <small class="form-text text-muted">Der vollständige Gruppenname wird: <?php echo htmlspecialchars($teachername); ?>_[Ihr Gruppenname]</small>
                        </div>
                        <div class="mb-3">
                            <label for="grouppassword" class="form-label">Gruppenpasswort</label>
                            <input type="password" class="form-control" id="grouppassword" required>
                            <small class="form-text text-muted">Mit diesem Passwort können Schüler der Gruppe beitreten</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" onclick="createGroup()">Erstellen</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal für Gruppendetails -->
    <div class="modal fade" id="groupDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="groupDetailsTitle">Gruppendetails</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="groupTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button">
                                <i class="fas fa-users"></i> Schüler
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="units-tab" data-bs-toggle="tab" data-bs-target="#units" type="button">
                                <i class="fas fa-book"></i> Units
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="vocab-tab" data-bs-toggle="tab" data-bs-target="#vocab" type="button">
                                <i class="fas fa-language"></i> Vokabeln
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="groupTabsContent">
                        <div class="tab-pane fade show active" id="students" role="tabpanel">
                            <div id="studentsContainer">
                                <!-- Schüler werden hier dynamisch geladen -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="units" role="tabpanel">
                            <div class="mb-3">
                                <button class="btn btn-success btn-sm" onclick="showCreateUnitForm()">
                                    <i class="fas fa-plus"></i> Neue Unit
                                </button>
                            </div>
                            <div id="unitsContainer">
                                <!-- Units werden hier dynamisch geladen -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="vocab" role="tabpanel">
                            <div class="mb-3">
                                <select class="form-select" id="vocabUnitSelect" onchange="loadVocab()">
                                    <option value="">Wählen Sie eine Unit...</option>
                                </select>
                            </div>
                            <div id="vocabContainer">
                                <!-- Vokabeln werden hier dynamisch geladen -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p class="mb-0">&copy; 2025 SprachApp. Alle Rechte vorbehalten.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentGroupId = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadGroups();
        });
        
        function loadGroups() {
            fetch('teacherdashboard.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_groups'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayGroups(data.groups);
                }
            });
        }
        
        function displayGroups(groups) {
            const container = document.getElementById('groupsContainer');
            container.innerHTML = '';
            
            if (groups.length === 0) {
                container.innerHTML = '<div class="alert alert-info">Sie haben noch keine Gruppen erstellt.</div>';
                return;
            }
            
            groups.forEach(group => {
                const groupCard = document.createElement('div');
                groupCard.className = 'group-card';
                groupCard.onclick = () => showGroupDetails(group.groupid);
                
                groupCard.innerHTML = `
                    <h4><?php echo htmlspecialchars($teachername); ?>_${group.groupname}</h4>
                    <div class="group-stats">
                        <span><i class="fas fa-users"></i> ${group.student_count} Schüler</span>
                        <span><i class="fas fa-book"></i> ${group.unit_count} Units</span>
                        <span><i class="fas fa-clock"></i> Erstellt: ${new Date(group.created_at).toLocaleDateString('de-DE')}</span>
                    </div>
                `;
                
                container.appendChild(groupCard);
            });
        }
        
        function createGroup() {
            const groupname = document.getElementById('groupname').value;
            const grouppassword = document.getElementById('grouppassword').value;
            
            fetch('teacherdashboard.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=create_group&groupname=${encodeURIComponent(groupname)}&grouppassword=${encodeURIComponent(grouppassword)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createGroupModal')).hide();
                    document.getElementById('createGroupForm').reset();
                    loadGroups();
                } else {
                    alert(data.message);
                }
            });
        }
        
        function showGroupDetails(groupId) {
            currentGroupId = groupId;
            
            fetch('teacherdashboard.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_group_details&groupid=${groupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('groupDetailsTitle').textContent = 
                        `<?php echo htmlspecialchars($teachername); ?>_${data.group.groupname}`;
                    
                    displayStudents(data.students);
                    displayUnits(data.units);
                    
                    // Units für Vokabel-Dropdown
                    const vocabSelect = document.getElementById('vocabUnitSelect');
                    vocabSelect.innerHTML = '<option value="">Wählen Sie eine Unit...</option>';
                    data.units.forEach(unit => {
                        vocabSelect.innerHTML += `<option value="${unit.unitid}">${unit.unitname}</option>`;
                    });
                    
                    const modal = new bootstrap.Modal(document.getElementById('groupDetailsModal'));
                    modal.show();
                }
            });
        }
        
        function displayStudents(students) {
            const container = document.getElementById('studentsContainer');
            container.innerHTML = '';
            
            if (students.length === 0) {
                container.innerHTML = '<p class="text-muted">Noch keine Schüler in dieser Gruppe.</p>';
                return;
            }
            
            students.forEach(student => {
                const studentItem = document.createElement('div');
                studentItem.className = 'student-item';
                studentItem.innerHTML = `
                    <div>
                        <strong>${student.studentname}</strong>
                        <br>
                        <small class="text-muted">${student.email}</small>
                    </div>
                    <button class="btn btn-danger btn-sm btn-action" onclick="removeStudent(${student.studentid})">
                        <i class="fas fa-trash"></i> Entfernen
                    </button>
                `;
                container.appendChild(studentItem);
            });
        }
        
        function displayUnits(units) {
            const container = document.getElementById('unitsContainer');
            container.innerHTML = '';
            
            if (units.length === 0) {
                container.innerHTML = '<p class="text-muted">Noch keine Units in dieser Gruppe.</p>';
                return;
            }
            
            units.forEach(unit => {
                const unitItem = document.createElement('div');
                unitItem.className = 'unit-item';
                unitItem.innerHTML = `
                    <div>
                        <strong>${unit.unitname}</strong>
                        <br>
                        <small class="text-muted">Erstellt: ${new Date(unit.created_at).toLocaleDateString('de-DE')}</small>
                    </div>
                    <button class="btn btn-danger btn-sm btn-action" onclick="deleteUnit(${unit.unitid})">
                        <i class="fas fa-trash"></i> Löschen
                    </button>
                `;
                container.appendChild(unitItem);
            });
        }
        
        function showCreateUnitForm() {
            const unitName = prompt('Name der neuen Unit:');
            if (unitName) {
                createUnit(unitName);
            }
        }
        
        function createUnit(unitName) {
            fetch('teacherdashboard.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=create_unit&groupid=${currentGroupId}&unitname=${encodeURIComponent(unitName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showGroupDetails(currentGroupId);
                } else {
                    alert(data.message);
                }
            });
        }
        
        function deleteUnit(unitId) {
            if (confirm('Möchten Sie diese Unit wirklich löschen? Alle Vokabeln werden ebenfalls gelöscht.')) {
                fetch('teacherdashboard.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_unit&unitid=${unitId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showGroupDetails(currentGroupId);
                    } else {
                        alert(data.message);
                    }
                });
            }
        }
        
        function loadVocab() {
            const unitId = document.getElementById('vocabUnitSelect').value;
            if (!unitId) {
                document.getElementById('vocabContainer').innerHTML = '';
                return;
            }
            
            fetch('teacherdashboard.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_vocab&unitid=${unitId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayVocab(data.vocab, unitId);
                }
            });
        }
        
        function displayVocab(vocab, unitId) {
            const container = document.getElementById('vocabContainer');
            
            let html = `
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" onclick="showAddVocabForm(${unitId})">
                        <i class="fas fa-plus"></i> Vokabel hinzufügen
                    </button>
                </div>
                <div class="vocab-table">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Deutsch</th>
                               <th>Englisch</th>
                               <th>Aktionen</th>
                           </tr>
                       </thead>
                       <tbody>
           `;
           
           if (vocab.length === 0) {
               html += '<tr><td colspan="3" class="text-center text-muted">Keine Vokabeln vorhanden</td></tr>';
           } else {
               vocab.forEach(v => {
                   html += `
                       <tr>
                           <td>${v.german_word}</td>
                           <td>${v.english_word}</td>
                           <td>
                               <button class="btn btn-danger btn-sm btn-action" onclick="deleteVocab(${v.gvocabid}, ${v.evocabid})">
                                   <i class="fas fa-trash"></i>
                               </button>
                           </td>
                       </tr>
                   `;
               });
           }
           
           html += `
                       </tbody>
                   </table>
               </div>
           `;
           
           container.innerHTML = html;
       }
       
       function showAddVocabForm(unitId) {
           const html = `
               <div class="modal fade" id="addVocabModal" tabindex="-1">
                   <div class="modal-dialog">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title">Vokabel hinzufügen</h5>
                               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                           </div>
                           <div class="modal-body">
                               <form id="addVocabForm">
                                   <div class="mb-3">
                                       <label for="germanWord" class="form-label">Deutsches Wort</label>
                                       <input type="text" class="form-control" id="germanWord" required>
                                   </div>
                                   <div class="mb-3">
                                       <label for="englishWord" class="form-label">Englisches Wort</label>
                                       <input type="text" class="form-control" id="englishWord" required>
                                   </div>
                               </form>
                           </div>
                           <div class="modal-footer">
                               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                               <button type="button" class="btn btn-primary" onclick="addVocab(${unitId})">Hinzufügen</button>
                           </div>
                       </div>
                   </div>
               </div>
           `;
           
           // Modal zum Body hinzufügen und anzeigen
           const modalContainer = document.createElement('div');
           modalContainer.innerHTML = html;
           document.body.appendChild(modalContainer);
           
           const modal = new bootstrap.Modal(document.getElementById('addVocabModal'));
           modal.show();
           
           // Modal nach dem Schließen entfernen
           document.getElementById('addVocabModal').addEventListener('hidden.bs.modal', function() {
               modalContainer.remove();
           });
       }
       
       function addVocab(unitId) {
           const german = document.getElementById('germanWord').value;
           const english = document.getElementById('englishWord').value;
           
           fetch('teacherdashboard.php', {
               method: 'POST',
               headers: {'Content-Type': 'application/x-www-form-urlencoded'},
               body: `action=add_vocab&unitid=${unitId}&german=${encodeURIComponent(german)}&english=${encodeURIComponent(english)}`
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   bootstrap.Modal.getInstance(document.getElementById('addVocabModal')).hide();
                   loadVocab();
               } else {
                   alert(data.message);
               }
           });
       }
       
       function deleteVocab(gvocabid, evocabid) {
           if (confirm('Möchten Sie diese Vokabel wirklich löschen?')) {
               fetch('teacherdashboard.php', {
                   method: 'POST',
                   headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                   body: `action=delete_vocab&gvocabid=${gvocabid}&evocabid=${evocabid}`
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       loadVocab();
                   } else {
                       alert(data.message);
                   }
               });
           }
       }
       
       function removeStudent(studentId) {
           if (confirm('Möchten Sie diesen Schüler wirklich aus der Gruppe entfernen?')) {
               fetch('teacherdashboard.php', {
                   method: 'POST',
                   headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                   body: `action=remove_student&studentid=${studentId}`
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       showGroupDetails(currentGroupId);
                   } else {
                       alert(data.message);
                   }
               });
           }
       }
   </script>
</body>
</html>