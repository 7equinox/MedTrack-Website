<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['exists' => false, 'error' => 'No ID provided']);
    exit;
}

$patientID = trim($_GET['id']);

if (empty($patientID)) {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $conn->prepare("SELECT 1 FROM patients WHERE PatientID = ?");
$stmt->bind_param("s", $patientID);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode(['exists' => $result->num_rows > 0]);

$stmt->close();
?> 