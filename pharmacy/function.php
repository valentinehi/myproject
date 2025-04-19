<?php
function getAvailableStock($drug_id, $conn) {
    $query = $conn->query("SELECT quantity, conversion_factor FROM drug_product WHERE id = $drug_id");
    $drug = $query->fetch_assoc();

    $stockInQuery = $conn->query("SELECT SUM(quantity) AS stock_in FROM inventory WHERE drug_id = $drug_id AND type = 1");
    $stockOutQuery = $conn->query("SELECT SUM(quantity) AS stock_out FROM inventory WHERE drug_id = $drug_id AND type = 2");

    $stock_in = $stockInQuery->fetch_assoc()['stock_in'] ?? 0;
    $stock_out = $stockOutQuery->fetch_assoc()['stock_out'] ?? 0;

    $available_stock = ($stock_in * $drug['conversion_factor']) - $stock_out;
    
    return $available_stock;
}

function deductStock($drug_id, $quantity_sold, $conn) {
    $query = $conn->query("SELECT conversion_factor FROM drug_product WHERE id = $drug_id");
    $drug = $query->fetch_assoc();

    // Convert to bulk unit
    $bulk_quantity = $quantity_sold / $drug['conversion_factor'];

    // Deduct from inventory
    $stmt = $conn->prepare("INSERT INTO inventory (drug_id, quantity, type) VALUES (?, ?, 2)");
    $stmt->bind_param("id", $drug_id, $bulk_quantity);
    return $stmt->execute();
}
?>

