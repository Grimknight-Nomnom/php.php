<?php
session_start();
header('Content-Type: application/json');

// Security Check: Ensure only logged-in Admins can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the ID sent from JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['id'] ?? null;

if ($userId) {
    $conn = new mysqli("localhost", "root", "", "clinicphp");
    
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Update the user's status to 'approved'
    $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User approved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid User ID']);
}
?>