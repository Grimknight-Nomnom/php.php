<?php
// add_medicine.php
session_start();
// This line is critical: tell the browser we are sending JSON
header('Content-Type: application/json');

// Turn off error reporting to the screen so it doesn't break JSON
error_reporting(0); 
ini_set('display_errors', 0);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check Request Method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data safely
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $expiration_date = $_POST['expiration_date'] ?? '';

    // Validate inputs
    if (empty($name) || empty($type) || empty($quantity) || empty($expiration_date)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit();
    }

    // Insert Query
    $stmt = $conn->prepare("INSERT INTO medicines (name, type, description, quantity, expiration_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $name, $type, $description, $quantity, $expiration_date);

    if ($stmt->execute()) {
        // --- START LOGGING CODE ---
        // Log the addition to the history table
        date_default_timezone_set('Asia/Manila');
        $logDate = date('Y-m-d H:i:s');
        $action = "Added";
        
        $logStmt = $conn->prepare("INSERT INTO medicine_logs (medicine_name, action_type, quantity, expiration_date, log_date) VALUES (?, ?, ?, ?, ?)");
        $logStmt->bind_param("ssiss", $name, $action, $quantity, $expiration_date, $logDate);
        $logStmt->execute();
        $logStmt->close();
        // --- END LOGGING CODE ---

        echo json_encode(['success' => true, 'message' => 'Medicine added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>