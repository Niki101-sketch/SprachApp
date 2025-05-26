<?php
// Aktiviere Fehlerberichterstattung für Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manuelle Datenbankverbindung herstellen
$servername = "sql108.infinityfree.com";
$dbusername = "if0_38905283";
$dbpassword = "ewgjt0aaksuC";
$dbname = "if0_38905283_sprachapp";

// Verbindung erstellen
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Verbindung überprüfen
if ($conn->connect_error) {
    die("Datenbankverbindung fehlgeschlagen: " . $conn->connect_error);
}

// Session starten
session_start();

// Aktuelle Benutzerinformationen
$current_username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$current_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Alle Benutzer aus den verschiedenen Tabellen abrufen
$admins = [];
$teachers = [];
$students = [];
$all_users = [];
$error_message = "";

try {
    // Admins abrufen
    $admin_sql = "SELECT * FROM admin";
    $admin_result = $conn->query($admin_sql);
    
    if ($admin_result && $admin_result->num_rows > 0) {
        while($row = $admin_result->fetch_assoc()) {
            $row['role'] = 'admin'; // Rolle hinzufügen
            $admins[] = $row;
            $all_users[] = $row;
        }
    }
    
    // Lehrer abrufen
    $teacher_sql = "SELECT * FROM teacher";
    $teacher_result = $conn->query($teacher_sql);
    
    if ($teacher_result && $teacher_result->num_rows > 0) {
        while($row = $teacher_result->fetch_assoc()) {
            $row['role'] = 'lehrer'; // Rolle hinzufügen
            $teachers[] = $row;
            $all_users[] = $row;
        }
    }
    
    // Schüler abrufen
    $student_sql = "SELECT * FROM student";
    $student_result = $conn->query($student_sql);
    
    if ($student_result && $student_result->num_rows > 0) {
        while($row = $student_result->fetch_assoc()) {
            $row['role'] = 'schueler'; // Rolle hinzufügen
            $students[] = $row;
            $all_users[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Fehler beim Abrufen der Benutzer: " . $e->getMessage();
}

// Bestimme die korrekten ID-Feldnamen für jede Tabelle
$id_fields = [
    'admin' => 'adminid',
    'lehrer' => 'teacherid',
    'schueler' => 'studentid'
];

// Bestimme die korrekten Username-Feldnamen für jede Tabelle
$username_fields = [
    'admin' => 'adminname',
    'lehrer' => 'teachername',
    'schueler' => 'studentname'
];

// Wenn ein Benutzer gelöscht werden soll
if (isset($_POST['delete'])) {
    $user_id = $_POST['userid'];
    $user_role = $_POST['userrole'];
    $table_name = '';
    $id_field = '';
    
    // Tabelle und ID-Feld basierend auf der Rolle bestimmen
    if ($user_role == 'admin') {
        $table_name = 'admin';
        $id_field = 'adminid';
    } else if ($user_role == 'lehrer') {
        $table_name = 'teacher';
        $id_field = 'teacherid';
    } else if ($user_role == 'schueler') {
        $table_name = 'student';
        $id_field = 'studentid';
    }
    
    if (!empty($table_name) && !empty($id_field)) {
        $delete_sql = "DELETE FROM $table_name WHERE $id_field = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Benutzer erfolgreich gelöscht.";
            header("Location: benutzer_verwalten.php");
            exit();
        } else {
            $error_message = "Fehler beim Löschen des Benutzers: " . $conn->error;
        }
        $delete_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Benutzerverwaltung</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .welcome-box {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #dc3545;
        }
        
        .welcome-box h2 {
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .admin-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 2rem;
        }
        
        .admin-header {
            background-color: #dc3545;
            color: white;
            padding: 1rem 1.5rem;
        }
        
        .admin-header h4 {
            margin: 0;
            font-weight: bold;
        }
        
        .admin-body {
            padding: 1.5rem;
        }
        
        .btn {
            border-radius: 4px;
            font-weight: bold;
            padding: 0.5rem 1.5rem;
            text-align: center;
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
        
        .user-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .user-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: left;
            padding: 0.75rem;
            border-bottom: 2px solid #dee2e6;
        }
        
        .user-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .user-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .user-table .role-pill {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.75rem;
            text-align: center;
        }
        
        .role-admin {
            background-color: #dc3545;
            color: white;
        }
        
        .role-lehrer {
            background-color: #0dcaf0;
            color: white;
        }
        
        .role-schueler {
            background-color: #6c757d;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
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
            
            .admin-card {
                margin-bottom: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
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
                        <a class="nav-link active" href="admin_panel.php">Admin-Panel</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center flex-wrap">
                    <span class="user-info">
                        <?php echo htmlspecialchars($current_username); ?>
                        <span class="role-badge"><?php echo htmlspecialchars($current_role); ?></span>
                    </span>
                    <a href="logout.php" class="btn logout-btn">Abmelden</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container content">
        <div class="welcome-box">
            <h2>Benutzerverwaltung</h2>
            <p>Hier können Sie alle Benutzerkonten der SprachApp verwalten.</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Benutzerübersicht -->
        <div class="admin-card">
            <div class="admin-header">
                <h4>Administratoren</h4>
            </div>
            <div class="admin-body">                
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Benutzername</th>
                                <th>Rolle</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Keine Administratoren gefunden.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($admin['adminid']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['adminname']); ?></td>
                                        <td>
                                            <span class="role-pill role-admin">
                                                Admin
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="post" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Administrator löschen möchten?');">
                                                    <input type="hidden" name="userid" value="<?php echo $admin['adminid']; ?>">
                                                    <input type="hidden" name="userrole" value="admin">
                                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Löschen</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-header">
                <h4>Lehrer</h4>
            </div>
            <div class="admin-body">                
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Benutzername</th>
                                <th>Rolle</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teachers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Keine Lehrer gefunden.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teachers as $teacher): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($teacher['teacherid']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['teachername']); ?></td>
                                        <td>
                                            <span class="role-pill role-lehrer">
                                                Lehrer
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="post" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Lehrer löschen möchten?');">
                                                    <input type="hidden" name="userid" value="<?php echo $teacher['teacherid']; ?>">
                                                    <input type="hidden" name="userrole" value="lehrer">
                                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Löschen</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-header">
                <h4>Schüler</h4>
            </div>
            <div class="admin-body">                
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Benutzername</th>
                                <th>Rolle</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Keine Schüler gefunden.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['studentid']); ?></td>
                                        <td><?php echo htmlspecialchars($student['studentname']); ?></td>
                                        <td>
                                            <span class="role-pill role-schueler">
                                                Schüler
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="post" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Schüler löschen möchten?');">
                                                    <input type="hidden" name="userid" value="<?php echo $student['studentid']; ?>">
                                                    <input type="hidden" name="userrole" value="schueler">
                                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Löschen</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
            var role = "<?php echo $current_role; ?>";
            
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

<?php
// Datenbank-Verbindung schließen
$conn->close();
?>