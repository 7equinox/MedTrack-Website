<?php
$page_title = 'Terms & Conditions';
$body_class = 'page-patient-terms';
$base_path = '../..';
$activePage = 'terms';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../templates/partials/personnel_header.php';
?>

  <main>
    <h1 class="page-title">Terms & Conditions</h1>
    <section class="terms-container">
      <p>By using MedTrack, you agree to these terms:</p>
        <ul>
            <li>No Medical Advice: MedTrack is a reminder tool, not a substitute for professional medical advice. Always consult your doctor.</li>
            <li>Your Data & Responsibility: You are responsible for your entered information and keeping your account secure.</li>
            <li>"As Is" Service: MedTrack is provided without warranty. We are not liable for any health outcomes or service issues.</li>
            <li>Changes: Terms may be updated. Your continued use implies acceptance.</li>
            <li>Contact: Questions? Email support@medtrackapp.com</li>
        </ul>
    </section>
  </main>

<?php 
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php'; 
?> 