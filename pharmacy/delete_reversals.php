<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_POST['sale_id']) || empty($_POST['sale_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing sale ID.']);
    exit;
}

$sale_id = (int) $_POST['sale_id'];

// Delete from database
$query = "DELETE FROM sales_reversals WHERE sale_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sale_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Reversal request deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete: ' . mysqli_error($conn)]);
}

?>
