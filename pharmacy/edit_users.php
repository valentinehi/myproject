<?php
// Include the database connection
require_once 'connect.php'; // Adjust the path if necessary

header('Content-Type: application/json'); // Ensure JSON response

// Handle GET request to fetch user details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_user' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure ID is an integer

    // Fetch user details from the database
    $sql = "SELECT id, firstName, lastName, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode(["status" => "success", "user" => $user]);
        } else {
            echo json_encode(["status" => "error", "message" => "User not found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Database query failed: " . $stmt->error]);
    }
    exit;
}

// Handle POST request to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = intval($_POST['user_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    // Check if the password is being updated (optional)
    $password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_DEFAULT) : null;

    // Update user data, including password if provided
    if ($password) {
        $sql = "UPDATE users SET firstName = ?, lastName = ?, email = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $firstName, $lastName, $email, $role, $password, $userId);
    } else {
        $sql = "UPDATE users SET firstName = ?, lastName = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $role, $userId);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User details updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating user: " . $stmt->error]);
    }
    exit;
}

// Fallback for invalid requests
echo json_encode(["status" => "error", "message" => "Invalid request."]);
?>
