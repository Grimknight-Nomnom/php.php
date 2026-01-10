<?php
// get_report_data.php
error_reporting(0);
ini_set('display_errors', 0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "clinicphp");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// 1. Get Date Parameters
$reqMonth = $_GET['month'] ?? date('m');
$reqYear = $_GET['year'] ?? date('Y');

// Ensure Month is 2 digits (e.g., "1" becomes "01")
$reqMonth = str_pad($reqMonth, 2, "0", STR_PAD_LEFT);

// Create the Search Pattern (e.g., "2026-01")
// This matches "2026-01-03 09:51:55" perfectly without complex date math
$searchPattern = "$reqYear-$reqMonth";

// =========================================================
// 2. AUTO-LOG EXPIRED ITEMS (Populates the Expired Graph)
// =========================================================
$currentYearMonth = date('Y-m');

// Only check for expirations if viewing the current month
if ($searchPattern == $currentYearMonth) {
    $stockSql = "SELECT name, quantity, expiration_date FROM medicines";
    $stockRes = $conn->query($stockSql);
    
    if ($stockRes) {
        while ($row = $stockRes->fetch_assoc()) {
            $expStr = trim($row['expiration_date']);
            if (empty($expStr)) continue;

            // Fix partial dates for comparison
            $cleanDate = $expStr;
            if (strlen($expStr) === 4) $cleanDate = "$expStr-01-01";
            elseif (strlen($expStr) === 7) $cleanDate = "$expStr-01";

            // If the text starts with "2026-01", it expires this month
            if (strpos($cleanDate, $searchPattern) === 0) {
                
                $medName = $conn->real_escape_string($row['name']);
                
                // Check if already logged (using text match on log_date)
                $checkLog = "SELECT id FROM medicine_logs 
                             WHERE medicine_name = '$medName' 
                             AND action_type = 'Expired' 
                             AND log_date LIKE '$searchPattern%'";
                
                $checkRes = $conn->query($checkLog);

                // Insert if missing
                if ($checkRes->num_rows == 0) {
                    $qty = $row['quantity'];
                    // Insert the log
                    $insSql = "INSERT INTO medicine_logs (medicine_name, action_type, quantity, patient_name, expiration_date, log_date) 
                               VALUES ('$medName', 'Expired', $qty, 'System', '$expStr', NOW())";
                    $conn->query($insSql);
                }
            }
        }
    }
}

// =========================================================
// 3. RELEASED GRAPH (The Fix)
// =========================================================
// usage: WHERE log_date LIKE '2026-01%' 
// This finds ALL records starting with that year-month
$sqlReleased = "SELECT medicine_name, SUM(quantity) as total_qty 
                FROM medicine_logs 
                WHERE action_type = 'Released' 
                AND log_date LIKE '$searchPattern%'
                GROUP BY medicine_name";

$resReleased = $conn->query($sqlReleased);
$releasedData = [];
if ($resReleased) { while($row = $resReleased->fetch_assoc()) { $releasedData[] = $row; } }

// =========================================================
// 4. EXPIRED GRAPH
// =========================================================
$sqlExpired = "SELECT medicine_name as name, SUM(quantity) as quantity 
               FROM medicine_logs 
               WHERE action_type = 'Expired' 
               AND log_date LIKE '$searchPattern%'
               GROUP BY medicine_name";

$resExpired = $conn->query($sqlExpired);
$expiredData = [];
if ($resExpired) { while($row = $resExpired->fetch_assoc()) { $expiredData[] = $row; } }

// =========================================================
// 5. LISTS
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
        if(strlen($dStr)==4) $dStr .= "-01-01";
        elseif(strlen($dStr)==7) $dStr .= "-01";
        
        try {
            $dObj = new DateTime($dStr);
            $diff = $today->diff($dObj);
            $days = $diff->days; 
            if ($diff->invert) $days = -$days;
            
            if ($days <= 65) { 
                $row['days_left'] = $days;
                $expiryData[] = $row;
            }
        } catch(Exception $e) {}
    }
}

// Output
echo json_encode([
    'success' => true,
    'released_graph' => $releasedData,
    'expired_graph' => $expiredData,
    'low_stock_list' => $lowData,
    'expiry_list' => $expiryData
]);

$conn->close();
?>