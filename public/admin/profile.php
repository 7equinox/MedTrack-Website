<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$adminID = $_SESSION['AdminID'];
$update_message = '';
$update_type = '';
$password_message = '';
$password_type = '';

// Handle profile info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $adminName = trim($_POST['admin_name']);
    $email = trim($_POST['email']);

    if (empty($adminName) || empty($email)) {
        $update_message = "Name and Email cannot be empty.";
        $update_type = 'error';
    } else {
        $stmt = $conn->prepare("UPDATE admins SET AdminName = ?, Email = ? WHERE AdminID = ?");
        $stmt->bind_param("sss", $adminName, $email, $adminID);
        if ($stmt->execute()) {
            $_SESSION['AdminName'] = $adminName; // Update session
            $update_message = "Profile updated successfully!";
            $update_type = 'success';
        } else {
            $update_message = "Failed to update profile.";
            $update_type = 'error';
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT Password FROM admins WHERE AdminID = ?");
    $stmt->bind_param("s", $adminID);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($current_password, $result['Password'])) {
        if ($new_password === $confirm_password) {
            if(strlen($new_password) >= 8) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE admins SET Password = ? WHERE AdminID = ?");
                $update_stmt->bind_param("ss", $hashed_password, $adminID);
                if ($update_stmt->execute()) {
                    $password_message = "Password changed successfully.";
                    $password_type = 'success';
                } else {
                    $password_message = "Failed to update password.";
                    $password_type = 'error';
                }
            } else {
                $password_message = "New password must be at least 8 characters long.";
                $password_type = 'error';
            }
        } else {
            $password_message = "New passwords do not match.";
            $password_type = 'error';
        }
    } else {
        $password_message = "Incorrect current password.";
        $password_type = 'error';
    }
}

// Fetch current admin data
$stmt = $conn->prepare("SELECT AdminName, Email FROM admins WHERE AdminID = ?");
$stmt->bind_param("s", $adminID);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

$page_title = 'Admin Profile';
$activePage = 'profile'; // To highlight the link in side menu
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<style>
.profile-main { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
.profile-container { display: flex; gap: 2rem; align-items: flex-start; }
.form-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); flex: 1; }
.form-card h2 { font-size: 1.5rem; margin-bottom: 1.5rem; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
.form-group { margin-bottom: 1.2rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-group input { width: 100%; padding: 0.8rem; border-radius: 4px; border: 1px solid #ccc; }
.btn-save { background-color: #2c3e50; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
.message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
.success { background-color: #d4edda; color: #155724; }
.error { background-color: #f8d7da; color: #721c24; }

@media (max-width: 992px) {
    .profile-container {
        flex-direction: column;
    }
}
</style>

<main class="profile-main">
    <h1>My Profile</h1>

    <div class="profile-container">
        <div class="form-card">
            <h2>Profile Information</h2>
            <?php if ($update_message): ?>
                <div class="message <?= $update_type ?>"><?= $update_message ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="form_type" value="update_profile">
                <div class="form-group">
                    <label for="admin_id">Admin ID</label>
                    <input type="text" id="admin_id" value="<?= htmlspecialchars($adminID) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="admin_name">Name</label>
                    <input type="text" id="admin_name" name="admin_name" value="<?= htmlspecialchars($admin['AdminName']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['Email']) ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
            </form>
        </div>

        <div class="form-card">
            <h2>Change Password</h2>
            <?php if ($password_message): ?>
                <div class="message <?= $password_type ?>"><?= $password_message ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="form_type" value="change_password">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn-save">Change Password</button>
            </form>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?>
<script src="../js/doctor_app.js"></script>
