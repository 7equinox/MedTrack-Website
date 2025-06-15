<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo "Missing Patient ID";
    exit;
}

$patientID = $_GET['id'];

// Archive the patient by setting IsArchived = TRUE
$query = "UPDATE patients SET IsArchived = TRUE WHERE PatientID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $patientID);

if ($stmt->execute()) {
    http_response_code(200);
    echo "Archived successfully";
} else {
    http_response_code(500);
    echo "Failed to archive";
}
$stmt->close();
?>
