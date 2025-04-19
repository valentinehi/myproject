<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sales_person') {
    header("Location: unauthorized.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="sale.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        

    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Sales Panel</h2>
        <a href="sales_dashboard.php?page=home"><i class="fas fa-home"></i> Home</a>
        <a href="sales_dashboard.php?page=start_sales"><i class="fas fa-cash-register"></i> Start Sales</a>
        <a href="sales_dashboard.php?page=sales_reversal"><i class="fas fa-undo"></i> Sales Reversal</a>
        <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to log out?');">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="logo">
                <img src="images/ABC.png" alt="Logo">
                <h1>Pharmacy Inventory Management System</h1>
            </div>
            <div class="dropdown">
                <button class="dropbtn">Salesperson</button>
                <div class="dropdown-content">
                    <a href="sales_profile.php">Profile</a>
                    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
        <?php
// Allowed pages
$allowed_pages = ['home', 'start_sales', 'reports', 'sales_reversal'];

// Set default page to 'home' if no valid page is provided
$page = isset($_GET['page']) && in_array($_GET['page'], $allowed_pages) ? $_GET['page'] : 'home';

// Ensure the file path matches your naming convention
$file_to_include = "sales_{$page}.php";

if (file_exists($file_to_include)) {
    include $file_to_include;
} else {
    echo "<p>Error: The page '{$file_to_include}' does not exist.</p>";
}
?>


        </div>
    </div>
    
</body>
</html>
