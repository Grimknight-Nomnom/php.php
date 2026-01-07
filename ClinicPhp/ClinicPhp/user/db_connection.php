<?php
$servername = "localhost";
$username = "root";       // Change to your actual username
$password = "";           // Change to your actual password
$dbname = "clinicphp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Return JSON instead of dying with text
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]));
}