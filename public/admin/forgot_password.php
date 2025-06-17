<?php
require_once __DIR__ . '/../../config/database.php';
// The following two files would be from a library like PHPMailer
// require __DIR__ . '/../../vendor/PHPMailer/src/Exception.php';
// require __DIR__ . '/../../vendor/PHPMailer/src/PHPMailer.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = trim($_POST['admin_id']);

    if (empty($adminId)) {
        $message = "Admin ID is required.";
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("SELECT Email FROM admins WHERE AdminID = ?");
        $stmt->bind_param("s", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($admin = $result->fetch_assoc()) {
            $email = $admin['Email'];
            $token = bin2hex(random_bytes(50));
            $expires = new DateTime('NOW');
            $expires->add(new DateInterval('PT1H')); // 1 hour expiry
            $expiresStr = $expires->format('Y-m-d H:i:s');

            $update = $conn->prepare("UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE AdminID = ?");
            $update->bind_param("sss", $token, $expiresStr, $adminId);
            $update->execute();

            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";
            
            // --- Mock Email Sending ---
            // In a real application, you would use a library like PHPMailer to send an email.
            // For this example, we will just display the link.
            $message = "A password reset link has been generated for the email associated with this account ($email). <br><br> Link (for demonstration): <br><a href='$reset_link'>$reset_link</a>";
            $message_type = 'success';
            // -------------------------

        } else {
            // Show a generic message to prevent user enumeration
            $message = "If an account with that Admin ID exists, a reset link will be sent to the associated email.";
            $message_type = 'success';
        }
        $stmt->close();
    }
}

$pageTitle = 'Forgot Password - MedTrack';
$base_path = '../';
require_once '../../templates/partials/header.php';
?>

<body class="page-admin-login">
    <div class="container">
        <div class="left-panel">
            <!-- Background image is set in CSS -->
        </div>
        <div class="right-panel">
            <div class="content" style="max-width: 400px; text-align: center;">
                <div class="logo">
                    <a href="../index.php"><img src="<?= $base_path ?>images/logo.png" alt="MedTrack Logo"></a>
                </div>
                <h2>Forgot Password</h2>
                <p class="instruction" style="margin-bottom: 1.5rem;">Enter your Admin ID to receive a password reset code.</p>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="message error" style="margin-bottom: 1rem;"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <form action="recover_password.php" method="GET">
                    <div class="form-group" style="text-align: left;">
                        <label for="admin_id">Admin ID</label>
                        <input type="text" id="admin_id" name="id" required placeholder="e.g., AD-001">
                    </div>
                    <div class="button-container">
                        <button type="submit" class="btn-admin-forgot-password">Send Recovery Code</button>
                    </div>
                </form>
                <div class="options" style="text-align: center; margin-top: 1.5rem;">
                    <a href="../admin_login.php">← Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html> 