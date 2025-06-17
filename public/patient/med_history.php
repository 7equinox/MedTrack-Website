<?php
session_start();

$pageTitle = 'Medication History - MedTrack';
$activePage = 'dashboard';
$base_path = '../';

require_once '../../templates/partials/header.php';

// ✅ Check login and get patient ID from session
if (!isset($_SESSION["PatientID"])) {
    header("Location: ../login.php");
    exit();
}
$patientID = $_SESSION["PatientID"];

require_once '../../config/database.php'; // Your DB connection

$takenMeds = [];

// ✅ Fetch only "Taken" medications for the logged-in patient
$medStmt = $conn->prepare("
    SELECT 
        m.*,
        ROW_NUMBER() OVER (PARTITION BY m.PrescriptionGUID ORDER BY m.IntakeTime ASC) as DoseNumber,
        (m.Frequency * (CASE WHEN m.DurationUnit = 'weeks' THEN m.Duration * 7 ELSE m.Duration END)) as TotalDoses
    FROM 
        medicationschedule m
    WHERE 
        m.PatientID = ? AND m.Status = 'Taken'
    ORDER BY 
        m.IntakeTime DESC
");
if (!$medStmt) {
    die("Prepare failed: " . $conn->error);
}
$medStmt->bind_param("s", $patientID);
$medStmt->execute();
$result = $medStmt->get_result();

while ($row = $result->fetch_assoc()) {
    $takenMeds[] = $row;
}

$medStmt->close();
?>

<body class="page-patient-area page-patient-med-history">
<?php require_once '../../templates/partials/patient_side_menu.php'; ?>
<?php require_once '../../templates/partials/patient_header.php'; ?>

<main>
  <div class="header-section">
    <h1>Medication History</h1>
    <a href="dashboard.php" class="back-button">Go Back to Dashboard</a>
  </div>

  <div class="history-list">
    <?php if (count($takenMeds) > 0): ?>
      <?php foreach ($takenMeds as $row): ?>
        <div class="card">
          <h2><?= htmlspecialchars($row['MedicationName']) ?></h2>
          <p><?= htmlspecialchars($row['Dosage']) ?> | For: <?= htmlspecialchars($row['MedicationFor']) ?></p>
          <p style="font-size: 0.9rem; color: #555;">
              Dose <?= htmlspecialchars($row['DoseNumber']) ?> of <?= htmlspecialchars($row['TotalDoses']) ?> &bull; 
              Taken on: <?= date("M d, Y, h:i A", strtotime($row['IntakeTime'])) ?>
          </p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="card">
        <p>No taken medication history found.</p>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once '../../templates/partials/footer.php'; ?>
