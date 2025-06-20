<?php

// --- MedTrack DB Auto-Setup ---
$host = 'localhost';
$username = 'root';
$password = '';

// Establish connection to MySQL server
$conn_setup = new mysqli($host, $username, $password);

if ($conn_setup->connect_error) {
    die("MySQL connection for setup failed: " . $conn_setup->connect_error);
}

// Include the setup script and run it
require_once __DIR__ . '/../config/setup.php';
setupDatabase($conn_setup);

// Close the setup connection
$conn_setup->close();
// --- End of Auto-Setup ---

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
        <p class="instruction">Good day! Please log in to proceed.</p>
        <div class="buttons">
          <a href="patient_login.php" class="btn btn-patient">Patient</a>
          <a href="doctor_login.php" class="btn btn-doctor">Doctor</a>
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