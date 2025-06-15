<?php
$page_title = 'Patient Medication History';
$body_class = 'page-staff-patient-med-history';
$base_path = '../..';
$activePage = 'patient_list';
require_once __DIR__ . '/../../templates/partials/staff_header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<main><p class='error'>No Patient ID provided.</p></main>";
    require_once __DIR__ . '/../../templates/partials/staff_footer.php';
    exit();
}

$patientID = $_GET['id'];

$query = "SELECT PatientName FROM patients WHERE PatientID = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "<main><p class='error'>Failed to prepare query: " . htmlspecialchars($conn->error) . "</p></main>";
    require_once __DIR__ . '/../../templates/partials/staff_footer.php';
    exit();
}

$stmt->bind_param("s", $patientID);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    echo "<main><p class='error'>Invalid Patient ID submitted.</p></main>";
    require_once __DIR__ . '/../../templates/partials/staff_footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleID = $_POST['ScheduleID'];
    $medName = $_POST['MedicationName'];
    $dosage = $_POST['Dosage'];
    $intakeTime = $_POST['IntakeTime'];
    $status = $_POST['Status'];

    $update = $conn->prepare("UPDATE medicationschedule SET MedicationName=?, Dosage=?, IntakeTime=?, Status=? WHERE ScheduleID=? AND PatientID=?");
    $update->bind_param("ssssss", $medName, $dosage, $intakeTime, $status, $scheduleID, $patientID);
    $update->execute();
    $update->close();
    header("Location: patient_med_history.php?id=" . urlencode($patientID));
    exit();
}
?>

<main>
  <div class="history-header">
    <div>
      <p class="breadcrumb">Medication History</p>
      <h1 class="patient-title"><?= htmlspecialchars($patientID) . ' - ' . htmlspecialchars($patient['PatientName']); ?></h1>
    </div>
    <a href="<?= $base_path; ?>/public/staff/patient_list.php" class="btn back-btn">Go Back to Patient List</a>
  </div>

  <div class="history-list">
    <?php
    $query = "SELECT ScheduleID, MedicationName, IntakeTime, Dosage, Status FROM medicationschedule WHERE PatientID = ? AND Status IN ('Taken', 'Missed', 'Upcoming') ORDER BY IntakeTime DESC";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $patientID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<form method="POST" class="history-card edit-row">';
                echo '<input type="hidden" name="ScheduleID" value="' . htmlspecialchars($row['ScheduleID']) . '">';

                echo '<div class="field-group">';
                echo '<label>Medication</label>';
                echo '<input type="text" name="MedicationName" value="' . htmlspecialchars($row['MedicationName']) . '">';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Intake Time</label>';
                echo '<input type="datetime-local" name="IntakeTime" value="' . date('Y-m-d\TH:i', strtotime($row['IntakeTime'])) . '">';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Dosage</label>';
                echo '<input type="text" name="Dosage" value="' . htmlspecialchars($row['Dosage']) . '">';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Status</label>';
                echo '<select name="Status">
                        <option ' . ($row['Status'] === 'Taken' ? 'selected' : '') . '>Taken</option>
                        <option ' . ($row['Status'] === 'Missed' ? 'selected' : '') . '>Missed</option>
                        <option ' . ($row['Status'] === 'Upcoming' ? 'selected' : '') . '>Upcoming</option>
                      </select>';
                echo '</div>';

                echo '<div class="field-group btn-group">';
                echo '<button type="submit" class="btn btn-edit">Save</button>';
                echo '</div>';

                echo '</form>';
            }
        } else {
            echo "<p>No medication history found for this patient.</p>";
        }
        $stmt->close();
    } else {
        echo "<p class='error'>Failed to fetch medication history: " . htmlspecialchars($conn->error) . "</p>";
    }
    ?>
  </div>
</main>

<style>
  .history-card {
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 10px;
    margin-bottom: 1rem;
    padding: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    align-items: center;
  }
  .field-group label {
    display: block;
    font-size: 0.85rem;
    margin-bottom: 4px;
    color: #444;
  }
  .field-group input,
  .field-group select {
    width: 100%;
    padding: 0.4rem;
    border: 1px solid #aaa;
    border-radius: 5px;
    font-size: 0.9rem;
  }
  .btn-group {
    text-align: right;
  }
  .btn-edit {
    padding: 0.5rem 1rem;
    background: #2196F3;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  .btn-edit:hover {
    background: #1976D2;
  }
</style>

<?php
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php';
?>
