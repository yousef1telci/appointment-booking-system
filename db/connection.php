<?php
// Database connection parameters
$hostname = "localhost";
$username = "root";
$password = ""; // Change this to your MySQL password
$database = "appointment_system";

// Create connection
$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
