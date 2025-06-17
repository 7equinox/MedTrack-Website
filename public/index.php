<?php
$pageTitle = 'Landing Page - MedTrack';
$base_path = './';
require_once '../templates/partials/header.php';
?>
<!-- === -->
<body class="page-index">
  <div class="container">
    <div class="left-panel">
      
    </div>
    <div class="right-panel">
      <div class="content">
        <div class="logo">
          <img src="<?php echo $base_path; ?>images/logo.png" alt="MedTrack Logo">
        </div>
        <h2>Welcome to</h2>
        <h1>MedTrack</h1>
        <p class="instruction">Hello! Please log in as a Patient or Medical Personnel to proceed.</p>
        <div class="buttons">
          <a href="patient_login.php" class="btn btn-patient">Patient</a>
          <a href="personnel_login.php" class="btn btn-personnel">Medical Personnel</a>
          <a href="admin_login.php" class="btn btn-admin">Administrator</a>
        </div>
        <p class="terms">
          By using this service, you understood and agree to MedTrack's
          <a href="terms.php">Terms of Use</a>.
        </p>
      </div>
    </div>
  </div>
</body>
<!-- === -->
</html> 