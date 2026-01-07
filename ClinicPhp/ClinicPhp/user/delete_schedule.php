<?php
session_start();
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

// Get the data from the fetch request
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }

    /** * FIX: Changed 'appointmentDate' to 'appointment_date' to match your DB
     * RULE: Only delete if the date is Today or in the Future (>= $today)
     */
    $stmt = $conn->prepare("DELETE FROM schedules WHERE user_id = ? AND appointment_date >= ?");
    $stmt->bind_param("is", $user_id, $today);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Upcoming schedule cancelled successfully.']);
        } else {
            // This triggers if the user has no upcoming schedules (only past ones)
            echo json_encode(['success' => false, 'message' => 'No upcoming schedule found to delete. Past appointments are locked.']);
        }
    } else {
        throw new Exception("Execution failed: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>