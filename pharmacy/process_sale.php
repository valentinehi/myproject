<?php
session_start();
require 'connect.php';

// Fetch salesperson's name from session
$salesperson_name = $_SESSION['firstName'] ?? 'Unknown';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $sale_date = $_POST['sale_date'];
    $amount_paid = floatval($_POST['amount_paid']);
    $cart = json_decode($_POST['cart'], true);

    $total_cost = 0;

    // Start a database transaction
    $conn->begin_transaction();

    try {
        foreach ($cart as $item) {
            $product_id = intval($item['id']);
            $quantity_sold_smallest = intval($item['qty']); // Sold quantity in smallest unit
            $selling_price = floatval($item['price']);

            // Fetch inventory details
            $query = "SELECT i.quantity, i.base_unit, i.smallest_unit, i.conversion_factor, 
                             i.selling_price, i.stock_out, d.cost_price
                      FROM inventory i 
                      JOIN drug_product d ON i.drug_id = d.id 
                      WHERE i.drug_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (!$product) {
                throw new Exception("Product not found (ID: $product_id)");
            }

            $conversion_factor = $product['conversion_factor'];
            if ($conversion_factor <= 0) {
                throw new Exception("Invalid conversion factor for product ID: $product_id");
            }

            // Convert quantity sold from smallest unit to base unit
            $quantity_sold_base = $quantity_sold_smallest / $conversion_factor;

            // Check for sufficient stock
            if ($product['quantity'] < $quantity_sold_base) {
                throw new Exception("Insufficient stock for product ID: $product_id");
            }

            // Calculate profit per smallest unit
            $cost_per_smallest_unit = $product['cost_price'] / $conversion_factor;
            $profit = ($selling_price - $cost_per_smallest_unit) * $quantity_sold_smallest;

            // Insert sale record
            $insertSale = "INSERT INTO sales (product_id, quantity, selling_price, sale_date, customer_name, salesperson, profit) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSale);
            $stmt->bind_param("iidsssd", $product_id, $quantity_sold_smallest, $selling_price, $sale_date, $customer_name, $salesperson_name, $profit);
            $stmt->execute();

            // Update inventory: deduct from quantity and add to stock_out
            $updateInventory = "UPDATE inventory 
                                SET quantity = quantity - ?, stock_out = stock_out + ? 
                                WHERE drug_id = ?";
            $stmt = $conn->prepare($updateInventory);
            $stmt->bind_param("ddi", $quantity_sold_base, $quantity_sold_base, $product_id);
            $stmt->execute();

            // Update total cost
            $total_cost += $selling_price * $quantity_sold_smallest;
        }

        // Calculate change
        $change = $amount_paid - $total_cost;

        // Commit transaction
        $conn->commit();

        echo json_encode(["status" => "success", "message" => "Sale completed", "change" => $change]);

    } catch (Exception $e) {
        $conn->rollback(); // Revert changes if an error occurs
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>
