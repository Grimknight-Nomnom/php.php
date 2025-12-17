<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get patient ID from request
$patientId = $_GET['id'] ?? 0;

// Query to get patient data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patientId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    echo json_encode(['success' => true, 'patient' => $patient]);
} else {
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
}

$conn->close();
?>