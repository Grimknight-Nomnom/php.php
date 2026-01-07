<?php
// Set headers first to ensure proper content type
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $date = isset($_GET['date']) ? $_GET['date'] : null;
    $events = [];

    if ($date) {
        // For specific date view
        $stmt = $conn->prepare("
            SELECT id, patient_name, appointment_date, appointment_number, is_completed 
            FROM schedules 
            WHERE DATE(appointment_date) = ?
            ORDER BY appointment_number
        ");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'appointment_number' => $row['appointment_number'],
                'patient_name' => $row['patient_name'],
                'is_completed' => (bool)$row['is_completed']
            ];
        }
    } else {
        // For full calendar view
        $result = $conn->query("
            SELECT 
                DATE(appointment_date) as date,
                GROUP_CONCAT(appointment_number ORDER BY appointment_number SEPARATOR ',') as numbers,
                GROUP_CONCAT(is_completed ORDER BY appointment_number SEPARATOR ',') as completions
            FROM schedules
            GROUP BY DATE(appointment_date)
        ");

        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'title' => 'Appointments',
                'start' => $row['date'],
                'numbers' => explode(',', $row['numbers']),
                'completions' => explode(',', $row['completions']),
                'display' => 'background'
            ];
        }
    }

    // Close connection
    $conn->close();

    // Output JSON response
    echo json_encode([
        'success' => true,
        'events' => $events
    ]);
    exit;

} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in get_schedules.php: " . $e->getMessage());
    
    // Return JSON error response
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching appointments',
        'debug' => $e->getMessage() // Only include in development
    ]);
    exit;
}
?>