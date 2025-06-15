<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo "Missing Patient ID";
    exit;
}

$patientID = $_GET['id'];

// Restore the patient by setting IsArchived = FALSE
$query = "UPDATE patients SET IsArchived = FALSE WHERE PatientID = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo "Failed to prepare query.";
    exit;
}

$stmt->bind_param("s", $patientID);

if ($stmt->execute()) {
    http_response_code(200);
    echo "Restored successfully";
} else {
    http_response_code(500);
    echo "Failed to restore patient";
}

$stmt->close();
$conn->close();
?> 