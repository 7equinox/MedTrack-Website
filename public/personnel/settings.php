<?php
session_start();
$page_title = 'Settings';
$body_class = 'page-personnel-settings';
$base_path = '../..';
$activePage = 'settings';
require_once __DIR__ . '/../../config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['PersonnelID']) || !isset($_SESSION['PersonnelName'])) {
    header("Location: $base_path/public/personnel/personnel_login.php");
    exit();
}

$PersonnelID = $_SESSION['PersonnelID'];

// Fetch personnel info from database
$query = "SELECT * FROM personnel WHERE PersonnelID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $PersonnelID);
$stmt->execute();
$result = $stmt->get_result();
$personnel = $result->fetch_assoc();

// Default values
$PersonnelName = $personnel['PersonnelName'] ?? 'Unknown Medical Personnel';
$personnelPic = !empty($personnel['ProfilePicture']) ? $base_path . '/' . $personnel['ProfilePicture'] : $base_path . '/public/images/default-prof-personnel.png';

require_once __DIR__ . '/../../templates/partials/personnel_header.php';
?>

<main>
    <div class="settings-header">
        <h1 class="settings-title">Settings</h1>
        <a href="dashboard.php" class="back-btn">Go Back to Dashboard</a>
    </div>
    <div class="settings-content-wrapper">
        <div class="settings-user-profile">
            <img src="<?= htmlspecialchars($personnelPic) ?>" alt="User Profile" class="profile-pic">
            <div>
                <h2 class="user-name"><?= htmlspecialchars($PersonnelName) ?></h2>
                <p class="personnel-id">Medical Personnel ID: <?= htmlspecialchars($PersonnelID) ?></p>
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
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php'; 
?>
