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
if ($_SESSION['role'] !== 'teacher' || $_SESSION['role'] !== 'admin') {
    header("Location: index2.php");
    exit();
}
*/

// Benutzerinformationen aus der Session holen
$teachername = $_SESSION['username'];
$teacherid = $_SESSION['userid'];
$role = $_SESSION['role'];

// AJAX-Handler für verschiedene Aktionen
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_all_students':
            try {
                // Alle Schüler mit ihrer Gruppenzugehörigkeit laden
                $stmt = $pdo->prepare("
                    SELECT s.*, g.groupname, g.groupid, t.teachername
                    FROM student s
                    LEFT JOIN `group` g ON s.groupid = g.groupid
                    LEFT JOIN teacher t ON g.teacherid = t.teacherid
                    ORDER BY s.studentname
                ");
                $stmt->execute();
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Gruppen des Lehrers laden
                $stmt = $pdo->prepare("SELECT * FROM `group` WHERE teacherid = ?");
                $stmt->execute([$teacherid]);
                $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'students' => $students, 'groups' => $groups]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Laden der Daten']);
            }
            exit();
            
        case 'get_group_students':
            $groupid = $_POST['groupid'];
            try {
                // Schüler einer bestimmten Gruppe laden
                $stmt = $pdo->prepare("
                    SELECT s.*, 
                           COUNT(DISTINCT vr.gvocabid) as correct_vocab,
                           COUNT(DISTINCT vw.gvocabid) as wrong_vocab,
                           MAX(GREATEST(COALESCE(vr.last_answered, '2000-01-01'), COALESCE(vw.last_answered, '2000-01-01'))) as last_activity
                    FROM student s
                    LEFT JOIN vocabright vr ON s.studentid = vr.studentid
                    LEFT JOIN vocabwrong vw ON s.studentid = vw.studentid
                    WHERE s.groupid = ?
                    GROUP BY s.studentid
                    ORDER BY s.studentname
                ");
                $stmt->execute([$groupid]);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'students' => $students]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Laden der Schüler']);
            }
            exit();
            
        case 'get_student_details':
            $studentid = $_POST['studentid'];
            try {
                // Schülerdetails laden
                $stmt = $pdo->prepare("
                    SELECT s.*, g.groupname, g.groupid
                    FROM student s
                    LEFT JOIN `group` g ON s.groupid = g.groupid
                    WHERE s.studentid = ?
                ");
                $stmt->execute([$studentid]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Lernstatistiken laden
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT vr.gvocabid) as total_correct,
                        COUNT(DISTINCT vw.gvocabid) as total_wrong,
                        SUM(vr.correct_answers) as sum_correct,
                        SUM(vw.wrong_answers) as sum_wrong
                    FROM student s
                    LEFT JOIN vocabright vr ON s.studentid = vr.studentid
                    LEFT JOIN vocabwrong vw ON s.studentid = vw.studentid
                    WHERE s.studentid = ?
                ");
                $stmt->execute([$studentid]);
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Fortschritt pro Unit laden
                $stmt = $pdo->prepare("
                    SELECT u.unitid, u.unitname,
                           COUNT(DISTINCT vm.gvocabid) as total_vocab,
                           COUNT(DISTINCT vr.gvocabid) as learned_vocab
                    FROM unit u
                    JOIN `group` g ON u.groupid = g.groupid
                    JOIN student s ON g.groupid = s.groupid
                    LEFT JOIN vocabgerman vg ON u.unitid = vg.unitid
                    LEFT JOIN vocabmapping vm ON vg.gvocabid = vm.gvocabid
                    LEFT JOIN vocabright vr ON vm.gvocabid = vr.gvocabid AND vr.studentid = s.studentid
                    WHERE s.studentid = ?
                    GROUP BY u.unitid
                ");
                $stmt->execute([$studentid]);
                $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true, 
                    'student' => $student, 
                    'stats' => $stats,
                    'progress' => $progress
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Laden der Schülerdetails']);
            }
            exit();
            
        case 'assign_to_group':
            $studentid = $_POST['studentid'];
            $groupid = $_POST['groupid'];
            
            try {
                // Überprüfen ob die Gruppe dem Lehrer gehört
                $stmt = $pdo->prepare("SELECT * FROM `group` WHERE groupid = ? AND teacherid = ?");
                $stmt->execute([$groupid, $teacherid]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Sie haben keine Berechtigung für diese Gruppe']);
                    exit();
                }
                
                $stmt = $pdo->prepare("UPDATE student SET groupid = ? WHERE studentid = ?");
                $stmt->execute([$groupid, $studentid]);
                echo json_encode(['success' => true, 'message' => 'Schüler erfolgreich zur Gruppe hinzugefügt']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Zuweisen zur Gruppe']);
            }
            exit();
            
        case 'remove_from_group':
            $studentid = $_POST['studentid'];
            
            try {
                // Überprüfen ob der Schüler in einer Gruppe des Lehrers ist
                $stmt = $pdo->prepare("
                    SELECT s.* FROM student s 
                    JOIN `group` g ON s.groupid = g.groupid 
                    WHERE s.studentid = ? AND g.teacherid = ?
                ");
                $stmt->execute([$studentid, $teacherid]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Sie haben keine Berechtigung für diesen Schüler']);
                    exit();
                }
                
                $stmt = $pdo->prepare("UPDATE student SET groupid = NULL WHERE studentid = ?");
                $stmt->execute([$studentid]);
                echo json_encode(['success' => true, 'message' => 'Schüler erfolgreich aus der Gruppe entfernt']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Fehler beim Entfernen aus der Gruppe']);
            }
            exit();
            
        case 'reset_progress':
            $studentid = $_POST['studentid'];
            
            try {
                $pdo->beginTransaction();
                
                // Fortschritt zurücksetzen
                $stmt = $pdo->prepare("DELETE FROM vocabright WHERE studentid = ?");
                $stmt->execute([$studentid]);
                
                $stmt = $pdo->prepare("DELETE FROM vocabwrong WHERE studentid = ?");
                $stmt->execute([$studentid]);
                
                $stmt = $pdo->prepare("DELETE FROM favourite WHERE studentid = ?");
                $stmt->execute([$studentid]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Fortschritt erfolgreich zurückgesetzt']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Fehler beim Zurücksetzen des Fortschritts']);
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
    <title>SprachApp - Schülerverwaltung</title>
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
        
        .page-header {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #17a2b8;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .student-card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background-color: #17a2b8;
            color: white;
        }
        
        .stats-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .stats-card h3 {
            margin: 0;
            color: #17a2b8;
        }
        
        .progress-item {
            background-color: white;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid #e9ecef;
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
        
        .group-badge {
            background-color: #17a2b8;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .no-group-badge {
            background-color: #dc3545;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .activity-indicator {
            font-size: 0.875rem;
            color: #6c757d;
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
                        <a class="nav-link" href="teacherdashboard.php">Lehrer-Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="studentmanager.php">Schülerverwaltung</a>
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
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Schülerverwaltung</h2>
                    <p class="mb-0">Verwalten Sie Ihre Schüler und sehen Sie deren Lernfortschritt ein</p>
                </div>
                <div>
                    <span class="badge bg-info" id="totalStudentsCount">0 Schüler</span>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4">
                    <label for="groupFilter" class="form-label">Nach Gruppe filtern</label>
                    <select class="form-select" id="groupFilter" onchange="filterStudents()">
                        <option value="all">Alle Schüler</option>
                        <option value="my">Nur meine Schüler</option>
                        <option value="unassigned">Ohne Gruppe</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="groupSelect" class="form-label">Bestimmte Gruppe</label>
                    <select class="form-select" id="groupSelect" onchange="loadGroupStudents()">
                        <option value="">Gruppe wählen...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchInput" class="form-label">Schüler suchen</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Name oder E-Mail..." onkeyup="searchStudents()">
                </div>
            </div>
        </div>
        
        <div id="studentsContainer">
            <!-- Schüler werden hier dynamisch geladen -->
        </div>
    </div>
    
    <!-- Modal für Schülerdetails -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailsTitle">Schülerdetails</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="studentDetailsContent">
                        <!-- Details werden hier dynamisch geladen -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
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
        let allStudents = [];
        let myGroups = [];
        
        document.addEventListener('DOMContentLoaded', function() {
            loadAllStudents();
        });
        
        function loadAllStudents() {
            fetch('studentmanager.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_all_students'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allStudents = data.students;
                    myGroups = data.groups;
                    
                    // Gruppen-Dropdown befüllen
                    const groupSelect = document.getElementById('groupSelect');
                    groupSelect.innerHTML = '<option value="">Gruppe wählen...</option>';
                    myGroups.forEach(group => {
                        groupSelect.innerHTML += `<option value="${group.groupid}"><?php echo htmlspecialchars($teachername); ?>_${group.groupname}</option>`;
                    });
                    
                    filterStudents();
                }
            });
        }
        
        function filterStudents() {
            const filter = document.getElementById('groupFilter').value;
            let filteredStudents = allStudents;
            
            if (filter === 'my') {
                filteredStudents = allStudents.filter(s => 
                    myGroups.some(g => g.groupid == s.groupid)
                );
            } else if (filter === 'unassigned') {
                filteredStudents = allStudents.filter(s => !s.groupid);
            }
            
            displayStudents(filteredStudents);
        }
        
        function loadGroupStudents() {
            const groupId = document.getElementById('groupSelect').value;
            if (!groupId) {
                filterStudents();
                return;
            }
            
            fetch('studentmanager.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_group_students&groupid=${groupId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudents(data.students);
                }
            });
        }
        
        function searchStudents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const filteredStudents = allStudents.filter(s => 
                s.studentname.toLowerCase().includes(searchTerm) || 
                s.email.toLowerCase().includes(searchTerm)
            );
            displayStudents(filteredStudents);
        }
        
        function displayStudents(students) {
            const container = document.getElementById('studentsContainer');
            container.innerHTML = '';
            
            document.getElementById('totalStudentsCount').textContent = `${students.length} Schüler`;
            
            if (students.length === 0) {
                container.innerHTML = '<div class="alert alert-info">Keine Schüler gefunden.</div>';
                return;
            }
            
            students.forEach(student => {
                const studentCard = document.createElement('div');
                studentCard.className = 'student-card';
                studentCard.onclick = () => showStudentDetails(student.studentid);
                
                const groupInfo = student.groupid ? 
                    `<span class="group-badge">${student.teachername}_${student.groupname}</span>` : 
                    '<span class="no-group-badge">Keine Gruppe</span>';
                
                const lastActivity = student.last_activity && student.last_activity !== '2000-01-01' ? 
                    `Letzte Aktivität: ${new Date(student.last_activity).toLocaleDateString('de-DE')}` : 
                    'Noch keine Aktivität';
                
                studentCard.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">${student.studentname}</h5>
                            <p class="text-muted mb-2">${student.email}</p>
                            ${groupInfo}
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="activity-indicator mb-2">
                                <i class="fas fa-clock"></i> ${lastActivity}
                            </div>
                            <div>
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-check"></i> ${student.correct_vocab || 0} richtig
                                </span>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times"></i> ${student.wrong_vocab || 0} falsch
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                
                container.appendChild(studentCard);
            });
        }
        
        function showStudentDetails(studentId) {
            fetch('studentmanager.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_student_details&studentid=${studentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudentDetails(data.student, data.stats, data.progress);
                    const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
                    modal.show();
                }
            });
        }
        
        function displayStudentDetails(student, stats, progress) {
            document.getElementById('studentDetailsTitle').textContent = student.studentname;
            
            const successRate = stats.sum_correct && stats.sum_wrong ? 
                Math.round((stats.sum_correct / (stats.sum_correct + stats.sum_wrong)) * 100) : 0;
            
            let progressHtml = '';
            if (progress.length > 0) {
                progress.forEach(unit => {
                    const unitProgress = unit.total_vocab > 0 ? 
                        Math.round((unit.learned_vocab / unit.total_vocab) * 100) : 0;
                    
                    progressHtml += `
                        <div class="progress-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>${unit.unitname}</strong>
                                <span>${unit.learned_vocab} / ${unit.total_vocab} Vokabeln</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: ${unitProgress}%">${unitProgress}%</div>
                            </div>
                        </div>
                    `;
                });
            } else {
                progressHtml = '<p class="text-muted">Noch kein Lernfortschritt vorhanden.</p>';
            }
            
            const content = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Kontaktinformationen</h6>
                        <p><strong>E-Mail:</strong> ${student.email}<br>
                        <strong>Gruppe:</strong> ${student.groupid ? student.groupname : 'Keine Gruppe'}<br>
                        <strong>Beigetreten:</strong> ${new Date(student.joined_at).toLocaleDateString('de-DE')}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Aktionen</h6>
                        ${student.groupid ? 
                            `<button class="btn btn-warning btn-sm me-2" onclick="removeFromGroup(${student.studentid})">
                                <i class="fas fa-user-minus"></i> Aus Gruppe entfernen
                            </button>` : 
                            `<button class="btn btn-success btn-sm me-2" onclick="showAssignToGroup(${student.studentid})">
                                <i class="fas fa-user-plus"></i> Zu Gruppe hinzufügen
                            </button>`
                        }
                        <button class="btn btn-danger btn-sm" onclick="confirmResetProgress(${student.studentid})">
                            <i class="fas fa-redo"></i> Fortschritt zurücksetzen
                        </button>
                    </div>
                </div>
                
                <h6>Lernstatistiken</h6>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3>${stats.sum_correct || 0}</h3>
                            <small>Richtige Antworten</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3>${stats.sum_wrong || 0}</h3>
                            <small>Falsche Antworten</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3>${successRate}%</h3>
                            <small>Erfolgsquote</small>
                        </div>
                    </div>
                </div>
                
                <h6>Fortschritt pro Unit</h6>
                ${progressHtml}
            `;
            
            document.getElementById('studentDetailsContent').innerHTML = content;
        }
        
        function showAssignToGroup(studentId) {
            let options = '<option value="">Gruppe wählen...</option>';
            myGroups.forEach(group => {
                options += `<option value="${group.groupid}"><?php echo htmlspecialchars($teachername); ?>_${group.groupname}</option>`;
            });
            
            const html = `
                <div class="modal fade" id="assignGroupModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: white;">
                                <h5 class="modal-title">Schüler zu Gruppe hinzufügen</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="assignGroupSelect" class="form-label">Wählen Sie eine Gruppe</label>
                                    <select class="form-select" id="assignGroupSelect">
                                        ${options}
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                                <button type="button" class="btn btn-success" onclick="assignToGroup(${studentId})">
                                    <i class="fas fa-user-plus"></i> Hinzufügen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = html;
            document.body.appendChild(modalContainer);
            
            const modal = new bootstrap.Modal(document.getElementById('assignGroupModal'));
           modal.show();
           
           document.getElementById('assignGroupModal').addEventListener('hidden.bs.modal', function() {
               modalContainer.remove();
           });
       }
       
       function assignToGroup(studentId) {
           const groupId = document.getElementById('assignGroupSelect').value;
           if (!groupId) {
               alert('Bitte wählen Sie eine Gruppe aus.');
               return;
           }
           
           fetch('studentmanager.php', {
               method: 'POST',
               headers: {'Content-Type': 'application/x-www-form-urlencoded'},
               body: `action=assign_to_group&studentid=${studentId}&groupid=${groupId}`
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   bootstrap.Modal.getInstance(document.getElementById('assignGroupModal')).hide();
                   bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal')).hide();
                   loadAllStudents();
                   showSuccessMessage(data.message);
               } else {
                   alert(data.message);
               }
           });
       }
       
       function removeFromGroup(studentId) {
           if (confirm('Möchten Sie diesen Schüler wirklich aus der Gruppe entfernen?')) {
               fetch('studentmanager.php', {
                   method: 'POST',
                   headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                   body: `action=remove_from_group&studentid=${studentId}`
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal')).hide();
                       loadAllStudents();
                       showSuccessMessage(data.message);
                   } else {
                       alert(data.message);
                   }
               });
           }
       }
       
       function confirmResetProgress(studentId) {
           const html = `
               <div class="modal fade" id="resetProgressModal" tabindex="-1">
                   <div class="modal-dialog">
                       <div class="modal-content">
                           <div class="modal-header bg-danger text-white">
                               <h5 class="modal-title">Fortschritt zurücksetzen</h5>
                               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                           </div>
                           <div class="modal-body">
                               <div class="alert alert-warning">
                                   <i class="fas fa-exclamation-triangle"></i> <strong>Achtung!</strong>
                                   <p class="mb-0 mt-2">Diese Aktion kann nicht rückgängig gemacht werden. Alle Lernfortschritte, Statistiken und Favoriten des Schülers werden gelöscht.</p>
                               </div>
                               <p>Sind Sie sicher, dass Sie den gesamten Fortschritt dieses Schülers zurücksetzen möchten?</p>
                           </div>
                           <div class="modal-footer">
                               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                               <button type="button" class="btn btn-danger" onclick="resetProgress(${studentId})">
                                   <i class="fas fa-trash"></i> Fortschritt löschen
                               </button>
                           </div>
                       </div>
                   </div>
               </div>
           `;
           
           const modalContainer = document.createElement('div');
           modalContainer.innerHTML = html;
           document.body.appendChild(modalContainer);
           
           const modal = new bootstrap.Modal(document.getElementById('resetProgressModal'));
           modal.show();
           
           document.getElementById('resetProgressModal').addEventListener('hidden.bs.modal', function() {
               modalContainer.remove();
           });
       }
       
       function resetProgress(studentId) {
           fetch('studentmanager.php', {
               method: 'POST',
               headers: {'Content-Type': 'application/x-www-form-urlencoded'},
               body: `action=reset_progress&studentid=${studentId}`
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   bootstrap.Modal.getInstance(document.getElementById('resetProgressModal')).hide();
                   bootstrap.Modal.getInstance(document.getElementById('studentDetailsModal')).hide();
                   loadAllStudents();
                   showSuccessMessage(data.message);
               } else {
                   alert(data.message);
               }
           });
       }
       
       function showSuccessMessage(message) {
           const alert = document.createElement('div');
           alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
           alert.style.zIndex = '9999';
           alert.innerHTML = `
               <i class="fas fa-check-circle"></i> ${message}
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           `;
           document.body.appendChild(alert);
           
           setTimeout(() => {
               alert.remove();
           }, 3000);
       }
   </script>
</body>
</html>