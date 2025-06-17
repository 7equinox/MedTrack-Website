<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$personnelID = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (!$personnelID || !in_array($action, ['archive', 'restore'])) {
    header("Location: personnel_management.php?error=Invalid action");
    exit();
}

$isArchived = ($action === 'archive') ? 1 : 0;
$verb = ($action === 'archive') ? 'Archived' : 'Restored';

$stmt = $conn->prepare("UPDATE personnel SET IsArchived = ? WHERE PersonnelID = ?");
$stmt->bind_param("is", $isArchived, $personnelID);

if ($stmt->execute()) {
    header("Location: personnel_management.php?success=" . $verb . " personnel " . urlencode($personnelID));
} else {
    header("Location: personnel_management.php?error=Database update failed");
}
$stmt->close();
?> 