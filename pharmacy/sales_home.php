<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connect.php'; // Ensure database connection

// Fetch Overall Total Sales
$totalSalesQuery = "SELECT SUM(selling_price * quantity) AS total_sales FROM sales";
$totalSalesResult = $conn->query($totalSalesQuery);
$totalSales = $totalSalesResult->fetch_assoc()['total_sales'] ?? 0;

// Fetch Total Sales Today
$today = date("Y-m-d");
$todaySalesQuery = "SELECT SUM(selling_price * quantity) AS today_sales FROM sales WHERE sale_date = '$today'";
$todaySalesResult = $conn->query($todaySalesQuery);
$todaySales = $todaySalesResult->fetch_assoc()['today_sales'] ?? 0;

// Fetch Expired Products Count
$expiredQuery = "SELECT COUNT(*) AS expired_count FROM expired_product WHERE date_expired < CURDATE()";
$expiredResult = $conn->query($expiredQuery);
$expiredProducts = $expiredResult->fetch_assoc()['expired_count'] ?? 0;

// Fetch Sales This Month
$monthStart = date("Y-m-01");
$thisMonthSalesQuery = "SELECT SUM(selling_price * quantity) AS month_sales FROM sales WHERE sale_date >= '$monthStart'";
$thisMonthSalesResult = $conn->query($thisMonthSalesQuery);
$thisMonthSales = $thisMonthSalesResult->fetch_assoc()['month_sales'] ?? 0;

// Fetch Sales Log Data
$salesLogQuery = "SELECT id, product_id, quantity, selling_price, sale_date FROM sales ORDER BY sale_date DESC LIMIT 10";
$salesLogResult = $conn->query($salesLogQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <link rel="stylesheet" href="sale.css"> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Ensure jQuery is loaded -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="home-container">
        <!-- Timer Card -->
        <div class="card timer-card">
            <h2>Current Time</h2>
            <p id="live-time">00:00:00</p>
        </div>

        <!-- Welcome Card -->
        <div class="card welcome-card">
            <h2>Welcome, <?php echo $_SESSION['firstName'] ?? 'Salesperson'; ?>!</h2>
            <p>Welcome to the Sales Dashboard. Use the navigation to manage sales and reports.</p>
        </div>
    </div>

    <!-- Sales Dashboard Cards -->
    <div class="sales-cards">
        <!-- Card 1: Overall Total Sales -->
        <div class="card sales-card total-sales">
            <i class="fas fa-dollar-sign"></i>
            <h3>Overall Total Sales</h3>
            <p>Ksh <?php echo number_format($totalSales, 2); ?></p>
        </div>

        <!-- Card 2: Total Sales Today -->
        <div class="card sales-card total-sales-today">
            <i class="fas fa-calendar-day"></i>
            <h3>Total Sales Today</h3>
            <p>Ksh <?php echo number_format($todaySales, 2); ?></p>
        </div>

        <!-- Card 3: Expired Products -->
        <div class="card sales-card expired-products">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Expired Products</h3>
            <p><?php echo $expiredProducts; ?> Products</p>
        </div>

        <!-- Card 4: Sales This Month -->
        <div class="card sales-card sales-this-month">
            <i class="fas fa-calendar-alt"></i>
            <h3>Sales This Month</h3>
            <p>Ksh <?php echo number_format($thisMonthSales, 2); ?></p>
        </div>
    </div>

    <!-- Sales Log Card -->
    <div class="card sales-log-card">
        <h2>Sales Log</h2>
        
        <!-- Search Query -->
        <div class="search-container">
            <input type="text" id="searchQuery" placeholder="Search by Order ID, Product Name, or Salesperson">
        </div>

        <!-- Sales Table -->
        <table id="salesTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Sales Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $salesLogResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>Ksh <?php echo number_format($row['selling_price'], 2); ?></td>
                        <td>Ksh <?php echo number_format($row['selling_price'] * $row['quantity'], 2); ?></td>
                        <td><?php echo $row['sale_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <button id="prevPage" onclick="prevPage()">Prev</button>
            <button id="nextPage" onclick="nextPage()">Next</button>
        </div>
    </div>

    <script>
        // Live Time Update
        function updateTime() {
            let now = new Date();
            let timeString = now.toLocaleTimeString();
            document.getElementById("live-time").textContent = timeString;
        }
        setInterval(updateTime, 1000);

        // Search Functionality
        $(document).ready(function() {
            $("#searchQuery").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#salesTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });

        // Pagination Logic (For Future)
        function prevPage() { alert("Previous Page Clicked"); }
        function nextPage() { alert("Next Page Clicked"); }
    </script>
</body>
</html>
