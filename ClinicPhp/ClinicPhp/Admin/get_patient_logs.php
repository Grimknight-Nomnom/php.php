<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "SELECT * FROM patient_medical_logs WHERE patient_id = ? ORDER BY date_logged DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        // Format the date nicely
        $row['formatted_date'] = date("F j, Y, g:i a", strtotime($row['date_logged']));
        $logs[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $logs]);
} else {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
}

$conn->close();
?>