<?php
session_start();

if (!isset($_SESSION["PatientID"])) {
    header("Location: ../patient_login.php");
    exit();
}

// Prevent caching so browser back won't show dashboard
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once '../../config/database.php'; // Your DB connection

$patientID = $_SESSION["PatientID"]; // ✅ Fix: Retrieve the patient ID from session

// Get patient info
$patientStmt = $conn->prepare("SELECT * FROM patients WHERE PatientID = ?");
$patientStmt->bind_param("s", $patientID);
$patientStmt->execute();
$patient = $patientStmt->get_result()->fetch_assoc();

// Get all medication schedules
$medStmt = $conn->prepare("
    SELECT 
        m.*,
        ROW_NUMBER() OVER (PARTITION BY m.PrescriptionGUID ORDER BY m.IntakeTime ASC) as DoseNumber,
        (m.Frequency * (CASE WHEN m.DurationUnit = 'weeks' THEN m.Duration * 7 ELSE m.Duration END)) as TotalDoses
    FROM 
        medicationschedule m
    WHERE 
        m.PatientID = ?
    ORDER BY 
        m.IntakeTime ASC
");
$medStmt->bind_param("s", $patientID);
$medStmt->execute();
$allMedications = $medStmt->get_result();

$upcomingMeds = [];
$takenMeds = [];
$firstReminder = null;

while ($row = $allMedications->fetch_assoc()) {
    if ($row['Status'] === 'Taken') {
        $takenMeds[] = $row;
    } else {
        $upcomingMeds[] = $row;
        if (!$firstReminder) $firstReminder = $row;
    }
}

?>
<?php
$pageTitle = 'Patient Dashboard - MedTrack';
$activePage = 'dashboard';
$base_path = '../';
require_once '../../templates/partials/header.php';
?>

<body class="page-patient-area page-patient-dashboard">
  <?php require_once '../../templates/partials/patient_side_menu.php'; ?>
  <?php require_once '../../templates/partials/patient_header.php'; ?>

  <!-- Reminder Popup -->
  <?php if ($firstReminder): ?>
    <div class="reminder-popup" id="reminderPopup">
      <p>
        Reminder: <?= htmlspecialchars($firstReminder['MedicationName']) ?> 
        (Dose <?= htmlspecialchars($firstReminder['DoseNumber']) ?> of <?= htmlspecialchars($firstReminder['TotalDoses']) ?>) 
        at <?= htmlspecialchars(date('Y-m-d h:i A', strtotime($firstReminder['IntakeTime']))) ?>
      </p>
      <span class="close-btn" id="closePopup">✖</span>
    </div>
  <?php endif; ?>

  <main>
    <div class="dashboard-header">
      <h1>Welcome, <?= htmlspecialchars($patient['PatientName']) ?></h1>
      <a href="med_history.php" class="history-btn" id="viewHistory">View History</a>
    </div>

    <div class="table-wrapper">
      <table class="medication-table">
        <thead>
          <tr>
            <th>Medication</th>
            <th>Dosage</th>
            <th>For</th>
            <th>Frequency</th>
            <th>Duration</th>
            <th>Dose No.</th>
            <th>Intake Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($upcomingMeds) > 0): ?>
            <?php foreach ($upcomingMeds as $med): ?>
              <tr>
                <td><?= htmlspecialchars($med['MedicationName']) ?></td>
                <td><?= htmlspecialchars($med['Dosage']) ?></td>
                <td><?= htmlspecialchars($med['MedicationFor']) ?></td>
                <td><?= htmlspecialchars($med['Frequency'] ?? 'N/A') ?>x per day</td>
                <td>
                    <?php
                        $unit = htmlspecialchars($med['DurationUnit'] ?? '');
                        $displayUnit = ($unit === 'weeks') ? 'week(s)' : (($unit === 'days') ? 'day(s)' : $unit);
                        echo (htmlspecialchars($med['Duration'] ?? 'N/A') . ' ' . $displayUnit);
                    ?>
                </td>
                <td><?= htmlspecialchars($med['DoseNumber']) ?> / <?= htmlspecialchars($med['TotalDoses']) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d h:i A', strtotime($med['IntakeTime']))) ?></td>
                <td><?= htmlspecialchars($med['Status']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center">No upcoming medications.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

<?php require_once '../../templates/partials/footer.php'; ?> 