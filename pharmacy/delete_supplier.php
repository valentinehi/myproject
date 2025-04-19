<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $supplierId = intval($_POST['id']);
    $sql = "DELETE FROM suppliers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $supplierId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Supplier deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting supplier!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid supplier ID!']);
}
?>
