<?php
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['PatientID']);
    $name = trim($_POST['PatientName']);
    $birthdate = $_POST['Birthdate'];
    $sex = $_POST['Sex'];
    $address = trim($_POST['HomeAddress']);
    $email = trim($_POST['Email']);
    $contact = trim($_POST['ContactNumber']);
    $room = trim($_POST['RoomNumber']);

    // Basic validation
    if (empty($id) || empty($name) || empty($birthdate) || empty($sex)) {
        echo "<p style='color:red;'>Please fill out all required fields.</p>";
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO patients (
            PatientID, PatientName, Birthdate, Sex, HomeAddress, Email, ContactNumber, RoomNumber
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssssss", $id, $name, $birthdate, $sex, $address, $email, $contact, $room);

    if ($stmt->execute()) {
        header("Location: patient_list.php");
        exit;
    } else {
        echo "<p style='color:red;'>Error: " . htmlspecialchars($stmt->error) . "</p>";
    }
}
?>
