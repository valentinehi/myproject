<?php
// Include the database connection
require_once 'connect.php';

if (isset($_POST['category_id'])) {
    $categoryId = $_POST['category_id'];

    // Delete the category from the database
    $deleteQuery = "DELETE FROM category WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $categoryId);

    if ($stmt->execute()) {
        // Get the maximum ID after deletion to set the auto-increment
        $maxIdQuery = "SELECT MAX(id) AS max_id FROM category";
        $result = $conn->query($maxIdQuery);
        $maxId = $result->fetch_assoc()['max_id'];

        // If there are no rows left, set the auto-increment to 1
        if ($maxId === null) {
            $maxId = 0; // Set to 0 if no rows are available
        }

        // Set the auto-increment value to the next available ID
        $resetAutoIncrementQuery = "ALTER TABLE category AUTO_INCREMENT = " . ($maxId + 1);
        $conn->query($resetAutoIncrementQuery); // Execute the query

        // Return success message
        echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully.']);
    } else {
        // Return error message if delete fails
        echo json_encode(['status' => 'error', 'message' => 'Error deleting category.']);
    }

    $stmt->close();
}
