<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$doctorID = $_GET['id'] ?? '';
if (!$doctorID) {
    die("No Doctor ID provided.");
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorName = trim($_POST['DoctorName']);
    $email = trim($_POST['Email']);
    $contactNumber = trim($_POST['ContactNumber']);
    $newPassword = $_POST['Password'];

    $sql = "UPDATE doctor SET DoctorName=?, Email=?, ContactNumber=? ";
    $types = "sss";
    $params = [$doctorName, $email, $contactNumber];

    if (!empty($newPassword)) {
        $sql .= ", Password=? ";
        $types .= "s";
        $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $sql .= "WHERE DoctorID=?";
    $types .= "s";
    $params[] = $doctorID;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $success_message = "Doctor details updated successfully!";
    } else {
        $error_message = "Database error: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Fetch current doctor data
$stmt = $conn->prepare("SELECT DoctorName, Email, ContactNumber FROM doctor WHERE DoctorID = ?");
$stmt->bind_param("s", $doctorID);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
    die("Doctor not found.");
}

$page_title = 'Edit Doctor';
$body_class = 'page-doctor-patient-edit';
$base_path = '../..';
$activePage = 'doctor';
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<main>
    <h1 class="page-title" style="text-align: center; margin-bottom: 2rem;">Edit Doctor: <?= htmlspecialchars($doctorID) ?></h1>

    <?php if ($success_message): ?>
        <div class="alert success" style="max-width: 900px; margin: 0 auto 1.5rem auto;"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert error" style="max-width: 900px; margin: 0 auto 1.5rem auto; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" class="add-med-form" style="display: block; max-width: 900px; margin: auto;">
        <div class="form-grid">
            <div class="form-group">
                <label for="DoctorID">Doctor ID</label>
                <input type="text" value="<?= htmlspecialchars($doctorID) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="DoctorName">Full Name</label>
                <input type="text" id="DoctorName" name="DoctorName" value="<?= htmlspecialchars($doctor['DoctorName']) ?>" required>
            </div>
            <div class="form-group col-span-2">
                <label for="Email">Email Address</label>
                <input type="email" id="Email" name="Email" value="<?= htmlspecialchars($doctor['Email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="ContactNumber">Contact Number</label>
                <input type="text" id="ContactNumber" name="ContactNumber" value="<?= htmlspecialchars($doctor['ContactNumber']) ?>">
            </div>
            <div class="form-group">
                <label for="Password">New Password</label>
                <input type="password" id="Password" name="Password" placeholder="Leave blank to keep current password">
            </div>
        </div>
        <div class="form-actions" style="margin-top: 1rem;">
            <a href="doctor_management.php" class="btn btn-cancel">Back to List</a>
            <button type="submit" class="btn btn-save">Save Changes</button>
        </div>
    </form>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?> 