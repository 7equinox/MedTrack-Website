<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$personnelID = $_GET['id'] ?? '';
if (!$personnelID) {
    die("No Personnel ID provided.");
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personnelName = trim($_POST['PersonnelName']);
    $email = trim($_POST['Email']);
    $contactNumber = trim($_POST['ContactNumber']);
    $newPassword = $_POST['Password'];

    $sql = "UPDATE personnel SET PersonnelName=?, Email=?, ContactNumber=? ";
    $types = "sss";
    $params = [$personnelName, $email, $contactNumber];

    if (!empty($newPassword)) {
        $sql .= ", Password=? ";
        $types .= "s";
        $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    $sql .= "WHERE PersonnelID=?";
    $types .= "s";
    $params[] = $personnelID;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $success_message = "Personnel details updated successfully!";
    } else {
        $error_message = "Database error: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Fetch current personnel data
$stmt = $conn->prepare("SELECT PersonnelName, Email, ContactNumber FROM personnel WHERE PersonnelID = ?");
$stmt->bind_param("s", $personnelID);
$stmt->execute();
$personnel = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$personnel) {
    die("Personnel not found.");
}

$page_title = 'Edit Personnel';
$body_class = 'page-personnel-patient-edit';
$base_path = '../..';
$activePage = 'personnel';
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<main>
    <h1 class="page-title" style="text-align: center; margin-bottom: 2rem;">Edit Personnel: <?= htmlspecialchars($personnelID) ?></h1>

    <?php if ($success_message): ?>
        <div class="alert success" style="max-width: 900px; margin: 0 auto 1.5rem auto;"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert error" style="max-width: 900px; margin: 0 auto 1.5rem auto; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST" class="add-med-form" style="display: block; max-width: 900px; margin: auto;">
        <div class="form-grid">
            <div class="form-group">
                <label for="PersonnelID">Personnel ID</label>
                <input type="text" value="<?= htmlspecialchars($personnelID) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="PersonnelName">Full Name</label>
                <input type="text" id="PersonnelName" name="PersonnelName" value="<?= htmlspecialchars($personnel['PersonnelName']) ?>" required>
            </div>
            <div class="form-group col-span-2">
                <label for="Email">Email Address</label>
                <input type="email" id="Email" name="Email" value="<?= htmlspecialchars($personnel['Email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="ContactNumber">Contact Number</label>
                <input type="text" id="ContactNumber" name="ContactNumber" value="<?= htmlspecialchars($personnel['ContactNumber']) ?>">
            </div>
            <div class="form-group">
                <label for="Password">New Password</label>
                <input type="password" id="Password" name="Password" placeholder="Leave blank to keep current password">
            </div>
        </div>
        <div class="form-actions" style="margin-top: 1rem;">
            <a href="personnel_management.php" class="btn btn-cancel">Back to List</a>
            <button type="submit" class="btn btn-save">Save Changes</button>
        </div>
    </form>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?> 