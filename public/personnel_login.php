<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $personnelID = trim($_POST['personnel_id']);
    $password = trim($_POST['password']);
    // ===
    // ==> 1) Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM personnel WHERE PersonnelID = ?");
    // ==> 2) Bind the user input as data
    $stmt->bind_param("s", $personnelID);
    // ==> 3) Execute the safe query
    $stmt->execute();
    $result = $stmt->get_result();
    $personnel = $result->fetch_assoc();

    if ($personnel) {
        $passwordInDb = $personnel['Password'];
        // password_get_info returns info about a hash. If algo is 0, it's not a known hash.
        $isHashed = password_get_info($passwordInDb)['algo'] !== 0;

        $loginSuccess = false;

        if ($isHashed) {
            // ==> 4) Securely verify the password hash
            // DB password is a modern hash, verify it
            if (password_verify($password, $passwordInDb)) {
                $loginSuccess = true;
                // ===
                // If a newer hashing algorithm is available, rehash and update the password
                if (password_needs_rehash($passwordInDb, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE personnel SET Password = ? WHERE PersonnelID = ?");
                    $updateStmt->bind_param("ss", $newHash, $personnelID);
                    $updateStmt->execute();
                }
            }
        } else {
            // This is for migrating from plaintext passwords.
            if ($password === $passwordInDb) {
                $loginSuccess = true;
                // Hash the password and update it in the database for future logins
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE personnel SET Password = ? WHERE PersonnelID = ?");
                $updateStmt->bind_param("ss", $newHash, $personnelID);
                $updateStmt->execute();
            }
        }

        if ($loginSuccess) {
            $_SESSION['PersonnelID'] = $personnel['PersonnelID'];
            $_SESSION['PersonnelName'] = $personnel['PersonnelName'];

            if (!empty($_POST["remember"])) {
                // Set cookie for Personnel ID for 30 days
                setcookie("remember_personnel_id", $personnelID, time() + (86400 * 30), "/");
            } else {
                // Unset Personnel ID cookie
                if (isset($_COOKIE['remember_personnel_id'])) {
                    setcookie("remember_personnel_id", "", time() - 3600, "/");
                }
            }
            // Always ensure the password cookie is removed for security
            if (isset($_COOKIE['remember_personnel_password'])) {
                setcookie("remember_personnel_password", "", time() - 3600, "/");
            }

            header("Location: personnel/dashboard.php");
            exit();
        }
    }
    
    // If we reach here, it's an invalid login attempt.
    $error = "Invalid Personnel ID or Password.";
}
?>

<?php
$pageTitle = 'Medical Personnel Login - MedTrack';
$base_path = './';
require_once '../templates/partials/header.php';
?>

<body class="page-personnel-login">
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
        <h2>MedTrack Medical Personnel Log In</h2>

        <?php if (!empty($error)): ?>
          <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label for="personnel-id">Medical Personnel ID</label>
            <input type="text" name="personnel_id" id="personnel-id" required value="<?php echo htmlspecialchars($_POST['personnel_id'] ?? $_COOKIE['remember_personnel_id'] ?? ''); ?>" />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-field">
              <input type="password" name="password" id="password" required value="" />
              <span class="toggle-icon"></span>
            </div>
          </div>

          <div class="options">
            <a href="./personnel/forgot_password.php">Forgot Password?</a>
          </div>

          <div class="checkbox-group">
            <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) || ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_COOKIE['remember_personnel_id'])) ? 'checked' : ''; ?> />
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
