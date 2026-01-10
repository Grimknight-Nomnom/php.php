<?php
// update_schedule.php
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
$patient_name = $_POST['patient_name'];

// Update schedule
$sql = "UPDATE schedule SET patient_name='$patient_name' WHERE id=$id";
if ($conn->query($sql)) {
    echo "Schedule updated successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>