<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);

        // Step 1: Delete the drug from `drug_product`
        $delete_drug = $conn->prepare("DELETE FROM drug_product WHERE id = ?");
        $delete_drug->bind_param("i", $id);

        if ($delete_drug->execute()) {
            // Step 2: Delete corresponding inventory record
            $delete_inventory = $conn->prepare("DELETE FROM inventory WHERE drug_id = ?");
            $delete_inventory->bind_param("i", $id);
            $delete_inventory->execute();

            echo json_encode(["status" => "success", "message" => "Drug and inventory record deleted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete drug."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid method."]);
}
?>
