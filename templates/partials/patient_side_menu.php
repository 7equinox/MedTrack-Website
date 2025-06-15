<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$patientName = "Unknown Patient";
$patientIDForDisplay = "PT-XXXX";
$patientPic = $base_path . 'images/default-prof-patient.png';

if (isset($conn) && isset($_SESSION['PatientID'])) {
    $patientID = $_SESSION['PatientID'];
    $patientIDForDisplay = htmlspecialchars($patientID);
    $stmt = $conn->prepare("SELECT PatientName, ProfilePicture FROM patients WHERE PatientID = ?");
    if ($stmt) {
        $stmt->bind_param("s", $patientID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $sideMenuPatient = $result->fetch_assoc();
            $patientName = htmlspecialchars($sideMenuPatient['PatientName']);
            if (!empty($sideMenuPatient['ProfilePicture'])) {
                $patientPic = $base_path . htmlspecialchars($sideMenuPatient['ProfilePicture']);
            }
        }
    }
}
?>
<div class="menu-overlay" id="menuOverlay"></div>
<div class="side-menu" id="sideMenu">
    <button class="side-menu-close" id="closeMenu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <div class="side-menu-content">
        <a href="<?= $base_path ?>patient/profile.php" class="profile-link">
            <div class="profile-header <?= (isset($activePage) && $activePage === 'profile') ? 'active' : '' ?>">
                <div class="profile-pic-wrapper">
                    <img src="<?= $patientPic ?>" alt="Profile" class="profile-pic">
                </div>
                <div class="profile-label">
                    <span class="profile-name"><?= $patientName ?></span>
                    <span class="profile-id"><?= $patientIDForDisplay ?></span>
                </div>
            </div>
        </a>
        <ul class="side-menu-links">
            <li><a href="<?php echo $base_path; ?>patient/dashboard.php" class="<?php echo (isset($activePage) && $activePage === 'dashboard') ? 'active' : ''; ?>">Home</a></li>
            <li class="show-on-mobile"><a href="<?php echo $base_path; ?>patient/dashboard.php" class="<?php echo (isset($activePage) && $activePage === 'dashboard') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo $base_path; ?>about.php" class="<?php echo (isset($activePage) && $activePage === 'about') ? 'active' : ''; ?>">About</a></li>
            <li><a href="<?php echo $base_path; ?>contact.php" class="<?php echo (isset($activePage) && $activePage === 'contact') ? 'active' : ''; ?>">Contact Us</a></li>
            <li><a href="<?php echo $base_path; ?>patient/terms.php" class="<?php echo (isset($activePage) && $activePage === 'terms') ? 'active' : ''; ?>">Terms and Conditions</a></li>
        </ul>
    </div>

    <div class="logout-button-container">
        <a href="<?php echo $base_path; ?>index.php" class="logout-button">Log Out</a>
    </div>
</div>
