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
    $doctorID = trim($_POST['DoctorID']);
    $doctorName = trim($_POST['DoctorName']);
    $email = trim($_POST['Email']);
    $password = $_POST['Password'];
    $contactNumber = trim($_POST['ContactNumber']);

    if (empty($doctorID) || empty($doctorName) || empty($email) || empty($password)) {
        $error_message = "ID, Name, Email, and Password are required.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO doctor (DoctorID, DoctorName, Email, Password, ContactNumber) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $doctorID, $doctorName, $email, $hashedPassword, $contactNumber);
        
        if ($stmt->execute()) {
            $success_message = "Medical doctor '" . htmlspecialchars($doctorName) . "' added successfully!";
        } else {
            if ($conn->errno == 1062) { // Duplicate entry
                $error_message = "Error: A doctor member with this ID or Email already exists.";
            } else {
                $error_message = "Database error: " . htmlspecialchars($stmt->error);
            }
        }
        $stmt->close();
    }
}

$page_title = 'Add Doctor';
$body_class = 'page-doctor-patient-edit';
$base_path = '../..';
$activePage = 'doctor';
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<main>
    <h1 class="page-title" style="text-align: center; margin-bottom: 2rem;">Add New Medical Doctor</h1>

    <?php if ($success_message): ?>
        <div class="alert success" style="max-width: 900px; margin: 0 auto 1.5rem auto;"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert error" style="max-width: 900px; margin: 0 auto 1.5rem auto; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" class="add-med-form" style="display: block; max-width: 900px; margin: auto; padding: 2.5rem; border-radius: 12px;">
        <div class="form-grid">
            <div class="form-group">
                <label for="DoctorID">Doctor ID</label>
                <input type="text" id="DoctorID" name="DoctorID" placeholder="e.g., MP-0006" required>
            </div>
            <div class="form-group">
                <label for="DoctorName">Full Name</label>
                <input type="text" id="DoctorName" name="DoctorName" required>
            </div>
            <div class="form-group col-span-2">
                <label for="Email">Email Address</label>
                <input type="email" id="Email" name="Email" required>
            </div>
            <div class="form-group">
                <label for="Password">Password</label>
                <input type="password" id="Password" name="Password" required>
            </div>
            <div class="form-group">
                <label for="ContactNumber">Contact Number</label>
                <input type="text" id="ContactNumber" name="ContactNumber">
            </div>
        </div>
        <div class="form-actions" style="margin-top: 1rem;">
            <a href="doctor_management.php" class="btn btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-save">Add Doctor</button>
        </div>
    </form>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?> 