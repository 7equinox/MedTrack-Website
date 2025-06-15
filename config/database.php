<?php
$host = 'localhost';         // or your DB host
$username = 'root';          // your MySQL username
$password = '';              // your MySQL password
$database = 'medtrackdb';      // your database name

// Create a new connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
