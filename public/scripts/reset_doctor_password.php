<?php
$pageTitle = 'Reset Doctor Password - MedTrack';
$base_path = '../';
require_once '../../templates/partials/header.php';
require_once '../../config/database.php';

$doctor_id = $_GET['id'] ?? '';
$update_success = false;
$error_message = '';

if (empty($doctor_id)) {
    die("No Doctor ID provided. Please go back to the <a href='../doctor/forgot_password.php'>Forgot Password</a> page.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];

    if (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE doctor SET Password = ? WHERE DoctorID = ?");
        $stmt->bind_param("ss", $hashed_password, $doctor_id);

        if ($stmt->execute()) {
            $update_success = true;
        } else {
            $error_message = "Failed to update password. Please try again.";
        }
        $stmt->close();
    }
}
?>

<body class="page-doctor-login">
    <div class="container">
        <div class="left-panel"></div>
        <div class="right-panel">
            <div class="content" style="max-width: 400px; text-align: center;">
                <div class="logo">
                    <a href="../index.php"><img src="<?= $base_path ?>images/logo.png" alt="MedTrack Logo"></a>
                </div>
                <h2>Reset Password</h2>
                
                <?php if ($update_success): ?>
                    <div id="success-panel" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background-color: #d4edda; color: #155724;">
                        Password for <strong><?= htmlspecialchars($doctor_id) ?></strong> has been updated successfully.
                        <p style="margin-top: 1rem;">Redirecting to login page...</p>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '../index.php';
                        }, 3000);
                    </script>
                <?php else: ?>
                    <p class="instruction" style="margin-bottom: 1rem;">
                        Create a new password for Doctor ID: <strong><?= htmlspecialchars($doctor_id) ?></strong>
                    </p>

                    <?php if ($error_message): ?>
                        <div class="message error" style="margin-bottom: 1rem;"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group" style="text-align: left;">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8">
                        </div>
                        <div class="form-group" style="text-align: left;">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="button-container">
                            <button type="submit" class="btn">Save New Password</button>
                        </div>
                    </form>
                    <script>
                        const password = document.getElementById("new_password");
                        const confirm_password = document.getElementById("confirm_password");

                        function validatePassword(){
                          if(password.value != confirm_password.value) {
                            confirm_password.setCustomValidity("Passwords Don't Match");
                          } else {
                            confirm_password.setCustomValidity('');
                          }
                        }
                        password.onchange = validatePassword;
                        confirm_password.onkeyup = validatePassword;
                    </script>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>

