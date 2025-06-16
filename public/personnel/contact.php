<?php
$page_title = 'Contact Us';
$body_class = 'page-contact-us';
$base_path = '../..';
$activePage = 'contact';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/personnel_header.php';
?>

    <main class="contact-main">
        <h1>Contact Us</h1>
        <div class="contact-container">
            <div class="contact-image">
                <img src="<?= $base_path ?>/public/images/medicine.jpg" alt="Caregiver assisting patient" />
            </div>
            <div class="contact-details">
                <div class="card">
                    <h2>
                        <svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Message Us
                    </h2>
                    <p>Have a question? Send us a message. Please provide details for a faster response.</p>
                    <p><strong>medtrack@gmail.com</strong></p>
                </div>
                <div class="card call">
                    <h2>
                        <svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        Call Us
                    </h2>
                    <p>Reach our support team by phone</p>
                    <p><strong>(01) 1234 5678</strong></p>
                </div>
            </div>
        </div>
    </main>

<?php 
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php'; 
?> 