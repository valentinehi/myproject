<?php
// Include the database connection
require_once 'connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');



// Check if the request is to get the stock data (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    
    $stockId = intval($_GET['id']); // Get the stock ID from the URL parameter

    // Fetch stock details from the database, including base_unit, smallest_unit, and conversion_factor
    $sql = "SELECT id, supplier_name, product_supplied, number_supplied, date_supplied, base_unit, smallest_unit, conversion_factor FROM stock WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stockId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $stock = $result->fetch_assoc();

        if ($stock) {
            // Return stock data as JSON
            echo json_encode(["status" => "success", "stock" => $stock]);
        } else {
            echo json_encode(["status" => "error", "message" => "Stock not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to fetch stock details"]);
    }
    exit; // Stop execution here for GET request
}

// Handle the POST request for updating stock data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        // Collect the form data
        $stockId = intval($_POST['id']);
        $supplierName = trim($_POST['supplier_name']);
        $productSupplied = trim($_POST['product_supplied']);
        $numberSupplied = intval($_POST['number_supplied']);
        $dateSupplied = $_POST['date_supplied'];
        $baseUnit = trim($_POST['base_unit']);
        $smallestUnit = trim($_POST['smallest_unit']);
        $conversionFactor = floatval($_POST['conversion_factor']);

        // Check for missing fields
        if (!$stockId || !$supplierName || !$productSupplied || !$numberSupplied || !$dateSupplied || !$baseUnit || !$smallestUnit || !$conversionFactor) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        // Update the stock data in the database, including base_unit, smallest_unit, and conversion_factor
        $sql = "UPDATE stock SET supplier_name = ?, product_supplied = ?, number_supplied = ?, date_supplied = ?, base_unit = ?, smallest_unit = ?, conversion_factor = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssdi", $supplierName, $productSupplied, $numberSupplied, $dateSupplied, $baseUnit, $smallestUnit, $conversionFactor, $stockId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Stock updated successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    }
    exit; // Stop execution here for POST request
}
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
