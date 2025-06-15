<?php
session_start();
$pageTitle = 'Terms & Conditions - MedTrack';
$activePage = 'terms';
$base_path = '../';
require_once '../../config/database.php';
require_once '../../templates/partials/header.php';
?>

<body class="page-patient-area page-patient-terms">
  <?php require_once '../../templates/partials/patient_side_menu.php'; ?>
  <?php require_once '../../templates/partials/patient_header.php'; ?>

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

<?php require_once '../../templates/partials/footer.php'; ?>

</body>
</html> 