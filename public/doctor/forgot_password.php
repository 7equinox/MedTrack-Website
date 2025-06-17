<?php
$pageTitle = 'Forgot Password - MedTrack';
$base_path = '../';
require_once '../../templates/partials/header.php';
?>

<body class="page-doctor-login">
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
                <p class="instruction" style="margin-bottom: 1.5rem;">Enter your Doctor ID to receive a password reset code.</p>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="message error" style="margin-bottom: 1rem;"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <form action="recover_password.php" method="GET">
                    <div class="form-group" style="text-align: left;">
                        <label for="doctor_id">Doctor ID</label>
                        <input type="text" id="doctor_id" name="id" required placeholder="e.g., DT-2025">
                    </div>
                    <div class="button-container">
                        <button type="submit" class="btn">Send Recovery Code</button>
                    </div>
                </form>
                <div class="options" style="text-align: center; margin-top: 1.5rem;">
                    <a href="../doctor_login.php">← Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html> 