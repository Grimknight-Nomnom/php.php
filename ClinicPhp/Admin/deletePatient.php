<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "clinicphp";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get patient ID from the request
$id = $_GET["id"];

// Delete patient from the `users` table
$sql = "DELETE FROM users WHERE id = $id"; // Adjusted table name to `users`
if ($conn->query($sql) === TRUE) {
    echo json_encode(array("success" => true));
} else {
    echo json_encode(array("error" => "Error deleting patient: " . $conn->error));
}

$conn->close();
?>