<?php
session_start();
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['firstName'])) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit;
    }

    $reversal_id = $_POST['reversal_id'];
    $action = $_POST['action'];
    $admin_id = $_SESSION['firstName']; // Admin handling the approval/rejection
    $approved_at = date('Y-m-d H:i:s'); // Timestamp

    // Fetch product details from sales_reversals
    $reversal_query = "SELECT product_id, quantity FROM sales_reversals WHERE id = ?";
    $stmt = $conn->prepare($reversal_query);
    $stmt->bind_param("i", $reversal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reversal = $result->fetch_assoc();

    if (!$reversal) {
        echo json_encode(["status" => "error", "message" => "Reversal not found."]);
        exit;
    }

    $product_id = $reversal['product_id'];
    $reversed_quantity = $reversal['quantity']; // This is in smallest units

    // Fetch conversion factor from inventory
    $conversion_query = "SELECT conversion_factor FROM inventory WHERE drug_id = ?";
    $stmt = $conn->prepare($conversion_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $inventory = $result->fetch_assoc();

    if (!$inventory) {
        echo json_encode(["status" => "error", "message" => "Inventory item not found."]);
        exit;
    }

    $conversion_factor = $inventory['conversion_factor'];

    if ($conversion_factor <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid conversion factor."]);
        exit;
    }

    // Convert reversed quantity from smallest unit to base unit
    $adjusted_quantity = $reversed_quantity / $conversion_factor;

    if ($action === "approve") {
        // Approve reversal
        $update_reversal = "UPDATE sales_reversals 
                            SET status = 'Approved', approved_by = ?, approved_at = ? 
                            WHERE id = ?";
        $stmt = $conn->prepare($update_reversal);
        $stmt->bind_param("ssi", $admin_id, $approved_at, $reversal_id);
        $stmt->execute();

        // Update inventory correctly
        $update_inventory = "UPDATE inventory 
                             SET quantity = quantity + ? 
                             WHERE drug_id = ?";
        $stmt = $conn->prepare($update_inventory);
        $stmt->bind_param("di", $adjusted_quantity, $product_id);
        $stmt->execute();
    } else {
        // Reject reversal
        $update_reversal = "UPDATE sales_reversals 
                            SET status = 'Rejected', approved_by = ?, approved_at = ? 
                            WHERE id = ?";
        $stmt = $conn->prepare($update_reversal);
        $stmt->bind_param("ssi", $admin_id, $approved_at, $reversal_id);
        $stmt->execute();
    }

    echo json_encode(["status" => "success"]);
}
?>
