<?php
// MUST be the very first line - no whitespace before!
header('Content-Type: application/json');

// Enable error logging but don't display to users
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/php_errors.log');

// Use absolute path to prevent include issues
require_once __DIR__.'/../includes/db_connection.php';

// Initialize response array
$response = ['success' => false, 'data' => []];

try {
    // Verify database connection
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new RuntimeException('Database connection failed');
    }

    // Test connection
    $conn->query("SELECT 1")->fetch();

    // Get date ranges
    $today = new DateTime();
    $nextMonth = (clone $today)->modify('+1 month');

    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT id, name, type, quantity, expiration_date 
        FROM medicines 
        WHERE quantity < 5 
           OR expiration_date BETWEEN :today AND :next_month
    ");
    
    $stmt->execute([
        ':today' => $today->format('Y-m-d'),
        ':next_month' => $nextMonth->format('Y-m-d')
    ]);

    // Process results
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $alerts = [];
        
        if ($row['quantity'] < 5) {
            $alerts[] = 'Low stock: '.$row['quantity'].' remaining';
        }
        
        if (new DateTime($row['expiration_date']) <= $nextMonth) {
            $expDate = new DateTime($row['expiration_date']);
            $alerts[] = 'Expires: '.$expDate->format('F Y'); // Changed to show only month and year
        }
        
        if (!empty($alerts)) {
            $response['data'][] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'type' => $row['type'],
                'alerts' => $alerts
            ];
        }
    }

    $response['success'] = true;
    $response['count'] = count($response['data']);

} catch (Throwable $e) {
    http_response_code(500);
    $response['error'] = 'Server error: '.$e->getMessage();
    error_log('Notification Error: '.$e->getMessage());
}

// Ensure no output before this
die(json_encode($response));
?>