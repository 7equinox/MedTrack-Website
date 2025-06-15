<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staffID = trim($_POST['staff_id']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
    $stmt->bind_param("s", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if ($staff && $password === $staff['Password']) {
        $_SESSION['StaffID'] = $staff['StaffID'];
        $_SESSION['StaffName'] = $staff['StaffName'];

        if (!empty($_POST["remember"])) {
            // Set cookies for 30 days
            setcookie("remember_staff_id", $staffID, time() + (86400 * 30), "/");
            setcookie("remember_staff_password", $password, time() + (86400 * 30), "/");
        } else {
            // Unset cookies
            if (isset($_COOKIE['remember_staff_id'])) {
                setcookie("remember_staff_id", "", time() - 3600, "/");
            }
            if (isset($_COOKIE['remember_staff_password'])) {
                setcookie("remember_staff_password", "", time() - 3600, "/");
            }
        }

        header("Location: staff/dashboard.php");
        exit();
    } else {
        $error = "Invalid Staff ID or Password.";
    }
}
?>

<?php
$pageTitle = 'Staff Login - MedTrack';
$base_path = './';
require_once '../templates/partials/header.php';
?>

<body class="page-staff-login">
  <div class="container">
    <div class="left-panel"></div>
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
        <h2>MedTrack Staff Log In</h2>

        <?php if (!empty($error)): ?>
          <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label for="staff-id">Staff ID</label>
            <input type="text" name="staff_id" id="staff-id" required value="<?php echo htmlspecialchars($_POST['staff_id'] ?? $_COOKIE['remember_staff_id'] ?? ''); ?>" />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-field">
              <input type="password" name="password" id="password" required value="<?php echo htmlspecialchars($_POST['password'] ?? $_COOKIE['remember_staff_password'] ?? ''); ?>" />
              <span class="toggle-icon"></span>
            </div>
          </div>

          <div class="options">
            <a href="#">Forgot Password</a>
          </div>

          <div class="checkbox-group">
            <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) || ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_COOKIE['remember_staff_id'])) ? 'checked' : ''; ?> />
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
  <script src="js/auth.js"></script>
</body>
</html>
