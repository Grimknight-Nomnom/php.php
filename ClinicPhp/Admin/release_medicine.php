<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $qty_released = intval($_POST['quantity_released'] ?? 0);
    $patient_name = $_POST['patient_name'] ?? ''; 

    if (empty($id) || $qty_released <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity or ID']);
        exit();
    }

    // 1. Check current stock first
    // UPDATE: We select 'name' as well so we can log it
    $checkSql = "SELECT name, quantity FROM medicines WHERE id = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['quantity'] >= $qty_released) {
        $medName = $row['name']; // Capture name for logging

        // 2. Update stock (Deduct)
        $updateSql = "UPDATE medicines SET quantity = quantity - ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateSql);
        $stmtUpdate->bind_param("ii", $qty_released, $id);
        
        if ($stmtUpdate->execute()) {
            
            // --- START LOGGING CODE ---
            date_default_timezone_set('Asia/Manila');
            $logDate = date('Y-m-d H:i:s');
            $action = "Released";
            
            // Prepare log insertion
            $logStmt = $conn->prepare("INSERT INTO medicine_logs (medicine_name, action_type, quantity, patient_name, log_date) VALUES (?, ?, ?, ?, ?)");
            // Types: s=string, s=string, i=int, s=string, s=string
            $logStmt->bind_param("ssiss", $medName, $action, $qty_released, $patient_name, $logDate);
            $logStmt->execute();
            $logStmt->close();
            // --- END LOGGING CODE ---

            echo json_encode(['success' => true, 'message' => 'Stock updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
        $stmtUpdate->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    }
    
    $stmtCheck->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>