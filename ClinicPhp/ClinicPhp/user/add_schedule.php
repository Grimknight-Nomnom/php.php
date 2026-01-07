<?php
session_start();
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $requested_date = $_POST['schedule_date'];
    $today = date('Y-m-d');

    // 1. Check if the user already has a future/current schedule
    $check_sql = "SELECT id FROM schedules WHERE user_id = ? AND schedule_date >= ? LIMIT 1";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('You already have an active schedule. You must wait until it passes to book again.'); window.location='user_schedules.php';</script>";
    } else {
        // 2. Insert the new schedule
        $insert_sql = "INSERT INTO schedules (user_id, schedule_date) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("is", $user_id, $requested_date);
        
        if ($stmt->execute()) {
            header("Location: user_schedules.php?success=1");
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>