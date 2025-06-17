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

$pageTitle = 'Admin Forgot Password';
$body_class = 'page-personnel-login';
$base_path = '..';
require_once __DIR__ . '/../../templates/partials/header.php';
?>

<body class="<?= $body_class; ?>">
    <div class="container">
        <div class="left-panel"></div>
        <div class="right-panel">
            <a href="../admin_login.php" class="back-button">&larr; Back to Login</a>
            <div class="content">
                <h2>Reset Admin Password</h2>
                <p class="instruction">Enter your Admin ID. A reset link will be sent to the email address associated with your account.</p>
                
                <?php if ($message): ?>
                    <div class="message <?= $message_type === 'success' ? 'success' : 'error' ?>" style="text-align: left; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; background-color: <?= $message_type === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $message_type === 'success' ? '#155724' : '#721c24' ?>;">
                        <?= $message; // Unescaped to allow the link to be clickable ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="admin_id">Admin ID</label>
                        <input type="text" id="admin_id" name="admin_id" required>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="btn">Send Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 