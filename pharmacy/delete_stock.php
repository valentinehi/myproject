<?php
require_once 'connect.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_stock']) && isset($_POST['stock_id'])) {
    try {
        $stockId = intval($_POST['stock_id']);

        if ($stockId <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid Stock ID"]);
            exit;
        }

        // Step 1: Retrieve stock details before deleting
        $get_stock = $conn->prepare("SELECT product_supplied, number_supplied FROM stock WHERE id = ?");
        $get_stock->bind_param("i", $stockId);
        $get_stock->execute();
        $result = $get_stock->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Stock not found"]);
            exit;
        }

        $row = $result->fetch_assoc();
        $productName = $row['product_supplied'];
        $numberSupplied = floatval($row['number_supplied']); // This is in base units

        // Step 2: Find the product ID
        $get_product_id = $conn->prepare("SELECT id FROM drug_product WHERE name = ?");
        $get_product_id->bind_param("s", $productName);
        $get_product_id->execute();
        $product_result = $get_product_id->get_result();

        if ($product_result->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Product not found"]);
            exit;
        }

        $product_row = $product_result->fetch_assoc();
        $productId = $product_row['id'];

        // Step 3: Reduce the inventory in base units
        $update_inventory = $conn->prepare("UPDATE inventory SET quantity = GREATEST(quantity - ?, 0) WHERE drug_id = ?");
        $update_inventory->bind_param("di", $numberSupplied, $productId);
        $update_inventory->execute();

        // Step 4: Delete the stock record from the stock table
        $stmt = $conn->prepare("DELETE FROM stock WHERE id = ?");
        $stmt->bind_param("i", $stockId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Stock deleted and inventory updated successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    }
    exit;
}
?>
