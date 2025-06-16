<?php
$page_title = 'Edit Patient';
$body_class = 'page-personnel-patient-edit';
$base_path = '../..';
$activePage = 'patient_list';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/personnel_header.php';

$patientID = $_GET['id'] ?? '';
$today = date('Y-m-d');

if (!$patientID) {
    die("No Patient ID provided.");
}

// Handle form submission first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['PatientName']);
    $birthdate = $_POST['Birthdate'];
    $sex = $_POST['Sex'];
    $address = trim($_POST['HomeAddress']);
    $email = trim($_POST['Email']);
    $contact = trim($_POST['ContactNumber']);
    $room = trim($_POST['RoomNumber']);

    $update = $conn->prepare("UPDATE patients SET PatientName=?, Birthdate=?, Sex=?, HomeAddress=?, Email=?, ContactNumber=?, RoomNumber=? WHERE PatientID=?");
    if (!$update) {
        die("Prepare failed: " . $conn->error);
    }

    $update->bind_param("ssssssss", $name, $birthdate, $sex, $address, $email, $contact, $room, $patientID);
    
    if ($update->execute()) {
        $update->close();
        header("Location: patient_edit.php?id=" . urlencode($patientID) . "&update_success=1");
        exit();
    } else {
        $error_message = "Update failed: " . addslashes($update->error);
    }
    $update->close();
}

// Fetch patient data for the form
$stmt = $conn->prepare("SELECT PatientName, Birthdate, Sex, HomeAddress, Email, ContactNumber, RoomNumber FROM patients WHERE PatientID = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $patientID);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Patient not found.");
}
?>

<main>
  <h1 class="page-title" style="text-align: center; margin-bottom: 2rem;">Edit Patient: <?= htmlspecialchars($patientID) ?></h1>

    <?php if (isset($_GET['update_success'])): ?>
        <div class="alert success" id="success-panel" style="max-width: 900px; margin: 0 auto 1.5rem auto;">
            Patient information has been successfully updated.
        </div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert error" style="max-width: 900px; margin: 0 auto 1.5rem auto; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

  <form method="POST" class="add-med-form" style="display: block; max-width: 900px; margin: auto; padding: 2.5rem; border-radius: 12px;">
      <div class="form-grid">
          <div class="form-group">
              <label for="patient-id">Patient ID</label>
              <input type="text" id="patient-id" value="<?= htmlspecialchars($patientID) ?>" disabled />
          </div>
          <div class="form-group">
              <label for="patient-name">Patient Name</label>
              <input type="text" id="patient-name" name="PatientName" value="<?= htmlspecialchars($patient['PatientName'] ?? '') ?>" required />
          </div>
          <div class="form-group">
              <label for="birthdate">Birthdate</label>
              <input type="date" id="birthdate" name="Birthdate" value="<?= htmlspecialchars($patient['Birthdate'] ?? '') ?>" required max="<?= $today ?>" />
          </div>
          <div class="form-group">
              <label for="sex">Sex</label>
              <select id="sex" name="Sex" required>
                  <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
                      <option value="<?= $option ?>" <?= ($patient['Sex'] ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          <div class="form-group col-span-2">
            <label for="home-address">Home Address</label>
            <input type="text" id="home-address" name="HomeAddress" value="<?= htmlspecialchars($patient['HomeAddress'] ?? '') ?>" required>
          </div>
          <div class="form-group col-span-2">
              <label for="email">Email</label>
              <input type="email" id="email" name="Email" value="<?= htmlspecialchars($patient['Email'] ?? '') ?>" />
          </div>
          <div class="form-group">
              <label for="contact-number">Contact Number</label>
              <input type="text" id="contact-number" name="ContactNumber" value="<?= htmlspecialchars($patient['ContactNumber'] ?? '') ?>" required />
          </div>
          <div class="form-group">
              <label for="room-number">Room Number</label>
              <input type="text" id="room-number" name="RoomNumber" value="<?= htmlspecialchars($patient['RoomNumber'] ?? '') ?>" required />
          </div>
      </div>
      <div class="form-actions" style="margin-top: 1rem;">
          <a href="<?= $base_path ?>/public/personnel/patient_list.php" class="btn btn-cancel">Cancel</a>
          <button type="submit" class="btn btn-save">Save Changes</button>
      </div>
  </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Set max date for birthdate input
    const birthdateInput = document.getElementById('birthdate');
    if (birthdateInput) {
        birthdateInput.max = new Date().toISOString().split("T")[0];
    }

    const successPanel = document.getElementById('success-panel');
    if (successPanel) {
        setTimeout(() => {
            successPanel.style.transition = 'opacity 0.5s ease';
            successPanel.style.opacity = '0';
            setTimeout(() => successPanel.style.display = 'none', 500);
        }, 3000);
    }
});
</script>

<?php
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php';
?>