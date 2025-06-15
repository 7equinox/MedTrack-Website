<?php
session_start();
if (!isset($_SESSION['StaffID']) || !isset($_SESSION['StaffName'])) {
    header("Location: ../../staff/staff_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$staffID = $_SESSION['StaffID'];
$page_title = 'Staff Profile';
$body_class = 'page-staff-profile';
$base_path = '../..';
$activePage = 'profile';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $uploadDir = $base_path . '/uploads/';
    $fileName = 'staff_' . $staffID . '.' . strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $targetFile = $uploadDir . $fileName;

    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $relativePath = 'uploads/' . $fileName;
            $updateStmt = $conn->prepare("UPDATE staff SET ProfilePicture = ? WHERE StaffID = ?");
            $updateStmt->bind_param("ss", $relativePath, $staffID);
            $updateStmt->execute();
        }
    }
}

// Handle profile info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['StaffName'] ?? '';
    $email = $_POST['Email'] ?? '';
    $contact = $_POST['ContactNumber'] ?? '';

    $updateInfoStmt = $conn->prepare("UPDATE staff SET StaffName = ?, Email = ?, ContactNumber = ? WHERE StaffID = ?");
    $updateInfoStmt->bind_param("ssss", $name, $email, $contact, $staffID);
    $updateInfoStmt->execute();

    $_SESSION['StaffName'] = $name; // Update session with new name
    header("Location: profile.php?update_success=1");
    exit();
}

// Fetch staff info
$query = "SELECT * FROM staff WHERE StaffID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $staffID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $staff = $result->fetch_assoc();
} else {
    echo "<p>Staff not found.</p>";
    exit();
}

// Profile picture path
$profilePicPath = !empty($staff['ProfilePicture']) ? $base_path . '/' . $staff['ProfilePicture'] : $base_path . '/public/images/default-prof-staff.png';

require_once __DIR__ . '/../../templates/partials/staff_header.php';
?>

<main>
    <h1 class="profile-title">Profile</h1>

    <?php if (isset($_GET['update_success'])): ?>
        <div class="alert success" id="success-panel">
            Profile has been successfully updated.
        </div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert error" style="background-color: #f8d7da; color: #721c24; padding: 1rem; border: 1px solid #f5c6cb; border-radius: 8px;">
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="profile-layout">
        <div class="profile-sidebar">
            <img src="<?= htmlspecialchars($profilePicPath) ?>" alt="Profile Picture" class="profile-pic">

            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <label for="profile_image" class="change-profile-link">Change Profile</label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="this.form.submit()" style="display: none;">
            </form>
        </div>

        <form method="POST" class="profile-form-card">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-grid">
                <div class="input-group">
                    <label for="staff-id">Staff ID</label>
                    <input type="text" id="staff-id" value="<?= htmlspecialchars($staff['StaffID']) ?>" readonly>
                </div>

                <div class="input-group col-span-2">
                    <label for="name">Name</label>
                    <div class="input-with-icon">
                        <input type="text" id="name" name="StaffName" value="<?= htmlspecialchars($staff['StaffName']) ?>" readonly>
                        <i class="fas fa-edit edit-icon" data-target="name"></i>
                    </div>
                </div>

                <div class="input-group col-span-2">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <input type="email" id="email" name="Email" value="<?= htmlspecialchars($staff['Email']) ?>" readonly>
                        <i class="fas fa-edit edit-icon" data-target="email"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="contact">Contact No.</label>
                    <div class="input-with-icon">
                        <input type="text" id="contact" name="ContactNumber" value="<?= htmlspecialchars($staff['ContactNumber']) ?>" readonly>
                        <i class="fas fa-edit edit-icon" data-target="contact"></i>
                    </div>
                </div>

                <div class="form-actions col-span-3">
                    <button type="button" id="cancel-btn" class="btn btn-cancel" disabled>Cancel</button>
                    <button type="submit" id="save-btn" class="btn btn-save">Save</button>
                </div>
            </div>
        </form>
    </div>
</main>

<?php 
require_once __DIR__ . '/../../templates/partials/staff_side_menu.php';
require_once __DIR__ . '/../../templates/partials/staff_footer.php'; 
?>

<script>
document.querySelectorAll('.edit-icon').forEach(icon => {
    icon.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (input) {
            input.removeAttribute('readonly');
            input.focus();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const successPanel = document.getElementById('success-panel');
    if (successPanel) {
        setTimeout(() => {
            successPanel.style.transition = 'opacity 0.5s ease';
            successPanel.style.opacity = '0';
            setTimeout(() => successPanel.style.display = 'none', 500);
        }, 3000);
    }

    const form = document.querySelector('.profile-form-card');
    const inputs = form.querySelectorAll('input[name]');
    const cancelBtn = document.getElementById('cancel-btn');

    // Store original values
    inputs.forEach(input => {
        input.dataset.originalValue = input.value;
    });

    function checkForChanges() {
        let hasChanged = false;
        inputs.forEach(input => {
            if (input.value !== input.dataset.originalValue) {
                hasChanged = true;
            }
        });
        cancelBtn.disabled = !hasChanged;
    }

    inputs.forEach(input => {
        input.addEventListener('input', checkForChanges);
    });

    cancelBtn.addEventListener('click', function() {
        inputs.forEach(input => {
            input.value = input.dataset.originalValue;
        });
        this.disabled = true;
    });

    form.addEventListener('submit', function() {
        // After submit, disable cancel button
        setTimeout(() => {
            cancelBtn.disabled = true;
            // Update original values to new saved values
            inputs.forEach(input => {
                input.dataset.originalValue = input.value;
            });
        }, 100);
    });
});
</script>
