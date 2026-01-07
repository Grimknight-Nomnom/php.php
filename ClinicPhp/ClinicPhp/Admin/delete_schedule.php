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

try {
    // Get the appointment ID to delete
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('No appointment ID provided');
    }

    // First get the appointment number to update others
    $getQuery = "SELECT appointment_number FROM schedules WHERE id = ?";
    $stmt = $conn->prepare($getQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Appointment not found');
    }
    
    $row = $result->fetch_assoc();
    $deletedNumber = $row['appointment_number'];
    $stmt->close();

    // Delete the appointment
    $deleteQuery = "DELETE FROM schedules WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Failed to delete appointment');
    }
    $stmt->close();

    // Update appointment numbers for remaining appointments
    $updateQuery = "UPDATE schedules 
                    SET appointment_number = appointment_number - 1 
                    WHERE appointment_number > ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $deletedNumber);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>