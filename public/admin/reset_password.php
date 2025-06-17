<?php
require_once __DIR__ . '/../../config/database.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$show_form = false;

if (empty($token)) {
    $message = "Invalid or missing reset token.";
    $message_type = 'error';
} else {
    $stmt = $conn->prepare("SELECT AdminID, reset_token_expires FROM admins WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($admin = $result->fetch_assoc()) {
        $expiry_time = new DateTime($admin['reset_token_expires']);
        $now = new DateTime();

        if ($now > $expiry_time) {
            $message = "This password reset link has expired.";
            $message_type = 'error';
        } else {
            $show_form = true;
        }
    } else {
        $message = "Invalid reset token.";
        $message_type = 'error';
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($password) || $password !== $password_confirm) {
        $message = "Passwords do not match or are empty.";
        $message_type = 'error';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE admins SET Password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $hashedPassword, $token);
        
        if ($update->execute()) {
            $message = "Your password has been reset successfully! You can now log in.";
            $message_type = 'success';
            $show_form = false;
        } else {
            $message = "An error occurred. Please try again.";
            $message_type = 'error';
        }
        $update->close();
    }
}

$pageTitle = 'Reset Admin Password';
$body_class = 'page-doctor-login';
$base_path = '..';
require_once __DIR__ . '/../../templates/partials/header.php';
?>
<body class="<?= $body_class; ?>">
    <div class="container">
        <div class="left-panel"></div>
        <div class="right-panel">
            <div class="content">
                <h2>Set New Password</h2>

                <?php if ($message): ?>
                    <div class="message <?= $message_type === 'success' ? 'success' : 'error' ?>" style="text-align: left; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; background-color: <?= $message_type === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $message_type === 'success' ? '#155724' : '#721c24' ?>;">
                        <?= htmlspecialchars($message) ?>
                        <?php if($message_type === 'success'): ?>
                            <br><br><a href="../admin_login.php">Back to Login</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_form): ?>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirm New Password</label>
                            <input type="password" id="password_confirm" name="password_confirm" required>
                        </div>
                        <div class="button-container">
                            <button type="submit" class="btn-admin-forgot-password">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 