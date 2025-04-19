<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierId = $_POST['id'];
    $supplierName = trim($_POST['name']);
    $supplierAddress = trim($_POST['address']);
    $supplierPhone = trim($_POST['phone']);

    if (!empty($supplierName) && !empty($supplierAddress) && !empty($supplierPhone)) {
        $sql = "UPDATE suppliers SET name = ?, address = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $supplierName, $supplierAddress, $supplierPhone, $supplierId);

        if ($stmt->execute()) {
            // Send updated data for real-time UI refresh
            echo json_encode([
                'status' => 'success',
                'message' => 'Supplier updated successfully!',
                'data' => [
                    'id' => $supplierId,
                    'name' => $supplierName,
                    'address' => $supplierAddress,
                    'phone' => $supplierPhone
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating supplier.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    }
}
?>

