<?php
// add_schedule.php
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

$patient_name = $_POST['patient_name'];
$appointment_date = $_POST['appointment_date'];

// Insert new schedule
$sql = "INSERT INTO schedule (patient_name, appointment_date) VALUES ('$patient_name', '$appointment_date')";
if ($conn->query($sql)) {
    echo "Schedule saved successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>