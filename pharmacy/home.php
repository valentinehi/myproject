<?php
include 'connect.php';

// Fetch data for total sales, total profit, low stock drugs, and expired products

// Total Sales
$totalSalesQuery = "SELECT SUM(quantity) AS total_sales FROM sales";  
$totalSalesResult = $conn->query($totalSalesQuery);
$totalSales = ($totalSalesResult->num_rows > 0) ? $totalSalesResult->fetch_assoc()['total_sales'] : 0;

// Total Profit
$totalProfitQuery = "SELECT SUM(profit) AS total_profit FROM sales";  
$totalProfitResult = $conn->query($totalProfitQuery);
$totalProfit = ($totalProfitResult->num_rows > 0) ? $totalProfitResult->fetch_assoc()['total_profit'] : 0;

// Low Stock Drugs
$lowStockQuery = "SELECT COUNT(*) AS low_stock FROM inventory WHERE quantity < 10"; 
$lowStockResult = $conn->query($lowStockQuery);
$lowStock = ($lowStockResult->num_rows > 0) ? $lowStockResult->fetch_assoc()['low_stock'] : 0;

// Expired Products
$expiredQuery = "SELECT COUNT(*) AS expired_products FROM expired_product WHERE date_expired < CURDATE()";
$expiredResult = $conn->query($expiredQuery);

$expiredProducts = ($expiredResult && $expiredResult->num_rows > 0) ? $expiredResult->fetch_assoc()['expired_products'] : 0;

$query = "SELECT i.name AS product_name, SUM(s.quantity) AS total_sold 
          FROM sales s
          JOIN inventory i ON s.product_id = i.drug_id
          GROUP BY s.product_id
          ORDER BY total_sold DESC
          LIMIT 5";

$result = mysqli_query($conn, $query);
$query_sales_log = "SELECT s.product_id, i.name AS product_name, i.cost_price, 
                           s.quantity, s.profit, s.sale_date, s.salesperson 
                    FROM sales s
                    JOIN drug_product i ON s.product_id = i.id
                    ORDER BY s.sale_date DESC";
$result_sales_log = mysqli_query($conn, $query_sales_log);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Overview</title>
    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="style.css">
    
    

    
</head>
<body>
    <h1> Welcome to home page</h1>
    <h2>Dashboard Overview</h2>
    
    <div class="card-container">
    <!-- Total Sales Card -->
    <div class="card card-sales">
        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
        <h3>Total Sales</h3>
        <p><span id="total-sales"><?php echo $totalSales; ?></span> Units</p>
    </div>
    <!-- Total Profit Card -->
    <div class="card card-profit">
        <i class="fas fa-dollar-sign fa-3x mb-3"></i>
        <h3>Total Profit</h3>
        <p>Ksh <span id="total-profit"><?php echo number_format($totalProfit, 2); ?></span></p>
    </div>
    <!-- Low Stock Drugs Card -->
    <div class="card card-low-stock">
        <i class="fas fa-pills fa-3x mb-3"></i>
        <h3>Low Stock Drugs</h3>
        <p><span id="low-stock-drugs"><?php echo $lowStock; ?></span> Items</p>
    </div>
    <!-- Expired Products Card -->
    <div class="card card-expired">
        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
        <h3>Expired Products</h3>
        <p><span id="expired-products"><?php echo $expiredProducts; ?></span> Items</p>
    </div>
</div>

<!-- Add these canvas elements where you want the charts to appear -->
<div class="chart-container">
    <div class="chart-box">
        <h3>Total Sales Per Day</h3>
        <canvas id="barChart"></canvas>
    </div>
    
    <div class="chart-box">
        <h3>Sales Distribution</h3>
        <canvas id="doughnutChart"></canvas>
    </div>
</div>

<h2> Top selling products </h2>
<table class="table">
    <thead>
        <tr>
            <th> No </th>
            <th>Product Name</th>
            <th>Total Quantity Sold</th>
        </tr>
    </thead>
    <tbody>
        <?php  $no = 1;
         while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
            <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['total_sold'] ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 100%; margin: auto;">
    <h2>Current Month Sales</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product Name</th>
                <th>Cost Price</th>
                <th>Quantity Sold</th>
                <th>Total Profit</th>
                <th>Sale Date</th>
                <th>Salesperson</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Database connection
            include 'connect.php'; // Ensure this file contains your database connection

            $currentMonth = date('Y-m'); // Format: YYYY-MM (e.g., 2025-03)
            $query = "SELECT s.product_id, i.name AS product_name, i.cost_price, 
                 s.quantity, s.profit, s.sale_date, s.salesperson 
          FROM sales s
          JOIN drug_product i ON s.product_id = i.id
          WHERE DATE_FORMAT(s.sale_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
          ORDER BY s.sale_date DESC";

            $result = mysqli_query($conn, $query);

            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$no}</td>
                        <td>" . htmlspecialchars($row['product_name']) . "</td>
                        <td>{$row['cost_price']}</td>
                        
                        <td>{$row['quantity']}</td>
                        <td>{$row['profit']}</td>
                        <td>{$row['sale_date']}</td>
                        <td>" . htmlspecialchars($row['salesperson']) . "</td>
                      </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>
<!-- Sales Log Table -->
<div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); max-width: 100%; margin: auto;">
    <h2>Sales Log</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product Name</th>
                <th>Cost Price</th>
                <th>Quantity Sold</th>
                <th>Total Profit</th>
                <th>Sale Date</th>
                <th>Salesperson</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result_sales_log)) {
                echo "<tr>
                        <td>{$no}</td>
                        <td>" . htmlspecialchars($row['product_name']) . "</td>
                        <td>{$row['cost_price']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['profit']}</td>
                        <td>{$row['sale_date']}</td>
                        <td>" . htmlspecialchars($row['salesperson']) . "</td>
                      </tr>";
                $no++;
            }

            if ($no === 1) {
                echo "<tr><td colspan='7' style='text-align:center;'>No sales records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
        </div>



<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('get_sales_data.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.salesperson);
            const salesData = data.map(item => item.total_sales);

            // Bar Chart
            new Chart(document.getElementById("barChart"), {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total Sales per Salesperson",
                        data: salesData,
                        backgroundColor: "rgba(54, 162, 235, 0.6)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Doughnut Chart
            new Chart(document.getElementById("doughnutChart"), {
                type: "doughnut",
                data: {
                    labels: labels,
                    datasets: [{
                        data: salesData,
                        backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4CAF50", "#8E44AD"],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true
                }
            });
        })
        .catch(error => console.error("Error fetching sales data:", error));
});

</script>
</body>
</html>