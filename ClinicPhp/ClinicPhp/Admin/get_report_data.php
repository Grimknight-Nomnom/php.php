<?php
// get_report_data.php

// 1. Setup & Headers
error_reporting(0);
ini_set('display_errors', 0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "clinicphp");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// 2. Get Date Parameters & FORCE 2-DIGIT MONTH
// This fixes the bug where "1" didn't match "01"
$rawMonth = $_GET['month'] ?? date('m');
$reqMonth = str_pad($rawMonth, 2, "0", STR_PAD_LEFT); // Turns "1" into "01"
$reqYear = $_GET['year'] ?? date('Y');

// This string (e.g., "2026-01") is what we look for in the database
$targetYearMonth = "$reqYear-$reqMonth";

// =========================================================
// 1. AUTO-LOG EXPIRED ITEMS (Populates the Expired Graph)
// =========================================================
$currentYearMonth = date('Y-m');

// Only run auto-check if we are looking at the current month
if ($targetYearMonth == $currentYearMonth) {
    $stockSql = "SELECT name, quantity, expiration_date FROM medicines";
    $stockRes = $conn->query($stockSql);
    
    if ($stockRes) {
        while ($row = $stockRes->fetch_assoc()) {
            $expStr = trim($row['expiration_date']);
            if (empty($expStr)) continue;

            // Normalize Date formats (YYYY, YYYY-MM, YYYY-MM-DD)
            $cleanDate = $expStr;
            if (strlen($expStr) === 4) $cleanDate .= '-01-01';
            elseif (strlen($expStr) === 7) $cleanDate .= '-01';

            // Check if this item expires in the current view month
            // We use string matching on the formatted date to be safe
            if (strpos($cleanDate, $targetYearMonth) === 0) {
                
                $medName = $conn->real_escape_string($row['name']);
                
                // Check if already logged using DATE_FORMAT
                $checkLog = "SELECT id FROM medicine_logs 
                             WHERE medicine_name = '$medName' 
                             AND action_type = 'Expired' 
                             AND DATE_FORMAT(log_date, '%Y-%m') = '$targetYearMonth'";
                
                $checkRes = $conn->query($checkLog);

                // Insert if missing
                if ($checkRes->num_rows == 0) {
                    $qty = $row['quantity'];
                    $insSql = "INSERT INTO medicine_logs (medicine_name, action_type, quantity, patient_name, expiration_date, log_date) 
                               VALUES ('$medName', 'Expired', $qty, 'System', '$expStr', NOW())";
                    $conn->query($insSql);
                }
            }
        }
    }
}

// =========================================================
// 2. RELEASED GRAPH (The Fix for Blank Graph)
// =========================================================
// We use DATE_FORMAT(log_date, '%Y-%m') to reliably match "2026-01"
$sqlReleased = "SELECT medicine_name, SUM(quantity) as total_qty 
                FROM medicine_logs 
                WHERE action_type = 'Released' 
                AND DATE_FORMAT(log_date, '%Y-%m') = '$targetYearMonth'
                GROUP BY medicine_name";

$resReleased = $conn->query($sqlReleased);
$releasedData = [];
if ($resReleased) { while($row = $resReleased->fetch_assoc()) { $releasedData[] = $row; } }

// =========================================================
// 3. EXPIRED GRAPH
// =========================================================
$sqlExpired = "SELECT medicine_name as name, SUM(quantity) as quantity 
               FROM medicine_logs 
               WHERE action_type = 'Expired' 
               AND DATE_FORMAT(log_date, '%Y-%m') = '$targetYearMonth'
               GROUP BY medicine_name";

$resExpired = $conn->query($sqlExpired);
$expiredData = [];
if ($resExpired) { while($row = $resExpired->fetch_assoc()) { $expiredData[] = $row; } }

// =========================================================
// 4. LOW STOCK & EXPIRY LISTS (Standard)
// =========================================================
$sqlLow = "SELECT name, quantity, type FROM medicines WHERE quantity <= 20 ORDER BY quantity ASC";
$resLow = $conn->query($sqlLow);
$lowData = [];
if ($resLow) { while($row = $resLow->fetch_assoc()) { $lowData[] = $row; } }

$sqlClose = "SELECT name, quantity, expiration_date FROM medicines ORDER BY expiration_date ASC";
$resClose = $conn->query($sqlClose);
$expiryData = [];
$today = new DateTime();

if ($resClose) {
    while($row = $resClose->fetch_assoc()) {
        $dStr = $row['expiration_date'];
        // Fix partial dates for calculation
        if(strlen($dStr)==4) $dStr .= "-01-01";
        elseif(strlen($dStr)==7) $dStr .= "-01";
        
        try {
            $dObj = new DateTime($dStr);
            $diff = $today->diff($dObj);
            $days = $diff->days; 
            if ($diff->invert) $days = -$days;
            
            // Show items expired or expiring in next 65 days
            if ($days <= 65) { 
                $row['days_left'] = $days;
                $expiryData[] = $row;
            }
        } catch(Exception $e) {}
    }
}

// Final Output
echo json_encode([
    'success' => true,
    'released_graph' => $releasedData,
    'expired_graph' => $expiredData,
    'low_stock_list' => $lowData,
    'expiry_list' => $expiryData
]);

$conn->close();
?>