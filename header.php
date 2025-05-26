<!-- header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['username'] ?? 'Gast';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachApp - Übungsbereich</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- optional -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
        }
        .hero-section {
            background-color: #e9ecef;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .feature-box {
            padding: 1.5rem;
            border-radius: 0.5rem;
            transition: transform 0.3s;
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .auth-buttons .btn {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index2.php">SprachApp</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="index2.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="einheiten.php">Einheiten</a></li>
                <li class="nav-item"><a class="nav-link" href="miniTest.php">Grammatiktrainer</a></li>
                <li class="nav-item"><a class="nav-link" href="zuordnen.php">MultiChoice</a></li>
                <li class="nav-item teacher-section"><a class="nav-link" href="schueler_verwalten.php">Schüler verwalten</a></li>
                <li class="nav-item admin-section"><a class="nav-link" href="admin_panel.php">Admin-Panel</a></li>
            </ul>
            <div class="d-flex align-items-center flex-wrap">
                <span class="user-info">
                    <?= htmlspecialchars($username) ?>
                    <span class="role-badge"><?= htmlspecialchars($role) ?></span>
                </span>
                <a href="logout.php" class="btn logout-btn">Abmelden</a>
            </div>
        </div>
    </div>
</nav>
