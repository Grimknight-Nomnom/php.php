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

// Fetch patient data
$sql = "SELECT id, firstname, middlename, lastname, birthdate, password FROM patients";
$result = $conn->query($sql);

$patients = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
} else {
    echo "0 results";
}

// Close connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($patients);
?>