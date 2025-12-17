<?php
session_start();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    // Get the medicine ID from the POST request
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Invalid medicine ID');
    }
    
    $id = (int)$data['id'];

    // Prepare and execute the SQL statement to delete the medicine
    $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Medicine deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No medicine found with that ID']);
        }
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>