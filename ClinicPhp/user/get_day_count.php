<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$conn = new mysqli($servername, $username, $password, $dbname);
$date = $_GET['date'];

// Replace 'appointments' and 'appointment_date' with your actual table/column names
// Inside get_day_count.php
$sql = "SELECT COUNT(*) as total FROM schedules WHERE DATE(appointment_date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode(['count' => (int)$data['total']]);
$stmt->close();
$conn->close();
?>