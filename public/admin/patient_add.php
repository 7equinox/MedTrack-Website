<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['PatientID']);
    $name = trim($_POST['PatientName']);
    $birthdate = $_POST['Birthdate'];
    $sex = $_POST['Sex'];
    $address = trim($_POST['HomeAddress']);
    $email = trim($_POST['Email']);
    $contact = trim($_POST['ContactNumber']);
    $room = trim($_POST['RoomNumber']);

    if (empty($id) || empty($name) || empty($birthdate) || empty($sex)) {
        $error_message = "ID, Name, Birthdate, and Sex are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO patients (PatientID, PatientName, Birthdate, Sex, HomeAddress, Email, ContactNumber, RoomNumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $id, $name, $birthdate, $sex, $address, $email, $contact, $room);

        if ($stmt->execute()) {
            $success_message = "Patient '" . htmlspecialchars($name) . "' added successfully!";
        } else {
            if ($conn->errno == 1062) {
                $error_message = "Error: A patient with this ID already exists.";
            } else {
                $error_message = "Database error: " . htmlspecialchars($stmt->error);
            }
        }
        $stmt->close();
    }
}

$page_title = 'Add Patient';
$body_class = 'page-doctor-patient-edit';
$base_path = '../..';
$activePage = 'patients';
$today = date('Y-m-d');
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<main>
    <h1 class="page-title" style="text-align: center; margin-bottom: 2rem;">Add New Patient</h1>

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
                <input type="text" id="PatientID" name="PatientID" placeholder="e.g., PT-0016" required>
            </div>
            <div class="form-group">
                <label for="PatientName">Full Name</label>
                <input type="text" id="PatientName" name="PatientName" required>
            </div>
            <div class="form-group">
                <label for="Birthdate">Birthdate</label>
                <input type="date" id="Birthdate" name="Birthdate" required max="<?= $today ?>">
            </div>
            <div class="form-group">
                <label for="Sex">Sex</label>
                <select id="Sex" name="Sex" required>
                    <option value="" disabled selected>Select Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group col-span-2">
                <label for="HomeAddress">Home Address</label>
                <input type="text" id="HomeAddress" name="HomeAddress">
            </div>
            <div class="form-group">
                <label for="Email">Email Address</label>
                <input type="email" id="Email" name="Email">
            </div>
            <div class="form-group">
                <label for="ContactNumber">Contact Number</label>
                <input type="text" id="ContactNumber" name="ContactNumber">
            </div>
             <div class="form-group">
                <label for="RoomNumber">Room Number</label>
                <input type="text" id="RoomNumber" name="RoomNumber">
            </div>
        </div>
        <div class="form-actions" style="margin-top: 1rem;">
            <a href="patient_management.php" class="btn btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-save">Add Patient</button>
        </div>
    </form>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?> 