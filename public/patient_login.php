<?php
session_start();

// Connect to database
$conn = new mysqli("localhost", "root", "", "medtrackdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patientID = strtoupper(trim($_POST["patient-id"])); // Ensure uppercase input

    $sql = "SELECT * FROM patients WHERE PatientID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $_SESSION["PatientID"] = $patientID;
        header("Location: ./patient/dashboard.php");
        exit();
    } else {
        $error = "Invalid Patient ID.";
    }

    $stmt->close();
}
$conn->close();
?>

<?php
$pageTitle = 'Patient Login - MedTrack';
$base_path = './';
require_once '../templates/partials/header.php';
?>
<body class="page-patient-login">
  <div class="container">
    <div class="left-panel">
      <!-- You can add a background image or slogan here -->
    </div>
    <div class="right-panel">
      <a href="index.php" class="back-button">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </a>
      <div class="content">
        <div class="logo">
          <img src="<?php echo $base_path; ?>images/logo.png" alt="MedTrack Logo">
        </div>
        <h1>MedTrack Patient Log In</h1>

        <!-- Show error if login fails -->
        <?php if (!empty($error)): ?>
          <div class="error-message" style="color: red; margin-bottom: 1rem;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="">
          <div class="form-group">
            <label for="patient-id">Patient ID</label>
            <input type="text" id="patient-id" name="patient-id" required />
          </div>

          <div class="checkbox-group">
            <input type="checkbox" id="remember" name="remember" />
            <label for="remember">Remember Me</label>
          </div>

          <div class="button-container">
            <button type="submit" class="btn">Sign in</button>
          </div>
        </form>

        <p class="terms">
          By using this service, you understood and agree to MedTrack's
          <a href="terms.php">Terms of Use</a>.
        </p>
      </div>
    </div>
  </div>
</body>
</html>
