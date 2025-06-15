<?php
// Ensure session is started and database is included before this block
if (!isset($_SESSION['StaffID'])) {
    header("Location: $base_path/public/staff/staff_login.php");
    exit();
}

$staffID = $_SESSION['StaffID'];

require_once __DIR__ . '/../../config/database.php';

// Fetch logged-in staff info
$stmt = $conn->prepare("SELECT StaffName, ProfilePicture FROM staff WHERE StaffID = ?");
$stmt->bind_param("s", $staffID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    $staffName = htmlspecialchars($staff['StaffName']);
    $staffPic = !empty($staff['ProfilePicture']) ? $base_path . '/' . $staff['ProfilePicture'] : $base_path . '/public/images/default-prof-staff.png';
} else {
    $staffName = "Unknown Staff";
    $staffPic = $base_path . '/public/images/default-prof-staff.png';
}
?>

<div class="side-menu" id="sideMenu">
    <button class="side-menu-close" id="closeMenu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <div class="side-menu-content">
        <a href="<?= $base_path ?>/public/staff/profile.php" class="profile-link">
            <div class="profile-header <?= ($activePage === 'profile') ? 'active' : '' ?>">
                <img src="<?= $staffPic ?>" alt="Profile" class="profile-pic">
                <div class="profile-info">
                    <span class="profile-name"><?= $staffName ?></span>
                    <span class="profile-role">Medical Staff</span>
                </div>
            </div>
        </a>
        <ul class="side-menu-links">
            <li class="show-on-mobile"><a href="<?= $base_path ?>/public/staff/dashboard.php" class="<?= ($activePage === 'dashboard') ? 'active' : '' ?>">Home</a></li>
            <li class="show-on-mobile"><a href="<?= $base_path ?>/public/staff/patient_list.php" class="<?= ($activePage === 'patient_list') ? 'active' : '' ?>">Patient List</a></li>
            <li class="show-on-mobile"><a href="<?= $base_path ?>/public/staff/report_log.php" class="<?= ($activePage === 'report_log') ? 'active' : '' ?>">Reports/Logs</a></li>
            <li><a href="<?= $base_path ?>/public/staff/settings.php" class="<?= ($activePage === 'settings') ? 'active' : '' ?>">Settings</a></li>
            <li><a href="<?= $base_path ?>/public/staff/about.php" class="<?= ($activePage === 'about') ? 'active' : '' ?>">About</a></li>
            <li><a href="<?= $base_path ?>/public/staff/contact.php" class="<?= ($activePage === 'contact') ? 'active' : '' ?>">Contact Us</a></li>
        </ul>
    </div>
    <div class="logout-button-container">
        <a href="<?= $base_path ?>/public/index.php" class="logout-button">Log Out</a>
    </div>
</div>
