<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$doctorID = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (!$doctorID || !in_array($action, ['archive', 'restore'])) {
    header("Location: doctor_management.php?error=Invalid action");
    exit();
}

$isArchived = ($action === 'archive') ? 1 : 0;
$verb = ($action === 'archive') ? 'Archived' : 'Restored';

$stmt = $conn->prepare("UPDATE doctor SET IsArchived = ? WHERE DoctorID = ?");
$stmt->bind_param("is", $isArchived, $doctorID);

if ($stmt->execute()) {
    header("Location: doctor_management.php?success=" . $verb . " doctor " . urlencode($doctorID));
} else {
    header("Location: doctor_management.php?error=Database update failed");
}
$stmt->close();
?> 