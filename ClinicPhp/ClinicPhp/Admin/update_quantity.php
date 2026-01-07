<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get the data from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$change = $data['change'];

// First, get the current quantity to ensure we don't go negative
$stmt = $conn->prepare("SELECT quantity FROM medicines WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$currentQuantity = $row['quantity'];
$stmt->close();

$newQuantity = $currentQuantity + $change;

// Don't allow negative quantities
if ($newQuantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity cannot be negative']);
    exit;
}

// Update the quantity in the database
$stmt = $conn->prepare("UPDATE medicines SET quantity = quantity + ? WHERE id = ?");
$stmt->bind_param("ii", $change, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating quantity: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>