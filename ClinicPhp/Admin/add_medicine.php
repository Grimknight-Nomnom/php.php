<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get form data
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $expiration_date = $_POST['expiration_date'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($type) || empty($quantity) || empty($expiration_date)) {
        die(json_encode(['success' => false, 'message' => 'All fields are required']));
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO medicines (name, type, description, quantity, expiration_date) 
                           VALUES (:name, :type, :description, :quantity, :expiration_date)");
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':expiration_date', $expiration_date);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Medicine added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add medicine']);
    }
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>