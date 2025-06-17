<?php
$page_title = 'Patient Medication History';
$body_class = 'page-doctor-patient-med-history';
$base_path = '../..';
$activePage = 'patient_list';
require_once __DIR__ . '/../../templates/partials/doctor_header.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<main><p class='error'>No Patient ID provided.</p></main>";
    require_once __DIR__ . '/../../templates/partials/doctor_footer.php';
    exit();
}

$patientID = $_GET['id'];

$query = "SELECT PatientName FROM patients WHERE PatientID = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "<main><p class='error'>Failed to prepare query: " . htmlspecialchars($conn->error) . "</p></main>";
    require_once __DIR__ . '/../../templates/partials/doctor_footer.php';
    exit();
}

$stmt->bind_param("s", $patientID);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    echo "<main><p class='error'>Invalid Patient ID submitted.</p></main>";
    require_once __DIR__ . '/../../templates/partials/doctor_footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleID = $_POST['ScheduleID'];
    $medName = $_POST['MedicationName'];
    $dosage = $_POST['Dosage'];
    $medicationFor = $_POST['MedicationFor'];
    $intakeTime = date('Y-m-d H:i:00', strtotime($_POST['IntakeTime']));
    $status = $_POST['Status'];

    $update = $conn->prepare("UPDATE medicationschedule SET MedicationName=?, Dosage=?, MedicationFor=?, IntakeTime=?, Status=? WHERE ScheduleID=? AND PatientID=?");
    $update->bind_param("sssssis", $medName, $dosage, $medicationFor, $intakeTime, $status, $scheduleID, $patientID);
    $update->execute();
    $update->close();
    header("Location: patient_med_history.php?id=" . urlencode($patientID) . "&update_success=1");
    exit();
}
?>

<main>
    <?php if (isset($_GET['update_success'])): ?>
        <div class="alert success" id="success-panel">
            Medication information has been successfully updated.
        </div>
    <?php endif; ?>

  <div class="history-header">
    <div>
      <p class="breadcrumb">Medication History</p>
      <h1 class="patient-title"><?= htmlspecialchars($patientID) . ' - ' . htmlspecialchars($patient['PatientName']); ?></h1>
    </div>
    <a href="<?= $base_path; ?>/public/doctor/patient_list.php" class="btn back-btn">Go Back to Patient List</a>
  </div>

  <div class="history-list">
    <?php
    $query = "
        SELECT 
            ScheduleID, MedicationName, IntakeTime, Dosage, MedicationFor, Frequency, Duration, DurationUnit, Status,
            ROW_NUMBER() OVER (PARTITION BY PrescriptionGUID ORDER BY IntakeTime ASC) as DoseNumber,
            (Frequency * (CASE WHEN DurationUnit = 'weeks' THEN Duration * 7 ELSE Duration END)) as TotalDoses
        FROM medicationschedule 
        WHERE PatientID = ? AND Status IN ('Taken', 'Missed', 'Upcoming') 
        ORDER BY IntakeTime DESC
    ";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $patientID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $original_med_name = htmlspecialchars($row['MedicationName']);
                $original_intake_time = htmlspecialchars(date('Y-m-d\TH:i:s', strtotime($row['IntakeTime'])));
                $original_dosage = htmlspecialchars($row['Dosage']);
                $original_med_for = htmlspecialchars($row['MedicationFor']);
                $original_frequency = htmlspecialchars($row['Frequency'] ?? '');
                $original_duration = htmlspecialchars($row['Duration'] ?? '');
                $original_duration_unit = htmlspecialchars($row['DurationUnit'] ?? '');
                $original_dose_number = htmlspecialchars($row['DoseNumber']);
                $total_doses = htmlspecialchars($row['TotalDoses']);
                $original_status = htmlspecialchars($row['Status']);

                echo '<form method="POST" class="history-card edit-row">';
                echo '<input type="hidden" name="ScheduleID" value="' . htmlspecialchars($row['ScheduleID']) . '">';

                echo '<div class="field-group">';
                echo '<label>Medication</label>';
                echo '<input type="text" name="MedicationName" value="' . $original_med_name . '" data-original-value="' . $original_med_name . '">';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Dosage</label>';
                echo '<input type="text" name="Dosage" value="' . $original_dosage . '" data-original-value="' . $original_dosage . '">';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>For</label>';
                echo '<input type="text" name="MedicationFor" value="' . $original_med_for . '" data-original-value="' . $original_med_for . '">';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Dose No.</label>';
                echo '<input type="text" value="' . $original_dose_number . ' / ' . $total_doses . '" readonly>';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Frequency</label>';
                echo '<input type="text" value="' . $original_frequency . 'x per day" readonly>';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Duration</label>';
                $unit = $original_duration_unit;
                $displayUnit = ($unit === 'weeks') ? 'week(s)' : (($unit === 'days') ? 'day(s)' : $unit);
                echo '<input type="text" value="' . $original_duration . ' ' . $displayUnit . '" readonly>';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Intake Time</label>';
                echo '<input type="datetime-local" name="IntakeTime" value="' . $original_intake_time . '" data-original-value="' . $original_intake_time . '" readonly>';
                echo '</div>';

                echo '<div class="field-group">';
                echo '<label>Status</label>';
                echo '<select name="Status" data-original-value="' . $original_status . '">
                        <option value="Taken" ' . ($row['Status'] === 'Taken' ? 'selected' : '') . '>Taken</option>
                        <option value="Missed" ' . ($row['Status'] === 'Missed' ? 'selected' : '') . '>Missed</option>
                        <option value="Upcoming" ' . ($row['Status'] === 'Upcoming' ? 'selected' : '') . '>Upcoming</option>
                      </select>';
                echo '</div>';

                echo '<div class="field-group btn-group">';
                echo '<button type="button" class="btn btn-cancel" disabled>Cancel</button>';
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
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
  }
  .btn-edit, .btn-cancel {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  .btn-edit {
    background: #2196F3;
    color: #fff;
  }
  .btn-edit:hover {
    background: #1976D2;
  }
  .btn-cancel {
      background-color: #e0e0e0;
      color: #333;
  }
  .btn-cancel:disabled {
      background-color: #f5f5f5;
      color: #aaa;
      cursor: not-allowed;
  }
  .alert.success {
      background-color: #d4edda;
      color: #155724;
      padding: 1rem;
      border: 1px solid #c3e6cb;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      text-align: center;
  }

  .field-group input[readonly] {
      background-color: #e9ecef;
      cursor: not-allowed;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Hide the success panel after 3 seconds
    const successPanel = document.getElementById('success-panel');
    if (successPanel) {
        setTimeout(() => {
            successPanel.style.transition = 'opacity 0.5s ease';
            successPanel.style.opacity = '0';
            setTimeout(() => successPanel.style.display = 'none', 500);
        }, 3000);
    }

    // Handle form changes for each medication row
    document.querySelectorAll('.edit-row').forEach(form => {
        const inputs = form.querySelectorAll('input[name], select[name]');
        const cancelBtn = form.querySelector('.btn-cancel');

        function checkForChanges() {
            let hasChanged = false;
            inputs.forEach(input => {
                if (input.value !== input.dataset.originalValue) {
                    hasChanged = true;
                }
            });
            cancelBtn.disabled = !hasChanged;
        }

        inputs.forEach(input => {
            input.addEventListener('input', checkForChanges);
        });

        cancelBtn.addEventListener('click', function() {
            inputs.forEach(input => {
                input.value = input.dataset.originalValue;
                // For select, we also need to ensure the selected property is right
                if (input.tagName === 'SELECT') {
                    Array.from(input.options).forEach(option => {
                        option.selected = (option.value === input.dataset.originalValue);
                    });
                }
            });
            this.disabled = true; // Directly disable the button on click
        });
    });
});
</script>

<?php
require_once __DIR__ . '/../../templates/partials/doctor_side_menu.php';
require_once __DIR__ . '/../../templates/partials/doctor_footer.php';
?>
