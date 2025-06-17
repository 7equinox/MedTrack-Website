<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$patientID = $_GET['id'] ?? '';
if (!$patientID) {
    die("No Patient ID provided.");
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['PatientName']);
    $birthdate = $_POST['Birthdate'];
    $sex = $_POST['Sex'];
    $address = trim($_POST['HomeAddress']);
    $email = trim($_POST['Email']);
    $contact = trim($_POST['ContactNumber']);
    $room = trim($_POST['RoomNumber']);

    $stmt = $conn->prepare("UPDATE patients SET PatientName=?, Birthdate=?, Sex=?, HomeAddress=?, Email=?, ContactNumber=?, RoomNumber=? WHERE PatientID=?");
    $stmt->bind_param("ssssssss", $name, $birthdate, $sex, $address, $email, $contact, $room, $patientID);

    if ($stmt->execute()) {
        $success_message = "Patient details updated successfully!";
    } else {
        $error_message = "Database error: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Fetch current patient data
$stmt = $conn->prepare("SELECT * FROM patients WHERE PatientID = ?");
$stmt->bind_param("s", $patientID);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Patient not found.");
}

$page_title = 'Edit Patient';
$body_class = 'page-doctor-patient-edit';
$base_path = '../..';
$activePage = 'patients';
$today = date('Y-m-d');
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<main>
    <h1 class="page-title" style="text-align: center; margin-bottom: 2rem;">Edit Patient: <?= htmlspecialchars($patientID) ?></h1>

    <?php if ($success_message): ?>
        <div class="alert success" style="max-width: 900px; margin: 0 auto 1.5rem auto;"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert error" style="max-width: 900px; margin: 0 auto 1.5rem auto; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" class="add-med-form" style="display: block; max-width: 900px; margin: auto;">
        <div class="form-grid">
            <div class="form-group">
                <label for="PatientID">Patient ID</label>
                <input type="text" value="<?= htmlspecialchars($patient['PatientID']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="PatientName">Full Name</label>
                <input type="text" id="PatientName" name="PatientName" value="<?= htmlspecialchars($patient['PatientName']) ?>" required>
            </div>
            <div class="form-group">
                <label for="Birthdate">Birthdate</label>
                <input type="date" id="Birthdate" name="Birthdate" value="<?= htmlspecialchars($patient['Birthdate']) ?>" required max="<?= $today ?>">
            </div>
            <div class="form-group">
                <label for="Sex">Sex</label>
                <select id="Sex" name="Sex" required>
                    <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
                        <option value="<?= $option ?>" <?= ($patient['Sex'] ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-span-2">
                <label for="HomeAddress">Home Address</label>
                <input type="text" id="HomeAddress" name="HomeAddress" value="<?= htmlspecialchars($patient['HomeAddress']) ?>">
            </div>
            <div class="form-group">
                <label for="Email">Email Address</label>
                <input type="email" id="Email" name="Email" value="<?= htmlspecialchars($patient['Email']) ?>">
            </div>
            <div class="form-group">
                <label for="ContactNumber">Contact Number</label>
                <input type="text" id="ContactNumber" name="ContactNumber" value="<?= htmlspecialchars($patient['ContactNumber']) ?>">
            </div>
             <div class="form-group">
                <label for="RoomNumber">Room Number</label>
                <input type="text" id="RoomNumber" name="RoomNumber" value="<?= htmlspecialchars($patient['RoomNumber']) ?>">
            </div>
        </div>
        <div class="form-actions" style="margin-top: 1rem;">
            <a href="patient_management.php" class="btn btn-cancel">Back to List</a>
            <button type="submit" class="btn btn-save">Save Changes</button>
        </div>
    </form>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?> 