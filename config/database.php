<?php
$host = 'localhost'; // DB host
$username = 'root'; // MySQL username
$password = ''; // MySQL password
$database = 'medtrackdb'; // database name

// Create a new connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
