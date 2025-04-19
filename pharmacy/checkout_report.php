<?php
if (!isset($_GET['cart'])) {
    echo "<script>alert('No order found!'); window.location.href='sales_page.php';</script>";
    exit;
}

$cart = json_decode(urldecode($_GET['cart']), true);
$totalAmount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .report-container {
            display: flex;
            justify-content: space-between;
        }
        .section {
            width: 45%;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        button {
            padding: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        .pay-now {
            background: green;
            color: white;
        }
        .cancel-order {
            background: red;
            color: white;
        }
        .return {
            background: orange;
            color: white;
        }
    </style>
</head>
<body>
  

    <h1>Checkout Report</h1>

    <div class="report-container">
        <!-- Customer Details -->
        <div class="section">
            <h2>Customer Details</h2>
            <form>
                <label>Customer Name:</label>
                <input type="text" id="customer-name" class="input-field" required><br><br>
                <label>Contact:</label>
                <input type="text" id="customer-contact" class="input-field" required><br><br>
                <button class="pay-now" onclick="payNow()">Pay Now</button>
            </form>
        </div>

        <!-- Order Details -->
        <div class="section">
            <h2>Order Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): 
                        $total = $item['price'] * $item['qty'];
                        $totalAmount += $total;
                    ?>
                    <tr>
                        <td><?= $item['name'] ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>$<?= number_format($total, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Total: $<?= number_format($totalAmount, 2) ?></h3>
            <div class="buttons">
                <button class="cancel-order" onclick="cancelOrder()">Cancel Order</button>
                <button class="return" onclick="returnOrder()">Return</button>
            </div>
        </div>
    </div>

    <script>
        function payNow() {
            let customerName = document.getElementById("customer-name").value;
            let customerContact = document.getElementById("customer-contact").value;

            if (!customerName || !customerContact) {
                alert("Please fill in customer details!");
                return;
            }

            alert("Payment Successful!");
            window.location.href = "sales_page.php";
        }

        function cancelOrder() {
            alert("Order Cancelled!");
            window.location.href = "sales_page.php";
        }

        function returnOrder() {
            alert("Order Returned!");
        }
    </script>

</body>
</html>
