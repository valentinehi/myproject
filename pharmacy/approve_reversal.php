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
    $admin_id = $_SESSION['firstName']; // Admin who approves or rejects
    $approved_at = date('Y-m-d H:i:s'); // Timestamp

    // Get reversal details (needed for inventory update and salesperson notification)
    $reversal_query = "SELECT product_id, quantity, requested_by FROM sales_reversals WHERE id = ?";
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
    $quantity = $reversal['quantity'];
    $requested_by = $reversal['requested_by']; // Salesperson who requested the reversal

    if ($action === "approve") {
        //Approve reversal and update inventory
        $query = "UPDATE sales_reversals 
                  SET status = 'Approved', approved_by = ?, approved_at = ?, notification_status = 'Unread'
                  WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $admin_id, $approved_at, $reversal_id);
        $stmt->execute();

        //Inventory Update: Convert Smallest Unit to Base Unit
        $inventory_query = "UPDATE inventory 
                            SET quantity = quantity + 
                                (? / (SELECT conversion_factor FROM inventory WHERE drug_id = ?))
                            WHERE drug_id = ?";

        $stmt = $conn->prepare($inventory_query);
        $stmt->bind_param("dii", $quantity, $product_id, $product_id);
        $stmt->execute();
    } else {
        //  Reject reversal
        $query = "UPDATE sales_reversals 
                  SET status = 'Rejected', approved_by = ?, approved_at = ?, notification_status = 'Unread'
                  WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $admin_id, $approved_at, $reversal_id);
        $stmt->execute();
    }

    echo json_encode(["status" => "success"]);
}
?>
