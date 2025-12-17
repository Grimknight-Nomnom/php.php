<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

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

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception("Invalid input data");
    }

    $patientName = $data['patientName'] ?? '';
    $appointmentDate = $data['appointmentDate'] ?? '';
    $userId = $data['userId'] ?? 0;

    // Validate input
    if (empty($patientName) || empty($appointmentDate) || empty($userId)) {
        throw new Exception("All fields are required");
    }

    // Check if date is valid
    $dateObj = DateTime::createFromFormat('Y-m-d', $appointmentDate);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $appointmentDate) {
        throw new Exception("Invalid date format");
    }

    // Check if date is today or in the future
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Reset time to midnight for accurate comparison
    
    // Convert to Y-m-d format for comparison
    $appointmentDateObj = new DateTime($appointmentDate);
    $appointmentDateObj->setTime(0, 0, 0);
    
    if ($appointmentDateObj < $today) {
        throw new Exception("Cannot schedule appointments for past dates");
    }

    // Check if user already has an appointment on this date
    $checkSql = "SELECT COUNT(*) as existing_count FROM schedules 
                WHERE user_id = ? AND appointment_date = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $userId, $appointmentDate);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkRow = $checkResult->fetch_assoc();
        if ($checkRow['existing_count'] > 0) {
            throw new Exception("You already have an appointment scheduled for this date");
        }
    }
    $checkStmt->close();

    // First check if we've reached the maximum appointments for this date
    $maxAppointments = 50; // Set your maximum appointments per day here
    
    $sql = "SELECT COUNT(*) as appointment_count FROM schedules WHERE appointment_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $appointmentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['appointment_count'] >= $maxAppointments) {
            throw new Exception("Maximum appointments ($maxAppointments) reached for this date. Please choose another date.");
        }
    }
    $stmt->close();

    // Get the next appointment number for this date
    $appointmentNumber = 1;
    
    // Check if there are existing appointments for this date
    $sql = "SELECT MAX(appointment_number) as max_num FROM schedules WHERE appointment_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $appointmentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['max_num'] !== null) {
            $appointmentNumber = $row['max_num'] + 1;
        }
    }
    $stmt->close();

    // Insert the new appointment
    $sql = "INSERT INTO schedules (user_id, patient_name, appointment_date, appointment_number) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $userId, $patientName, $appointmentDate, $appointmentNumber);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Appointment scheduled successfully',
            'appointmentNumber' => $appointmentNumber
        ]);
    } else {
        throw new Exception("Failed to schedule appointment: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>