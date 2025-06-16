<?php
session_start();
if (!isset($_SESSION['PersonnelName'])) {
    header("Location: ../../personnel/personnel_login.php");
    exit();
}
$PersonnelName = $_SESSION['PersonnelName'];

require_once __DIR__ . '/../../config/database.php';

// --- Automatically update overdue medications to 'Missed' ---
$updateOverdueStatus = "UPDATE medicationschedule SET Status = 'Missed' WHERE Status = 'Upcoming' AND IntakeTime < NOW()";
$conn->query($updateOverdueStatus);
// -------------------------------------------------------------

// Handle add medication form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medication'])) {
    $patientID = trim($_POST['patient_id']);
    $medicationName = trim($_POST['medication_name']);
    $dosage = trim($_POST['dosage']);
    $intakeTime = date('Y-m-d H:i:s', strtotime($_POST['intake_time']));

    // Validate required fields
    if ($patientID === '' || $medicationName === '' || $dosage === '' || $intakeTime === '') {
        echo "<p style='color:red;'>Please fill in all fields.</p>";
        exit();
    }

    // Check if PatientID exists AND is not archived
    $check = $conn->prepare("SELECT 1 FROM patients WHERE PatientID = ? AND IsArchived = FALSE");
    $check->bind_param("s", $patientID);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows === 0) {
        echo "<p style='color:red;'>Error: Patient ID <strong>$patientID</strong> is invalid or archived.</p>";
        exit();
    }

    // Proceed with insert
    $stmt = $conn->prepare("INSERT INTO medicationschedule (PatientID, MedicationName, Dosage, IntakeTime, Status) VALUES (?, ?, ?, ?, 'Upcoming')");
    if ($stmt) {
        $stmt->bind_param("ssss", $patientID, $medicationName, $dosage, $intakeTime);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<p style='color:red;'>Insert failed: " . htmlspecialchars($stmt->error) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
    }
}

// Fetch medication data with patient info
$sql = "
    SELECT m.PatientID, p.PatientName, m.MedicationName, m.Dosage, m.IntakeTime, p.RoomNumber
    FROM medicationschedule m
    INNER JOIN patients p ON m.PatientID = p.PatientID
    WHERE p.IsArchived = FALSE
    ORDER BY m.IntakeTime
";
$result = $conn->query($sql);

// Fetch patient list for dropdown
$patients = $conn->query("SELECT PatientID, PatientName FROM patients WHERE IsArchived = FALSE ORDER BY PatientName");

// Stats
$totalPatients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
$scheduledMeds = $conn->query("SELECT COUNT(*) as count FROM medicationschedule WHERE Status = 'Upcoming'")->fetch_assoc()['count'];
$completeMeds = $conn->query("SELECT COUNT(*) as count FROM medicationschedule WHERE Status = 'Taken'")->fetch_assoc()['count'];
$missedMeds = $conn->query("SELECT COUNT(*) as count FROM medicationschedule WHERE Status = 'Missed'")->fetch_assoc()['count'];

// Missed alerts
$missedAlerts = $conn->query("SELECT p.PatientName, p.RoomNumber, m.MedicationName, m.IntakeTime FROM medicationschedule m JOIN patients p ON m.PatientID = p.PatientID WHERE m.Status = 'Missed' AND p.IsArchived = FALSE ORDER BY m.IntakeTime DESC");

$page_title = 'Medical Personnel Dashboard';
$body_class = 'page-personnel-dashboard';
$base_path = '../..';
$activePage = 'dashboard';
require_once __DIR__ . '/../../templates/partials/personnel_header.php';

// Auto-generate reports for missed medications if not already logged
$missedQuery = $conn->query("
    SELECT m.PatientID, p.PatientName, p.RoomNumber, m.MedicationName
    FROM medicationschedule m
    JOIN patients p ON m.PatientID = p.PatientID
    WHERE m.Status = 'Missed' AND p.IsArchived = FALSE
");

while ($missed = $missedQuery->fetch_assoc()) {
    $patientID = $missed['PatientID'];
    $details = $missed['PatientName'] . " missed their medication (" . $missed['MedicationName'] . ") in Room " . $missed['RoomNumber'] . ".";

    // Check if this report already exists (status still Inspect)
    $checkReport = $conn->prepare("SELECT 1 FROM reports WHERE PatientID = ? AND ReportDetails = ? AND ReportStatus = 'Inspect'");
    $checkReport->bind_param("ss", $patientID, $details);
    $checkReport->execute();
    $existing = $checkReport->get_result();

    if ($existing->num_rows === 0) {
        $personnelID = $_SESSION['PersonnelID'] ?? 'System'; // fallback if not available

        $insertReport = $conn->prepare("INSERT INTO reports (PatientID, PersonnelID, ReportDetails, ReportDate) VALUES (?, ?, ?, NOW())");
        $insertReport->bind_param("sss", $patientID, $personnelID, $details);
        $insertReport->execute();
    }
}
?>

<main>
<h1 class="welcome-title">Welcome <?= htmlspecialchars($PersonnelName) ?></h1>

<section class="dashboard-content">
    <div class="left-column">
        <section class="stats-cards">
            <div class="card total-patients">
                <span class="card-value"><?= $totalPatients ?></span>
                <span class="card-label">Total Patients</span>
            </div>
            <div class="card scheduled-meds">
                <span class="card-value"><?= $scheduledMeds ?></span>
                <span class="card-label">Scheduled Meds</span>
            </div>
            <div class="card complete-meds">
                <span class="card-value"><?= $completeMeds ?></span>
                <span class="card-label">Complete Meds</span>
            </div>
            <div class="card missed-meds">
                <span class="card-value"><?= $missedMeds ?></span>
                <span class="card-label">Missed Meds</span>
            </div>
        </section>
        <div class="patient-list-container">
            <div class="search-bar" style="margin-bottom: 1.5rem;">
                <input id="dashboard-search-input" type="text" placeholder="Search by patient, medication, room...">
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Time</th>
                            <th>Room No.</th>
                        </tr>
                    </thead>
                    <tbody id="patient-list-body">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['PatientID']) ?></td>
                                    <td><?= htmlspecialchars($row['PatientName']) ?></td>
                                    <td><?= htmlspecialchars($row['MedicationName']) ?></td>
                                    <td><?= htmlspecialchars($row['Dosage']) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d h:i A', strtotime($row['IntakeTime']))) ?></td>
                                    <td><?= htmlspecialchars($row['RoomNumber']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No medication schedules found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <button id="toggle-med-form" class="btn btn-add-med-toggle">Add Medication</button>

            <form method="POST" id="medication-form" class="add-med-form hidden">
                <h3 class="form-title">Add New Medication Schedule</h3>
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label for="patient-id-select">Patient ID</label>
                        <select name="patient_id" id="patient-id-select" required>
                            <option value="" disabled selected>Select a Patient</option>
                            <?php
                            // We need to reset the mysql result pointer to loop through patients again
                            if (isset($patients) && $patients->num_rows > 0) {
                                mysqli_data_seek($patients, 0);
                                while ($p = $patients->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($p['PatientID']) ?>">[<?= htmlspecialchars($p['PatientID']) ?>] <?= htmlspecialchars($p['PatientName']) ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="medication-name">Medication Name</label>
                        <input type="text" name="medication_name" id="medication-name" required />
                    </div>

                    <div class="form-group">
                        <label for="dosage">Dosage</label>
                        <input type="text" name="dosage" id="dosage" required />
                    </div>

                    <div class="form-group col-span-2">
                        <label for="intake-time">Intake Time</label>
                        <input type="datetime-local" name="intake_time" id="intake-time" required step="60" />
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancel-med-form" class="btn btn-cancel">Cancel</button>
                    <button type="submit" name="add_medication" class="btn btn-save">Save</button>
                </div>
            </form>
        </div>
    </div>
    <div class="right-column">
        <aside class="alert-panel">
            <h2>Alert Panel Section</h2>
            <?php if ($missedAlerts->num_rows > 0): ?>
                <?php while ($alert = $missedAlerts->fetch_assoc()): ?>
                    <div class="alert-item alert-danger">
                        <div class="alert-content">
                            <?= htmlspecialchars($alert['PatientName']) ?> missed their medication (<?= htmlspecialchars($alert['MedicationName']) ?>) in Room <?= htmlspecialchars($alert['RoomNumber']) ?>.
                            <br>
                            <small style="opacity: 0.8;"><?= date('M d, Y, h:i A', strtotime($alert['IntakeTime'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert-item alert-success">
                    <div class="alert-content">No missed medications.</div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php';
?>
