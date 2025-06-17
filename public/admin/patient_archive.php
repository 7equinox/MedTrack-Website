<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$patientID = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (!$patientID || !in_array($action, ['archive', 'restore'])) {
    header("Location: patient_management.php?error=Invalid action");
    exit();
}

$isArchived = ($action === 'archive') ? 1 : 0;
$verb = ($action === 'archive') ? 'Archived' : 'Restored';

$stmt = $conn->prepare("UPDATE patients SET IsArchived = ? WHERE PatientID = ?");
$stmt->bind_param("is", $isArchived, $patientID);

if ($stmt->execute()) {
    header("Location: patient_management.php?success=" . $verb . " patient " . urlencode($patientID));
} else {
    header("Location: patient_management.php?error=Database update failed");
}
$stmt->close();
?> 