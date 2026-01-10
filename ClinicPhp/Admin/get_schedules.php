<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if a specific date was requested
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    
    if ($date) {
        // Return appointments for a specific date
        $stmt = $conn->prepare("SELECT id, patient_name, appointment_date, appointment_number, user_id, is_completed FROM schedules WHERE appointment_date = ?");
        $stmt->bind_param("s", $date);
    } else {
        // Return all appointments
        $stmt = $conn->prepare("SELECT id, patient_name, appointment_date, appointment_number, user_id, is_completed FROM schedules");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['id'],
            'title' => $row['patient_name'] . ' (#' . $row['appointment_number'] . ')',
            'start' => $row['appointment_date'],
            'appointment_number' => $row['appointment_number'],
            'patient_name' => $row['patient_name'],
            'user_id' => $row['user_id'],
            'is_completed' => (bool)$row['is_completed']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'events' => $events
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>