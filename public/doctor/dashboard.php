<?php
session_start();
if (!isset($_SESSION['DoctorName'])) {
    header("Location: ../../doctor/doctor_login.php");
    exit();
}
$DoctorName = $_SESSION['DoctorName'];

require_once __DIR__ . '/../../config/database.php';

// Helper function to generate a GUID
function guidv4($data = null) {
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// --- Automatically update overdue medications to 'Missed' ---
$updateOverdueStatus = "UPDATE medicationschedule SET Status = 'Missed' WHERE Status = 'Upcoming' AND IntakeTime < NOW()";
$conn->query($updateOverdueStatus);
// -------------------------------------------------------------

// Handle add medication form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medication'])) {
    $patientID = trim($_POST['patient_id']);
    $medicationName = trim($_POST['medication_name']);
    $dosage = trim($_POST['dosage']);
    $medicationFor = trim($_POST['medication_for']);
    $startTime = new DateTime($_POST['start_time']);
    $frequency = (int)$_POST['frequency'];
    $duration = (int)$_POST['duration'];
    $durationUnit = $_POST['duration_unit'];

    // Validate required fields
    if (empty($patientID) || empty($medicationName) || empty($dosage) || empty($startTime) || empty($frequency) || empty($duration) || empty($durationUnit)) {
        die("Please fill in all fields.");
    }

    // Check if PatientID exists
    $check = $conn->prepare("SELECT 1 FROM patients WHERE PatientID = ? AND IsArchived = FALSE");
    $check->bind_param("s", $patientID);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        die("<p style='color:red;'>Error: Patient ID <strong>$patientID</strong> is invalid or archived.</p>");
    }
    $check->close();

    // Generate a single GUID for this entire prescription batch
    $prescriptionGUID = guidv4();

    // Prepare statement for insertion
    $stmt = $conn->prepare("
        INSERT INTO medicationschedule 
        (PrescriptionGUID, PatientID, MedicationName, Dosage, MedicationFor, Frequency, Duration, DurationUnit, IntakeTime, Status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Upcoming')
    ");
    if (!$stmt) {
        die("<p style='color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>");
    }

    $totalDays = ($durationUnit === 'weeks') ? $duration * 7 : $duration;
    $intervalHours = 24 / $frequency;

    for ($d = 0; $d < $totalDays; $d++) {
        for ($f = 0; $f < $frequency; $f++) {
            $intakeDateTime = clone $startTime;
            $hoursToAdd = ($d * 24) + ($f * $intervalHours);
            $intakeDateTime->add(new DateInterval("PT{$hoursToAdd}H"));
            $intakeTimeStr = $intakeDateTime->format('Y-m-d H:i:s');
            
            $stmt->bind_param("sssssiiss", $prescriptionGUID, $patientID, $medicationName, $dosage, $medicationFor, $frequency, $duration, $durationUnit, $intakeTimeStr);
            $stmt->execute();
        }
    }
    
            $stmt->close();
    header("Location: dashboard.php?add_success=1");
            exit();
}

// --- Search and Pagination Logic ---
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 10;
$offset = ($page - 1) * $rows_per_page;
$search_query = "%" . $search_term . "%";

// --- Get total number of records for pagination ---
$total_rows_sql = "
    SELECT COUNT(*) as total
    FROM medicationschedule m
    INNER JOIN patients p ON m.PatientID = p.PatientID
    WHERE p.IsArchived = FALSE AND m.Status != 'Taken' AND (m.PatientID LIKE ? OR p.PatientName LIKE ? OR m.MedicationName LIKE ? OR p.RoomNumber LIKE ?)
";
$stmt_total = $conn->prepare($total_rows_sql);
$stmt_total->bind_param("ssss", $search_query, $search_query, $search_query, $search_query);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);


// Fetch medication data with patient info (with pagination)
$sql = "
    SELECT 
        m.PatientID, p.PatientName, m.MedicationName, m.Dosage, m.MedicationFor, 
        m.Frequency, m.Duration, m.DurationUnit, m.IntakeTime, p.RoomNumber,
        ROW_NUMBER() OVER (PARTITION BY m.PrescriptionGUID ORDER BY m.IntakeTime ASC) as DoseNumber,
        (m.Frequency * (CASE WHEN m.DurationUnit = 'weeks' THEN m.Duration * 7 ELSE m.Duration END)) as TotalDoses
    FROM medicationschedule m
    INNER JOIN patients p ON m.PatientID = p.PatientID
    WHERE p.IsArchived = FALSE AND m.Status != 'Taken' AND (m.PatientID LIKE ? OR p.PatientName LIKE ? OR m.MedicationName LIKE ? OR p.RoomNumber LIKE ?)
    ORDER BY m.IntakeTime
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ssssii", $search_query, $search_query, $search_query, $search_query, $rows_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch patient list for dropdown
$patients = $conn->query("SELECT PatientID, PatientName FROM patients WHERE IsArchived = FALSE ORDER BY PatientName");

// Stats
$totalPatients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
$scheduledMeds = $conn->query("SELECT COUNT(*) as count FROM medicationschedule WHERE Status = 'Upcoming'")->fetch_assoc()['count'];
$completeMeds = $conn->query("SELECT COUNT(*) as count FROM medicationschedule WHERE Status = 'Taken'")->fetch_assoc()['count'];
$missedMeds = $conn->query("SELECT COUNT(*) as count FROM medicationschedule WHERE Status = 'Missed'")->fetch_assoc()['count'];

// Missed alerts
$missedAlerts = $conn->query("SELECT p.PatientName, p.RoomNumber, m.MedicationName, m.Dosage, m.MedicationFor, m.IntakeTime FROM medicationschedule m JOIN patients p ON m.PatientID = p.PatientID WHERE m.Status = 'Missed' AND p.IsArchived = FALSE ORDER BY m.IntakeTime DESC");

$page_title = 'Doctor Dashboard';
$body_class = 'page-doctor-dashboard';
$base_path = '../..';
$activePage = 'dashboard';
require_once __DIR__ . '/../../templates/partials/doctor_header.php';

// Auto-generate reports for missed medications if not already logged
$missedQuery = $conn->query("
    SELECT m.ScheduleID, m.PatientID, p.PatientName, p.RoomNumber, m.MedicationName, m.Dosage, m.MedicationFor, m.IntakeTime
    FROM medicationschedule m
    JOIN patients p ON m.PatientID = p.PatientID
    WHERE m.Status = 'Missed' AND p.IsArchived = FALSE
");

while ($missed = $missedQuery->fetch_assoc()) {
    $scheduleID = $missed['ScheduleID'];
    $patientID = $missed['PatientID'];
    $details = "Patient " . $missed['PatientName'] . " (Room " . $missed['RoomNumber'] . ") " .
               "missed their scheduled dose of " . $missed['Dosage'] . " of " . $missed['MedicationName'] . " " .
               "(for " . $missed['MedicationFor'] . "). The dose was scheduled for " . date('M d, Y, h:i A', strtotime($missed['IntakeTime'])) . ".";

    // Check if a report for this specific schedule already exists
    $checkReport = $conn->prepare("SELECT 1 FROM reports WHERE ScheduleID = ?");
    $checkReport->bind_param("i", $scheduleID);
    $checkReport->execute();
    $existing = $checkReport->get_result();

    if ($existing->num_rows === 0) {
        $doctorID = $_SESSION['DoctorID'] ?? 'System'; // fallback if not available

        $insertReport = $conn->prepare("INSERT INTO reports (PatientID, ScheduleID, DoctorID, ReportDetails, ReportDate) VALUES (?, ?, ?, ?, NOW())");
        $insertReport->bind_param("siss", $patientID, $scheduleID, $doctorID, $details);
        $insertReport->execute();
    }
}
?>

<main>
<h1 class="welcome-title">Welcome <?= htmlspecialchars($DoctorName) ?></h1>

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
                <input id="dashboard-search-input" name="search" type="text" placeholder="Search by patient, medication, room..." value="<?= htmlspecialchars($search_term) ?>">
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>For</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Dose No.</th>
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
                                    <td><?= htmlspecialchars($row['MedicationFor']) ?></td>
                                    <td><?= htmlspecialchars($row['Frequency']) ?>x per day</td>
                                    <td>
                                        <?php
                                            $unit = htmlspecialchars($row['DurationUnit']);
                                            $displayUnit = ($unit === 'weeks') ? 'week(s)' : (($unit === 'days') ? 'day(s)' : $unit);
                                            echo htmlspecialchars($row['Duration']) . ' ' . $displayUnit;
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['DoseNumber']) ?> / <?= htmlspecialchars($row['TotalDoses']) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d h:i A', strtotime($row['IntakeTime']))) ?></td>
                                    <td><?= htmlspecialchars($row['RoomNumber']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">No medication schedules found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="pagination-container" class="pagination-container">
                <?php $search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>
                <a href="?page=<?= max(1, $page - 1) . $search_param ?>" class="btn-page <?= ($page <= 1) ? 'disabled' : '' ?>" data-page="<?= max(1, $page - 1) ?>">&laquo; Previous</a>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i . $search_param ?>" class="btn-page <?= ($page == $i) ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>

                <a href="?page=<?= min($total_pages, $page + 1) . $search_param ?>" class="btn-page <?= ($page >= $total_pages) ? 'disabled' : '' ?>" data-page="<?= min($total_pages, $page + 1) ?>">Next &raquo;</a>
            </div>

            <button id="toggle-med-form" class="btn btn-add-med-toggle">Add Medication</button>

            <form method="POST" id="medication-form" class="add-med-form hidden">
                <h3 class="form-title">Add New Medication Schedule</h3>
                <div class="form-grid">
                    <div class="form-group col-span-2">
                        <label for="patient-id-select">Patient</label>
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
                        <input type="text" name="dosage" id="dosage" required placeholder="e.g., 500mg" />
                    </div>

                    <div class="form-group col-span-2">
                        <label for="medication-for">Medication For</label>
                        <input type="text" name="medication_for" id="medication-for" placeholder="e.g., Allergy, Infection" required />
                    </div>

                    <div class="form-group col-span-2">
                        <label for="start-time">Start Time</label>
                        <input type="datetime-local" name="start_time" id="start-time" required step="60" />
                    </div>

                    <div class="form-row-flex">
                        <div class="form-group">
                            <label for="frequency">Frequency</label>
                            <input type="number" name="frequency" id="frequency" required min="1" value="1" />
                        </div>
                        <div class="form-group" style="flex-grow: 0; align-self: flex-end; padding-bottom: 0.75rem;">
                            <span style="font-size: 1rem;">time(s) per day</span>
                        </div>
                    </div>

                    <div class="form-row-flex">
                        <div class="form-group">
                            <label for="duration">Duration</label>
                            <input type="number" name="duration" id="duration" required min="1" value="1" />
                        </div>
                        <div class="form-group">
                             <label for="duration-unit" style="color: transparent;">Unit</label>
                            <select name="duration_unit" id="duration-unit">
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="button" id="cancel-med-form" class="btn btn-cancel">Cancel</button>
                    <button type="submit" name="add_medication" class="btn btn-save">Save</button>
                </div>
                <p style="text-align: center; margin-top: 1rem; font-size: 0.85rem; color: #555;">
                    Note: Start Time, Frequency, and Duration cannot be changed once submitted.
                </p>
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
                            <strong><?= htmlspecialchars($alert['PatientName']) ?></strong> (Room <?= htmlspecialchars($alert['RoomNumber']) ?>) missed <strong><?= htmlspecialchars($alert['Dosage']) ?> of <?= htmlspecialchars($alert['MedicationName']) ?></strong>.
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

<style>
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem 0;
    margin-top: 1.5rem;
    gap: 0.5rem;
}

.pagination-container .btn-page {
    color: #2c3e50;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border: 1px solid #ccc;
    border-radius: 6px;
    transition: background-color 0.2s, color 0.2s;
    font-weight: 500;
}

.pagination-container .btn-page:hover {
    background-color: #f0f8ff;
}

.pagination-container .btn-page.active {
    background-color: #2c3e50;
    color: white;
    border-color: #2c3e50;
    pointer-events: none;
}

.pagination-container .btn-page.disabled {
    color: #aaa;
    pointer-events: none;
    background-color: #f5f5f5;
    border-color: #ddd;
}
</style>

<?php
require_once __DIR__ . '/../../templates/partials/doctor_side_menu.php';
require_once __DIR__ . '/../../templates/partials/doctor_footer.php';
?>
