<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([]));
}

// Check if a specific date is requested
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $query = "SELECT id, patient_name, appointment_number 
              FROM schedules 
              WHERE appointment_date = ? 
              ORDER BY appointment_number";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode($appointments);
    $stmt->close();
    $conn->close();
    exit();
}

// Original full calendar data
$query = "SELECT id, CONCAT(patient_name, ' (#', appointment_number, ')') as title, 
          appointment_date as start FROM schedules";
$result = $conn->query($query);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

$conn->close();

echo json_encode($events);
?>