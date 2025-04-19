<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryId = $_POST['category_id'];
    $categoryName = trim($_POST['category_name']);

    if (!empty($categoryName)) {
        $stmt = $conn->prepare("UPDATE category SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $categoryName, $categoryId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Category Updated Successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Category name cannot be empty.']);
    }
    exit;
}
