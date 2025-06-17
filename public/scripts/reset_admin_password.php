<?php
require_once __DIR__ . '/../../config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = trim($_POST['admin_id']);
    $newPassword = $_POST['new_password'];
    $isHashed = isset($_POST['is_hashed']);

    if (empty($adminId) || empty($newPassword)) {
        $message = "Admin ID and New Password are required.";
        $message_type = 'error';
    } else {
        // Check if admin exists
        $check = $conn->prepare("SELECT 1 FROM admins WHERE AdminID = ?");
        $check->bind_param("s", $adminId);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            $message = "Admin with ID '$adminId' not found.";
            $message_type = 'error';
        } else {
            $passwordToSave = $isHashed ? $newPassword : password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE admins SET Password = ? WHERE AdminID = ?");
            $stmt->bind_param("ss", $passwordToSave, $adminId);

            if ($stmt->execute()) {
                $message = "Password for Admin ID '$adminId' has been updated successfully.";
                $message_type = 'success';
            } else {
                $message = "Failed to update password: " . htmlspecialchars($stmt->error);
                $message_type = 'error';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset Utility</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f4f7f6; color: #333; line-height: 1.6; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .checkbox-group { display: flex; align-items: center; }
        .checkbox-group input { margin-right: 10px; }
        .btn { display: block; width: 100%; background-color: #3498db; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; font-weight: 600; text-align: center; }
        .btn:hover { background-color: #2980b9; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .note { font-size: 0.9rem; color: #666; background: #ecf0f1; padding: 10px; border-radius: 4px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Password Reset Utility</h1>
        <?php if ($message): ?>
            <div class="message <?= $message_type; ?>"><?= $message; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="admin_id">Admin ID</label>
                <input type="text" id="admin_id" name="admin_id" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password / Password Hash</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_hashed" name="is_hashed" value="1">
                <label for="is_hashed">The value above is a pre-computed hash</label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Update Password</button>
            </div>
            <div class="note">
                <strong>Note:</strong> If the checkbox is ticked, the value will be saved directly. Otherwise, it will be securely hashed before being saved.
            </div>
        </form>
    </div>
</body>
</html> 