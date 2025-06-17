<?php
$pageTitle = 'Enter Recovery Code - MedTrack';
$base_path = '../';
require_once '../../templates/partials/header.php';
require_once '../../config/database.php';

$doctor_id = $_GET['id'] ?? '';

if (empty($doctor_id)) {
    header("Location: forgot_password.php?error=No ID provided.");
    exit();
}

// Check if doctor ID exists
$stmt = $conn->prepare("SELECT Email FROM doctor WHERE DoctorID = ?");
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: forgot_password.php?error=Doctor ID not found.");
    exit();
}
$doctor = $result->fetch_assoc();
$email = $doctor['Email'];
// Mask the email for display, e.g., show first 3 and last 4 chars
$email_parts = explode('@', $email);
$masked_email = substr($email_parts[0], 0, 3) . '...' . '@' . $email_parts[1];

?>

<body class="page-doctor-login">
    <div class="container">
        <div class="left-panel"></div>
        <div class="right-panel">
            <div class="content" style="max-width: 450px; text-align: center;">
                <div class="logo">
                    <a href="../index.php"><img src="<?= $base_path ?>images/logo.png" alt="MedTrack Logo"></a>
                </div>
                <h2>Enter Recovery Code</h2>
                <p class="instruction" style="font-size: 0.95rem;">
                    For demonstration purposes, a real email has not been sent to <strong><?= htmlspecialchars($masked_email) ?></strong>. 
                    <br><br><strong>You may enter any code below to proceed.</strong>
                </p>

                <form action="../scripts/reset_doctor_password.php" method="GET">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($doctor_id) ?>">
                    <div class="form-group" style="text-align: left;">
                        <label for="recovery_code">Recovery Code</label>
                        <input type="text" id="recovery_code" name="code" required placeholder="Enter any code">
                    </div>
                    <div class="button-container">
                        <button type="submit" class="btn">Reset Password</button>
                    </div>
                </form>

                <div class="options" style="text-align: center; margin-top: 1.5rem;">
                    <a href="forgot_password.php">← Use a different ID</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 