<?php
session_start();
$pageTitle = 'Patient Profile - MedTrack';
$activePage = 'profile';
$base_path = '../';
require_once '../../config/database.php';
require_once '../../templates/partials/header.php';

if (!isset($_SESSION['PatientID'])) {
    header("Location: $base_path/index.php");
    exit();
}

$patientID = $_SESSION['PatientID'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $uploadDir = '../uploads/';
    $fileName = 'profile_' . $patientID . '.' . strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $targetFile = $uploadDir . $fileName;

    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $relativePath = 'uploads/' . $fileName;
            $updateStmt = $conn->prepare("UPDATE patients SET ProfilePicture = ? WHERE PatientID = ?");
            $updateStmt->bind_param("ss", $relativePath, $patientID);
            $updateStmt->execute();
        }
    }
}

// Fetch patient info
$query = "SELECT * FROM patients WHERE PatientID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $patientID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $patient = $result->fetch_assoc();
} else {
    echo "<p>Patient not found.</p>";
    exit();
}

// Age calculation
$age = '';
if (!empty($patient['Birthdate'])) {
    $dob = new DateTime($patient['Birthdate']);
    $now = new DateTime();
    $age = $dob->diff($now)->y;
}

// Profile picture path
$profilePicPath = !empty($patient['ProfilePicture']) ? $base_path . $patient['ProfilePicture'] : $base_path . 'images/default-prof-patient.png';
?>

<body class="page-patient-area page-patient-profile">
<?php require_once '../../templates/partials/patient_side_menu.php'; ?>
<?php require_once '../../templates/partials/patient_header.php'; ?>

<main>
  <h1>Profile</h1>
  <div class="profile-container">
    <div class="profile-picture-section">
      <div class="profile-image-wrapper">
        <img src="<?= htmlspecialchars($profilePicPath) ?>" alt="Patient Profile Image" class="profile-image">
      </div>

      <!-- Upload Form -->
      <form method="POST" enctype="multipart/form-data" class="upload-form" style="margin-top: 10px;">
        <label for="profile_image" class="change-profile-link" style="display: inline-block; padding: 8px 14px; background-color: #3490dc; color: white; border-radius: 4px; cursor: pointer; font-size: 14px;">
          Upload New Profile
        </label>
        <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="this.form.submit()" style="display: none;">
      </form>
    </div>

    <div class="profile-details-section">
      <div class="form-card">
        <div class="form-grid">
          <div class="form-group g-col-2">
            <label for="patient-id">Patient ID</label>
            <div class="readonly-input" id="patient-id"><?= htmlspecialchars($patient['PatientID']) ?></div>
          </div>
          <div class="form-group g-col-2">
            <label for="birthdate">Birthdate</label>
            <div class="readonly-input" id="birthdate"><?= htmlspecialchars($patient['Birthdate']) ?></div>
          </div>
          <div class="form-group g-col-2">
            <label for="age">Age</label>
            <div class="readonly-input" id="age"><?= htmlspecialchars($age) ?></div>
          </div>
          <div class="form-group g-col-4">
            <label for="name">Name</label>
            <div class="readonly-input" id="name"><?= htmlspecialchars($patient['PatientName']) ?></div>
          </div>
          <div class="form-group g-col-2">
            <label for="sex">Sex</label>
            <div class="readonly-input" id="sex"><?= htmlspecialchars(ucfirst($patient['Sex'])) ?></div>
          </div>
          <div class="form-group g-col-6">
            <label for="home-address">Home Address</label>
            <div class="readonly-input" id="home-address"><?= htmlspecialchars($patient['HomeAddress']) ?></div>
          </div>
          <div class="form-group g-col-6">
            <label for="email">Email</label>
            <div class="readonly-input" id="email"><?= htmlspecialchars($patient['Email']) ?></div>
          </div>
          <div class="form-group g-col-3">
            <label for="contact-no">Contact No.</label>
            <div class="readonly-input" id="contact-no"><?= htmlspecialchars($patient['ContactNumber']) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once '../../templates/partials/footer.php'; ?>
