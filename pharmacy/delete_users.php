<?php
// Include the database connection
require_once 'connect.php'; // Adjust the path if necessary

header('Content-Type: application/json'); // Ensure JSON response

// Handle POST request to delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_users'])) {
    $userId = intval($_POST['user_id']); // Ensure user_id is an integer

    // Check if user ID is valid
    if ($userId <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid user ID."]);
        exit;
    }

    // SQL query to delete the user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting user: " . $stmt->error]);
    }
    exit;
}

// Fallback for invalid requests
echo json_encode(["status" => "error", "message" => "Invalid request."]);
?>
