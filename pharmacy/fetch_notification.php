<?php
require_once 'connect.php';

// Fetch sales reversals awaiting approval
$reversalQuery = "SELECT COUNT(*) AS pending_reversals FROM sales_reversals WHERE status = 'pending'";
$reversalResult = $conn->query($reversalQuery);
$pendingReversals = ($reversalResult->fetch_assoc())['pending_reversals'] ?? 0;

// Fetch expired products
$expiredQuery = "SELECT COUNT(*) AS expired_count FROM expired_product WHERE date_expired <= CURDATE()";
$expiredResult = $conn->query($expiredQuery);
$expiredCount = ($expiredResult->fetch_assoc())['expired_count'] ?? 0;

// Total notifications
$totalNotifications = $pendingReversals + $expiredCount;

// Create JSON response
$response = [
    'total' => $totalNotifications,
    'notifications' => []
];

if ($pendingReversals > 0) {
    $response['notifications'][] = "$pendingReversals pending sales reversals.";
}

if ($expiredCount > 0) {
    $response['notifications'][] = "$expiredCount expired products in inventory.";
}

echo json_encode($response);
?>
