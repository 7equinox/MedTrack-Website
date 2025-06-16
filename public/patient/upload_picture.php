<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['PatientID'])) {
    header("Location: ../patient_login.php");
    exit();
}

$patientID = $_SESSION['PatientID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $uploadDir = '../uploads/';
    $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
    $fileName = basename($_FILES['profile_picture']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = 'patient_' . $patientID . '.' . strtolower($fileExtension);
    $destPath = $uploadDir . $newFileName;

    // Allow only image types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
        die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }

    // Get current profile picture
    $query = "SELECT ProfilePicture FROM patients WHERE PatientID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $patientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentPicture = $result->fetch_assoc()['ProfilePicture'];
    $stmt->close();

    // Delete old profile picture if it exists and is not a default picture
    if ($currentPicture && $currentPicture !== 'default-prof-patient.png') {
        $oldPicturePath = $uploadDir . $currentPicture;
        if (file_exists($oldPicturePath)) {
            unlink($oldPicturePath);
        }
    }

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $query = "UPDATE patients SET ProfilePicture = ? WHERE PatientID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $newFileName, $patientID);
        $stmt->execute();
        $stmt->close();

        header("Location: profile.php?upload=success");
        exit();
    } else {
        die("Error uploading the file.");
    }
} else {
    die("No file uploaded.");
}
