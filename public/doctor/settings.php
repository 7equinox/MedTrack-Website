<?php
session_start();
$page_title = 'Settings';
$body_class = 'page-doctor-settings';
$base_path = '../..';
$activePage = 'settings';
require_once __DIR__ . '/../../config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['DoctorID']) || !isset($_SESSION['DoctorName'])) {
    header("Location: $base_path/public/doctor/doctor_login.php");
    exit();
}

$DoctorID = $_SESSION['DoctorID'];

// Fetch doctor info from database
$query = "SELECT * FROM doctor WHERE DoctorID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $DoctorID);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Default values
$DoctorName = $doctor['DoctorName'] ?? 'Unknown Doctor';
$doctorPic = !empty($doctor['ProfilePicture']) ? $base_path . '/' . $doctor['ProfilePicture'] : $base_path . '/public/images/default-prof-doctor.png';

require_once __DIR__ . '/../../templates/partials/doctor_header.php';
?>

<main>
    <div class="settings-header">
        <h1 class="settings-title">Settings</h1>
        <a href="dashboard.php" class="back-btn">Go Back to Dashboard</a>
    </div>
    <div class="settings-content-wrapper">
        <div class="settings-user-profile">
            <img src="<?= htmlspecialchars($doctorPic) ?>" alt="User Profile" class="profile-pic">
            <div>
                <h2 class="user-name"><?= htmlspecialchars($DoctorName) ?></h2>
                <p class="doctor-id">Doctor ID: <?= htmlspecialchars($DoctorID) ?></p>
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
require_once __DIR__ . '/../../templates/partials/doctor_side_menu.php';
require_once __DIR__ . '/../../templates/partials/doctor_footer.php'; 
?>
