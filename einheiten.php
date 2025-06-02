<?php
// einheiten.php - Einheiten-Übersicht
require_once 'config.php';

// Benutzerinformationen abrufen
$userInfo = getUserInfo();

// PDO Datenbankverbindung
$pdo = getPDOConnection();

// Units aus der Datenbank laden
$stmt = $pdo->prepare("
    SELECT 
        u.unitid,
        u.unitname,
        COUNT(vg.gvocabid) as vocab_count
    FROM unit u
    LEFT JOIN vocabgerman vg ON u.unitid = vg.unitid
    GROUP BY u.unitid, u.unitname
    ORDER BY u.unitid
");
$stmt->execute();
$units = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "SprachApp - Einheiten";

// Header einbinden
include 'header.php';
?>

<div class="container content">
    <div class="welcome-box">
        <h2>Verfügbare Units</h2>
        <p>Wählen Sie eine Unit aus, um mit dem Lernen zu beginnen.</p>
    </div>
    
    <?php if (empty($units)): ?>
        <div class="alert alert-info" role="alert">
            Zurzeit sind keine Units verfügbar.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($units as $unit): ?>
                <div class="col-md-4">
                    <div class="unit-card">
                        <div class="unit-header">
                            <h5><?php echo htmlspecialchars($unit['unitname']); ?></h5>
                        </div>
                        <div class="unit-body">
                            <p class="mb-4">
                                <?php echo $unit['vocab_count']; ?> Vokabel<?php echo $unit['vocab_count'] != 1 ? 'n' : ''; ?>
                            </p>
                            <div class="d-grid">
                                <a href="karteikarten.php?unit=<?php echo $unit['unitid']; ?>" class="btn btn-primary w-100">
                                    Lernen starten
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>