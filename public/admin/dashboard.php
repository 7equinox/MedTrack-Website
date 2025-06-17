<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

$page_title = 'Admin Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../../templates/partials/admin_header.php';
?>

<main class="admin-dashboard-main">
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['AdminName'] ?? 'Admin'); ?>!</p>
    </div>

    <div class="management-links">
        <a href="doctor_management.php" class="management-link link-doctor">
            Manage Doctor
        </a>
        <a href="patient_management.php" class="management-link link-patients">
            Manage Patients
        </a>
    </div>
</main>

<?php
require_once __DIR__ . '/../../templates/partials/admin_side_menu.php';
require_once __DIR__ . '/../../templates/partials/admin_footer.php';
?>
<script src="../js/doctor_app.js"></script> 