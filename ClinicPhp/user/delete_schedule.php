<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['userId'];

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed");
    }

    // Adjust 'user_id' and 'schedules' to match your actual database table and column names
    $stmt = $conn->prepare("DELETE FROM schedules WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No schedule found for this user.']);
        }
    } else {
        throw new Exception("Execution failed");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>