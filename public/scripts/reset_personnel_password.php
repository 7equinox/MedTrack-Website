<?php
// A utility script to securely reset a medical personnel member's password.
// USAGE:
// 1. Navigate to this script in your browser (e.g., your-site.com/public/scripts/reset_personnel_password.php).
// 2. Enter the Personnel ID (e.g., MD-0001) and the new password.
// 3. Click "Reset Password".
// 4. IMPORTANT: Delete this file from your server immediately after use.

require_once '../../config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personnelID = trim($_POST['personnel_id']);
    $newPassword = $_POST['new_password'];

    if (empty($personnelID) || empty($newPassword)) {
        $message = "Personnel ID and new password cannot be empty.";
        $message_type = 'error';
    } else {
        // Hash the new password securely
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE personnel SET Password = ? WHERE PersonneIID = ?");
        $stmt->bind_param("ss", $hashedPassword, $personnelID);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "Password for Personnel ID '" . htmlspecialchars($personnelID) . "' has been successfully reset.";
                $message_type = 'success';
            } else {
                $message = "No medical personnel found with Personnel ID '" . htmlspecialchars($personnelID) . "'. No changes were made.";
                $message_type = 'error';
            }
        } else {
            $message = "Error updating password: " . htmlspecialchars($stmt->error);
            $message_type = 'error';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Personnel Password Reset Utility</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f5f7fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { max-width: 450px; width: 100%; margin: auto; background: #fff; padding: 2.5rem; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.07); }
        h1 { color: #333; text-align: center; margin-top: 0; margin-bottom: 1rem; font-weight: 600; }
        .warning { background: #fff3cd; color: #856404; padding: 1rem; border: 1px solid #ffeeba; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1rem; }
        input:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 2px rgba(0,123,255,0.2); }
        button { width: 100%; padding: 14px; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1.1rem; font-weight: 600; transition: background-color 0.2s; }
        button:hover { background: #c82333; }
        .message { padding: 1rem; border-radius: 8px; margin-top: 1.5rem; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Medical Personnel Password Reset</h1>
        <div class="warning">
            <strong>Warning:</strong> This is a powerful tool. Please delete this file from your server immediately after you are finished using it.
        </div>

        <?php if ($message): ?>
            <div class="message <?= htmlspecialchars($message_type); ?>">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="personnel_id">Personnel ID</label>
                <input type="text" id="personnel_id" name="personnel_id" required placeholder="e.g., MD-0001">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>

