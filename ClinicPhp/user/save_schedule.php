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
    $todayStr = date('Y-m-d');

    // 1. Validate input
    if (empty($patientName) || empty($appointmentDate) || empty($userId)) {
        throw new Exception("All fields are required");
    }

    // 2. Check if user already has an active/future schedule
    // This implements the "Only 1 schedule at a time" rule
    $activeCheckSql = "SELECT appointment_date FROM schedules 
                       WHERE user_id = ? AND appointment_date >= ? LIMIT 1";
    $activeStmt = $conn->prepare($activeCheckSql);
    $activeStmt->bind_param("is", $userId, $todayStr);
    $activeStmt->execute();
    $activeResult = $activeStmt->get_result();

    if ($activeResult->num_rows > 0) {
        $activeRow = $activeResult->fetch_assoc();
        throw new Exception("You already have an active appointment scheduled for " . $activeRow['appointment_date'] . ". You can only add a new one once this date has passed.");
    }
    $activeStmt->close();

    // 3. Check if date is today or in the future
    if ($appointmentDate < $todayStr) {
        throw new Exception("Cannot schedule appointments for past dates");
    }

    // 4. Maximum appointments check (Daily Limit)
    $maxAppointments = 50; 
    $limitSql = "SELECT COUNT(*) as appointment_count FROM schedules WHERE appointment_date = ?";
    $limitStmt = $conn->prepare($limitSql);
    $limitStmt->bind_param("s", $appointmentDate);
    $limitStmt->execute();
    $limitResult = $limitStmt->get_result();
    $limitRow = $limitResult->fetch_assoc();
    
    if ($limitRow['appointment_count'] >= $maxAppointments) {
        throw new Exception("Maximum appointments reached for this date. Please choose another day.");
    }
    $limitStmt->close();

    // 5. Get the next appointment number for this specific date
    $appointmentNumber = 1;
    $numSql = "SELECT MAX(appointment_number) as max_num FROM schedules WHERE appointment_date = ?";
    $numStmt = $conn->prepare($numSql);
    $numStmt->bind_param("s", $appointmentDate);
    $numStmt->execute();
    $numRes = $numStmt->get_result();
    $numRow = $numRes->fetch_assoc();
    if ($numRow['max_num'] !== null) {
        $appointmentNumber = $numRow['max_num'] + 1;
    }
    $numStmt->close();

    // 6. Insert the new appointment
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
        throw new Exception("Failed to save to database");
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