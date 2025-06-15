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
$medStmt = $conn->prepare("SELECT MedicationName, Dosage, IntakeTime FROM medicationschedule WHERE PatientID = ? AND Status = 'Taken'");
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
$conn->close();
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
          <h2><?= htmlspecialchars($row['MedicationName']) ?> | <?= date("h:i A", strtotime($row['IntakeTime'])) ?></h2>
          <p><?= htmlspecialchars($row['Dosage']) ?> | Taken</p>
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
