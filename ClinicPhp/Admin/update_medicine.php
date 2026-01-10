<?php
// update_medicine.php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "clinicphp");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// 1. Get Data
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$type = $_POST['type'] ?? '';
$desc = $_POST['description'] ?? '';
$qty = $_POST['quantity'] ?? 0;
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

// 2. Validate
if (!$id || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit();
}

// 3. Format Date (YYYY-MM)
$finalDate = "";
if (!empty($year) && !empty($month)) {
    $finalDate = "$year-$month";
} elseif (!empty($year)) {
    $finalDate = "$year";
}

// 4. Update Stock Table
$sql = "UPDATE medicines SET name=?, type=?, description=?, quantity=?, expiration_date=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssisi", $name, $type, $desc, $qty, $finalDate, $id);

if ($stmt->execute()) {
    
    // 5. CRITICAL: Insert into History Log
    // We use 'Updated' as the action type
    $logSql = "INSERT INTO medicine_logs (medicine_name, action_type, quantity, patient_name, expiration_date, log_date) 
               VALUES (?, 'Updated', ?, 'Admin Edit', ?, NOW())";
    
    $logStmt = $conn->prepare($logSql);
    // Bind: string(name), int(qty), string(date)
    $logStmt->bind_param("sis", $name, $qty, $finalDate);
    $logStmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>