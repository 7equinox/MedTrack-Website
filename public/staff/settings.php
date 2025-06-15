<?php
session_start();
$page_title = 'Settings';
$body_class = 'page-staff-settings';
$base_path = '../..';
$activePage = 'settings';
require_once __DIR__ . '/../../config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['StaffID']) || !isset($_SESSION['StaffName'])) {
    header("Location: $base_path/public/staff/staff_login.php");
    exit();
}

$staffID = $_SESSION['StaffID'];

// Fetch staff info from database
$query = "SELECT * FROM staff WHERE StaffID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $staffID);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

// Default values
$staffName = $staff['StaffName'] ?? 'Unknown Staff';
$staffPic = !empty($staff['ProfilePicture']) ? $base_path . '/' . $staff['ProfilePicture'] : $base_path . '/public/images/default-prof-staff.png';

require_once __DIR__ . '/../../templates/partials/staff_header.php';
?>

<main>
    <div class="settings-header">
        <h1 class="settings-title">Settings</h1>
        <a href="dashboard.php" class="back-btn">Go Back to Dashboard</a>
    </div>
    <div class="settings-content-wrapper">
        <div class="settings-user-profile">
            <img src="<?= htmlspecialchars($staffPic) ?>" alt="User Profile" class="profile-pic">
            <div>
                <h2 class="user-name"><?= htmlspecialchars($staffName) ?></h2>
                <p class="staff-id">Staff ID: <?= htmlspecialchars($staffID) ?></p>
            </div>
        </div>
        <div class="settings-options-list">
            <a href="profile.php" class="settings-option-card">
                <span>Customize Profile</span>
                <span class="arrow-icon">&gt;</span>
            </a>
            <a href="archive.php" class="settings-option-card">
                <span>Archive</span>
                <span class="arrow-icon">&gt;</span>
            </a>
            <a href="terms.php" class="settings-option-card">
                <span>Terms & Conditions</span>
                <span class="arrow-icon">&gt;</span>
            </a>
        </div>
    </div>
</main>

<?php 
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php'; 
?>
