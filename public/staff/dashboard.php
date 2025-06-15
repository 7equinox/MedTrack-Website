<?php
session_start();
if (!isset($_SESSION['StaffName'])) {
    header("Location: ../../staff/staff_login.php");
    exit();
}
$staffName = $_SESSION['StaffName'];

require_once __DIR__ . '/../../config/database.php';

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
$missedAlerts = $conn->query("SELECT p.PatientName, p.RoomNumber FROM medicationschedule m JOIN patients p ON m.PatientID = p.PatientID WHERE m.Status = 'Missed' AND p.IsArchived = FALSE");

$page_title = 'Staff Dashboard';
$body_class = 'page-staff-dashboard';
$base_path = '../..';
$activePage = 'dashboard';
require_once __DIR__ . '/../../templates/partials/staff_header.php';

// Auto-generate reports for missed medications if not already logged
$missedQuery = $conn->query("
    SELECT m.PatientID, p.PatientName, p.RoomNumber
    FROM medicationschedule m
    JOIN patients p ON m.PatientID = p.PatientID
    WHERE m.Status = 'Missed' AND p.IsArchived = FALSE
");

while ($missed = $missedQuery->fetch_assoc()) {
    $patientID = $missed['PatientID'];
    $details = $missed['PatientName'] . " in Room " . $missed['RoomNumber'] . " missed their scheduled medication.";

    // Check if this report already exists (status still Inspect)
    $checkReport = $conn->prepare("SELECT 1 FROM reports WHERE PatientID = ? AND ReportDetails = ? AND ReportStatus = 'Inspect'");
    $checkReport->bind_param("ss", $patientID, $details);
    $checkReport->execute();
    $existing = $checkReport->get_result();

    if ($existing->num_rows === 0) {
        $staffID = $_SESSION['StaffID'] ?? 'System'; // fallback if not available

        $insertReport = $conn->prepare("INSERT INTO reports (PatientID, StaffID, ReportDetails) VALUES (?, ?, ?)");
        $insertReport->bind_param("sss", $patientID, $staffID, $details);
        $insertReport->execute();
    }
}
?>

<main>
<h1 class="welcome-title">Welcome <?= htmlspecialchars($staffName) ?></h1>

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
                <input type="text" placeholder="Search...">
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
                                    <td><?= htmlspecialchars($row['IntakeTime']) ?></td>
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

            <button id="toggle-med-form" style="margin-top:2rem; background-color:#2c3e50; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;" class="btn">Add Medication</button>
            <form method="POST" id="medication-form" style="display:none; margin-top: 1rem; background:#f9f9f9; padding: 1rem; border-radius: 8px;">
                <div style="margin-bottom: 0.75rem;">
                    <label>Patient ID:</label><br>
                    <select name="patient_id" required style="width: 100%; padding: 0.5rem;">
                        <?php while ($p = $patients->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($p['PatientID']) ?>">[<?= htmlspecialchars($p['PatientID']) ?>] <?= htmlspecialchars($p['PatientName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <label>Medication Name:</label><br>
                    <input type="text" name="medication_name" required style="width: 100%; padding: 0.5rem;" />
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <label>Dosage:</label><br>
                    <input type="text" name="dosage" required style="width: 100%; padding: 0.5rem;" />
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <label>Intake Time:</label><br>
                    <input type="datetime-local" name="intake_time" required style="width: 100%; padding: 0.5rem;" />
                </div>
                <div>
                    <button type="submit" name="add_medication" class="btn" style="background-color:#2c3e50; color:white; padding:10px 20px; border:none; border-radius:5px;">Save</button>
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
                        <div class="alert-content"><?= htmlspecialchars($alert['PatientName']) ?> missed their medication in Room <?= htmlspecialchars($alert['RoomNumber']) ?>.</div>
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

<script>
document.getElementById('toggle-med-form').addEventListener('click', function() {
    const form = document.getElementById('medication-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
</script>

<?php
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php';
?>
