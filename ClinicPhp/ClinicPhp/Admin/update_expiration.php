<?php
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "clinicphp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$expiration_date = $_POST['expiration_date'];

// Update expiration date in the database
$sql = "UPDATE medicines SET expiration_date = '$expiration_date' WHERE id = $id";
if ($conn->query($sql)) {
    echo "Expiration date updated successfully";
} else {
    echo "Error updating expiration date: " . $conn->error;
}

$conn->close();
?>