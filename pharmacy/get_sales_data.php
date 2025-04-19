<?php
require_once 'connect.php'; // Ensure correct database connection

$query = "SELECT salesperson, SUM(selling_price * quantity) AS total_sales FROM sales GROUP BY salesperson";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
