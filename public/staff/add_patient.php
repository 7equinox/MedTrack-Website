<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$id = trim($_POST['PatientID'] ?? '');
$name = trim($_POST['PatientName'] ?? '');
$birthdate = $_POST['Birthdate'] ?? '';
$sex = $_POST['Sex'] ?? '';
$address = trim($_POST['HomeAddress'] ?? '');
$email = trim($_POST['Email'] ?? '');
$contact = trim($_POST['ContactNumber'] ?? '');
$room = trim($_POST['RoomNumber'] ?? '');

// Basic validation
if (empty($id) || empty($name) || empty($birthdate) || empty($sex)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
    exit;
}

// Server-side check for existing ID
$checkStmt = $conn->prepare("SELECT 1 FROM patients WHERE PatientID = ?");
$checkStmt->bind_param("s", $id);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    $checkStmt->close();
    echo json_encode(['status' => 'error', 'message' => "Patient ID '$id' already exists."]);
    exit;
}
$checkStmt->close();

$stmt = $conn->prepare("
    INSERT INTO patients (
        PatientID, PatientName, Birthdate, Sex, HomeAddress, Email, ContactNumber, RoomNumber
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssssss", $id, $name, $birthdate, $sex, $address, $email, $contact, $room);

if ($stmt->execute()) {
    $newPatientData = [
        'PatientID' => $id,
        'PatientName' => $name,
        'RoomNumber' => $room
    ];
    echo json_encode(['status' => 'success', 'message' => 'Patient successfully added.', 'patient' => $newPatientData]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
?>
