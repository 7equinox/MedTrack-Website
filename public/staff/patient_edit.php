<?php
$page_title = 'Edit Patient';
$body_class = 'page-staff-patient-edit';
$base_path = '../..';
$activePage = 'patient_list';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/staff_header.php';

$patientID = $_GET['id'] ?? '';
$patient = null;
$updateSuccess = false;

if (!$patientID) {
    die("No Patient ID provided.");
}

$stmt = $conn->prepare("SELECT PatientName, Birthdate, Sex, Email, ContactNumber, RoomNumber FROM patients WHERE PatientID = ?");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['PatientName']);
    $birthdate = $_POST['Birthdate'];
    $sex = $_POST['Sex'];
    $email = trim($_POST['Email']);
    $contact = trim($_POST['ContactNumber']);
    $room = trim($_POST['RoomNumber']);

    $update = $conn->prepare("UPDATE patients SET PatientName=?, Birthdate=?, Sex=?, Email=?, ContactNumber=?, RoomNumber=? WHERE PatientID=?");
    if (!$update) {
        die("Prepare failed: " . $conn->error);
    }

    $update->bind_param("sssssss", $name, $birthdate, $sex, $email, $contact, $room, $patientID);
    if ($update->execute()) {
        $updateSuccess = true;
        $patient = [
            'PatientName' => $name,
            'Birthdate' => $birthdate,
            'Sex' => $sex,
            'Email' => $email,
            'ContactNumber' => $contact,
            'RoomNumber' => $room
        ];
    } else {
        echo "<script>alert('Update failed: " . addslashes($update->error) . "');</script>";
    }
    $update->close();
}
?>

<main>
  <h1 class="page-title">Edit Patient: <?= htmlspecialchars($patientID) ?></h1>

  <div class="list-container">
    <div class="form-wrapper">
      <?php if ($updateSuccess): ?>
        <div class="alert success">Patient updated successfully.</div>
      <?php endif; ?>

      <form method="POST" class="edit-form page-patient-profile">
        <div class="form-group g-col-2">
          <label>Patient ID</label>
          <input type="text" value="<?= htmlspecialchars($patientID) ?>" disabled class="form-control disabled" />
        </div>

        <div class="form-group g-col-4">
          <label>Patient Name</label>
          <input type="text" name="PatientName" value="<?= htmlspecialchars($patient['PatientName']) ?>" required />
        </div>

        <div class="form-group g-col-3">
          <label>Birthdate</label>
          <input type="date" name="Birthdate" value="<?= htmlspecialchars($patient['Birthdate']) ?>" required />
        </div>

        <div class="form-group g-col-3">
          <label>Sex</label>
          <select name="Sex" required>
            <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
              <option value="<?= $option ?>" <?= $patient['Sex'] === $option ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group g-col-4">
          <label>Email</label>
          <input type="email" name="Email" value="<?= htmlspecialchars($patient['Email']) ?>" />
        </div>

        <div class="form-group g-col-4">
          <label>Contact Number</label>
          <input type="text" name="ContactNumber" value="<?= htmlspecialchars($patient['ContactNumber']) ?>" />
        </div>

        <div class="form-group g-col-4">
          <label>Room Number</label>
          <input type="text" name="RoomNumber" value="<?= htmlspecialchars($patient['RoomNumber']) ?>" />
        </div>

        <div class="form-group g-col-6" style="margin-top: 1.5rem;">
          <button type="submit" class="btn btn-edit">Save Changes</button>
          <a href="<?= $base_path ?>/public/staff/patient_list.php" class="btn btn-cancel">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php';
?>