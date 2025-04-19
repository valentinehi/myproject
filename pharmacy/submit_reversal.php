<?php
session_start();
require 'connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
// Validate required fields
if (!isset($_POST['sale_id'], $_POST['product_id'], $_POST['quantity'], $_POST['reason']) || empty(trim($_POST['reason']))) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields or reason is empty.']);
    exit;
}
$sale_id = $_POST['sale_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$reason = trim($_POST['reason']);
$requested_by = $_SESSION['firstName'] ?? null; // Use firstName instead of ID
$requested_at = date('Y-m-d H:i:s');

if (!$requested_by) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

// Insert into database
$query = "INSERT INTO sales_reversals (sale_id, product_id, quantity, reason, requested_by, requested_at, status) 
          VALUES (?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiisss", $sale_id, $product_id, $quantity, $reason, $requested_by, $requested_at);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

?>
