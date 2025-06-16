<?php
session_start();
if (!isset($_SESSION['PersonnelID']) || !isset($_SESSION['PersonnelName'])) {
    header("Location: ../../personnel/personnel_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$PersonnelID = $_SESSION['PersonnelID'];
$page_title = 'Personnel Profile';
$body_class = 'page-personnel-profile';
$base_path = '../..';
$activePage = 'profile';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $uploadDir = '../uploads/';
    $fileName = 'personnel_' . $PersonnelID . '.' . strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $targetFile = $uploadDir . $fileName;

    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowedTypes)) {
        // Get current profile picture
        $query = "SELECT ProfilePicture FROM personnel WHERE PersonnelID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $PersonnelID);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentPicture = $result->fetch_assoc()['ProfilePicture'];
        $stmt->close();

        // Delete old profile picture if it exists and is not a default picture
        if ($currentPicture && $currentPicture !== 'default-prof-personnel.png') {
            $oldPicturePath = $uploadDir . $currentPicture;
            if (file_exists($oldPicturePath)) {
                unlink($oldPicturePath);
            }
        }

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $relativePath = 'uploads/' . $fileName;
            $updateStmt = $conn->prepare("UPDATE personnel SET ProfilePicture = ? WHERE PersonnelID = ?");
            $updateStmt->bind_param("ss", $relativePath, $PersonnelID);
            $updateStmt->execute();
            $updateStmt->close();
            
            header("Location: profile.php?update_success=1");
            exit();
        }
    }
}

// Handle profile info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['PersonnelName'] ?? '';
    $email = $_POST['Email'] ?? '';
    $contact = $_POST['ContactNumber'] ?? '';

    $updateInfoStmt = $conn->prepare("UPDATE personnel SET PersonnelName = ?, Email = ?, ContactNumber = ? WHERE PersonnelID = ?");
    $updateInfoStmt->bind_param("ssss", $name, $email, $contact, $PersonnelID);
    $updateInfoStmt->execute();

    $_SESSION['PersonnelName'] = $name; // Update session with new name
    header("Location: profile.php?update_success=1");
    exit();
}

// Fetch personnel info
$query = "SELECT * FROM personnel WHERE PersonnelID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $PersonnelID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $personnel = $result->fetch_assoc();
} else {
    echo "<p>Medical Personnel not found.</p>";
    exit();
}

// Profile picture path
$profilePicPath = !empty($personnel['ProfilePicture']) ? $base_path . '/' . $personnel['ProfilePicture'] : $base_path . '/public/images/default-prof-personnel.png';

require_once __DIR__ . '/../../templates/partials/personnel_header.php';
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
                    <label for="personnel-id">Medical Personnel ID</label>
                    <input type="text" id="personnel-id" value="<?= htmlspecialchars($personnel['PersonnelID']) ?>" readonly>
                </div>

                <div class="input-group col-span-2">
                    <label for="name">Name</label>
                    <div class="input-with-icon">
                        <input type="text" id="name" name="PersonnelName" value="<?= htmlspecialchars($personnel['PersonnelName']) ?>" readonly>
                        <i class="fas fa-edit edit-icon" data-target="name"></i>
                    </div>
                </div>

                <div class="input-group col-span-2">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <input type="email" id="email" name="Email" value="<?= htmlspecialchars($personnel['Email']) ?>" readonly>
                        <i class="fas fa-edit edit-icon" data-target="email"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="contact">Contact No.</label>
                    <div class="input-with-icon">
                        <input type="text" id="contact" name="ContactNumber" value="<?= htmlspecialchars($personnel['ContactNumber']) ?>" readonly>
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
require_once __DIR__ . '/../../templates/partials/personnel_side_menu.php';
require_once __DIR__ . '/../../templates/partials/personnel_footer.php'; 
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
