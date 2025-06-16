<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$personnelName = "Unknown Personnel";
$personnelPic = $base_path . '/public/images/default-prof-personnel.png';

if (isset($conn) && isset($_SESSION['PersonnelID'])) {
    $personnelID = $_SESSION['PersonnelID'];
    $stmt = $conn->prepare("SELECT PersonnelName, ProfilePicture FROM personnel WHERE PersonnelID = ?");
    if ($stmt) {
        $stmt->bind_param("s", $personnelID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $personnel = $result->fetch_assoc();
            $personnelName = htmlspecialchars($personnel['PersonnelName']);
            if (!empty($personnel['ProfilePicture'])) {
                // Prepend base_path to the stored relative path
                $personnelPic = $base_path . '/' . htmlspecialchars($personnel['ProfilePicture']);
            }
        }
    }
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
        <a href="<?= $base_path ?>/public/personnel/profile.php" class="profile-link">
            <div class="profile-header <?= ($activePage === 'profile') ? 'active' : '' ?>">
                <div class="profile-pic-wrapper">
                    <img src="<?= $personnelPic ?>" alt="Profile" class="profile-pic">
                </div>
                <div class="profile-label">
                    <span class="profile-name"><?= $personnelName ?></span>
                    <span class="profile-id"><?= htmlspecialchars($_SESSION['PersonnelID'] ?? 'MD-XXXX') ?></span>
                </div>
            </div>
        </a>
        <ul class="side-menu-links">
            <li class="show-on-mobile"><a href="<?= $base_path ?>/public/personnel/dashboard.php" class="<?= ($activePage === 'dashboard') ? 'active' : '' ?>">Home</a></li>
            <li class="show-on-mobile"><a href="<?= $base_path ?>/public/personnel/patient_list.php" class="<?= ($activePage === 'patient_list') ? 'active' : '' ?>">Patient List</a></li>
            <li class="show-on-mobile"><a href="<?= $base_path ?>/public/personnel/report_log.php" class="<?= ($activePage === 'report_log') ? 'active' : '' ?>">Reports</a></li>
            <li><a href="<?= $base_path ?>/public/personnel/settings.php" class="<?= ($activePage === 'settings') ? 'active' : '' ?>">Settings</a></li>
            <li><a href="<?= $base_path ?>/public/personnel/about.php" class="<?= ($activePage === 'about') ? 'active' : '' ?>">About</a></li>
            <li><a href="<?= $base_path ?>/public/personnel/contact.php" class="<?= ($activePage === 'contact') ? 'active' : '' ?>">Contact Us</a></li>
        </ul>
    </div>
    <div class="logout-button-container">
        <a href="<?= $base_path ?>/public/index.php" class="logout-button">Log Out</a>
    </div>
</div>
