<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error_message = '';

if (isset($_SESSION['AdminID'])) {
    header("Location: admin/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = trim($_POST['admin_id']);
    $password = $_POST['password'];

    if (empty($adminId) || empty($password)) {
        $error_message = "Admin ID and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT AdminID, AdminName, Password FROM admins WHERE AdminID = ?");
        $stmt->bind_param("s", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['Password'])) {
                $_SESSION['AdminID'] = $user['AdminID'];
                $_SESSION['AdminName'] = $user['AdminName'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error_message = "Invalid Admin ID or password.";
            }
        } else {
            $error_message = "Invalid Admin ID or password.";
        }
        $stmt->close();
    }
}

$pageTitle = 'Admin Login - MedTrack';
$body_class = 'page-personnel-login'; // Can reuse this class for styling
$base_path = './'; // Set the correct base path
require_once __DIR__ . '/../templates/partials/header.php';
?>

<body class="<?= $body_class; ?>">
    <div class="container">
        <div class="left-panel" style="background-image: url('images/medicine.jpg');"></div>
        <div class="right-panel">
            <div class="content">
                <div class="logo">
                    <img src="images/logo.png" alt="MedTrack Logo">
                </div>
                <h2 style="text-align: center;">Administrator Login</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert error" style="margin-bottom: 1.5rem; background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="admin_id">Admin ID</label>
                        <input type="text" id="admin_id" name="admin_id" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-icon"></span>
                        </div>
                    </div>
                    <div class="options">
                        <a href="admin/forgot_password.php">Forgot Password?</a>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="btn">Login</button>
                    </div>

                    <p class="terms">
          By using this service, you understood and agree to MedTrack's
          <a href="terms.php">Terms of Use</a>.
        </p>
                </form>
            </div>
        </div>
    </div>
    <script src="js/auth.js"></script>
</body>
</html> 