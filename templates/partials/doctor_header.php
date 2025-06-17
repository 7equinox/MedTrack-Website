<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Doctor' ?> - MedTrack</title>
    <link rel="icon" href="<?php echo $base_path; ?>/public/images/logo.png" type="image/png" />
    <link rel="stylesheet" href="<?= $base_path ?>/public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="page-doctor-area <?= $body_class ?? '' ?>">
    <div class="menu-overlay" id="menuOverlay"></div>

    <header>
        <div class="logo">
            <a href="<?= $base_path ?>/public/doctor/dashboard.php">
                <img src="<?= $base_path ?>/public/images/logo-with-label.png" alt="MedTrack Logo" class="logo-img">
            </a>
        </div>
        <nav>
            <ul id="navLinks">
                <li><a href="<?= $base_path ?>/public/doctor/dashboard.php" class="<?= ($activePage === 'dashboard') ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= $base_path ?>/public/doctor/patient_list.php" class="<?= ($activePage === 'patient_list') ? 'active' : '' ?>">Patient List</a></li>
                <li><a href="<?= $base_path ?>/public/doctor/report_log.php" class="<?= ($activePage === 'report_log') ? 'active' : '' ?>">Reports</a></li>
            </ul>
            <button class="menu-toggle" id="menuToggle">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </nav>
    </header>
