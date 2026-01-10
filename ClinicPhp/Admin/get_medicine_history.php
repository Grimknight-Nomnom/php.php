<?php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB Connection failed']);
    exit();
}

// Fetch logs ordered by newest first
$sql = "SELECT * FROM medicine_logs ORDER BY log_date DESC";
$result = $conn->query($sql);

$logs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format date and time
        $phpDate = new DateTime($row['log_date']);
        $row['formatted_date'] = $phpDate->format('F j, Y');
        $row['formatted_time'] = $phpDate->format('g:i A'); // 12-hour format
        $logs[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $logs]);
$conn->close();
?>