<div class="menu-overlay" id="menuOverlay"></div>
<aside class="side-menu" id="sideMenu">
    <div class="side-menu-header">
        <div class="logo">
            <img src="../images/logo.png" alt="MedTrack Logo">
        </div>
        <button class="side-menu-close" id="closeMenu">&times;</button>
    </div>

    <div class="side-menu-content">
        <a href="profile.php" class="profile-link">
            <div class="profile-header">
                <div class="profile-label">
                    <span class="profile-name"><?= htmlspecialchars($_SESSION['AdminName'] ?? 'Admin'); ?></span>
                    <span class="profile-id"><?= htmlspecialchars($_SESSION['AdminID'] ?? 'AD-???'); ?></span>
                </div>
            </div>
        </a>
        <ul class="side-menu-links">
            <li><a href="dashboard.php" class="<?= ($activePage ?? '') == 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="doctor_management.php" class="<?= ($activePage ?? '') == 'doctor' ? 'active' : '' ?>">Doctor</a></li>
            <li><a href="patient_management.php" class="<?= ($activePage ?? '') == 'patients' ? 'active' : '' ?>">Patients</a></li>
        </ul>
    </div>

    <div class="logout-button-container">
        <a href="../logout.php" class="logout-button">Logout</a>
    </div>
</aside> 