<?php // templates/partials/patient_header.php ?>
<header class="flex items-center justify-between px-6 py-4 bg-white shadow-md relative z-50">
    <!-- Logo -->
    <div class="logo">
        <a href="<?php echo $base_path; ?>patient/dashboard.php">
            <img src="<?php echo $base_path; ?>images/logo-with-label.png" alt="MedTrack Logo" class="h-10">
        </a>
    </div>

    <!-- Menu Toggle Button (always visible) -->
    <button class="menu-toggle" id="menuToggle" aria-label="Open Menu">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>
</header>
