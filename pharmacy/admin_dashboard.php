<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit;
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    
    
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    display: flex;
    height: 100vh;
    overflow: auto; /* Ensure the body can scroll */
}

/* Custom scrollbar styling */
::-webkit-scrollbar {
    width: 8px; /* Width of the scrollbar */
    height: 8px; /* Height for horizontal scrollbar */
}

::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2); /* Thinner scrollbar thumb with semi-transparent color */
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.4); /* Darker on hover */
}

::-webkit-scrollbar-track {
    background-color: transparent; /* Transparent background */
}

/* Sidebar Navigation */
.sidebar {
    width: 200px;
    background-color: #000;
    color: #fff;
    padding: 20px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    position: fixed; /* Keeps the sidebar fixed on the screen */
    top: 70px; /* Below the header */
    left: 0;
    bottom: 0;
    z-index: 1000;
    overflow-y: auto; /* Allows scrolling in the sidebar */
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 18px;
    font-weight: bold;
}

.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    margin: 10px 0;
    padding: 10px 15px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.sidebar a:hover {
    background-color: #444;
}

.logout-link {
    margin-top: auto;
    text-align: center;
    background-color: red;
}

.logout-link:hover {
    background-color: darkred;
}

/* Main Content */
.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 200px; /* Matches the sidebar width */
    margin-top: 140px; /* Matches the header height */
    padding: 20px;
    box-sizing: border-box; /* Include padding in width calculations */
    overflow: auto; /* Allows scrolling for overflowing content */
    padding-left: 300px;
    height: calc(100vh - 140px); /* Ensure it takes up full height excluding the header */
}
.header-right {
    display: flex;
    align-items: center;
    gap: 20px; /* Adds space between notification and admin dropdown */
}


/* Header */
.dashboard-header {
    background-color: #007bff;
    color: white;
    position: relative;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    position: fixed; /* Stays at the top of the page */
    top: 0;
    left: 0;
    z-index: 1001; /* Above sidebar */
    margin-left: 0;
    
}

.dashboard-header .logo {
    display: flex;
    align-items: center;
}

.dashboard-header .logo img {
    width: 40px;
    height: 40px;
    margin-right: 10px;
}

.dashboard-header .logo h1 {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
}

.dashboard-header .dropdown {
    position: relative;
    display: inline-block;
}

.dashboard-header .dropdown .dropbtn {
    background: none;
    color: white;
    border: none;
    font-size: 16px;
    cursor: pointer;
}

.dashboard-header .dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 150px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 2000; /* Above header */
}

.dashboard-header .dropdown-content a {
    color: black;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
}

.dashboard-header .dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dashboard-header .dropdown:hover .dropdown-content {
    display: block;
}

/* Dashboard Content */
.dashboard-content {
    margin-left: 250px; /* Matches sidebar width */
    overflow: auto; /* Prevents overflow issues */
    margin-top: 100px;
}
/* Notification Bell */
.notification-dropdown {
    position: relative;
    display: inline-block;
    margin-right: 20px;
}

/* Bell icon */
.notification-icon {
    font-size: 24px;
    cursor: pointer;
    color: white; 
}

/* Notification count positioned above the bell */
.notification-count {
    position: absolute;
    top: -10px; /* Moves it slightly above the bell */
    right: -5px; /* Adjusts position to the right */
    font-size: 12px;
    font-weight: bold;
    color: white;
    background: red; /* Background color for visibility */
    padding: 3px 5px;
    border-radius: 20%; /* Makes it rectangle */
    display: inline-block;
}

/* Hide count if there are no notifications */
.notification-count.hidden {
    display: none;
}

/* Notification dropdown (hidden by default) */
.notification-content {
    display: none; /* Initially hidden */
    position: absolute;
    right: 0;
    background: white;
    min-width: 250px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1002;
    border-radius: 5px;
    overflow: hidden;
    padding: 10px;
}

/* Notification items */
.notification-content p {
    padding: 10px;
    margin: 0;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    color: #333;
}

.notification-content p:last-child {
    border-bottom: none;
}





/* Responsive Design */
@media screen and (max-width: 768px) {
    .sidebar {
        width: 100%; /* Sidebar becomes full-width */
        position: relative; /* Stacks with the content */
        top: 0; /* Reset positioning */
        height: auto; /* Auto-adjust height */
    }

    .main-content {
        margin-left: 0; /* No left margin */
        margin-top: 70px; /* Space below header */
    }
}

</style>
</head>
<body>
    <!-- Dynamic Content -->
    <div class="dashboard-content">
            <?php
            
             

// Allowed pages
$allowed_pages = ['home', 'inventory', 'add', 'add_drug', 'add_stock',  'sales', 'users', 'add_category', 'add_supplier'];

// If 'page' is set in the URL and is a valid page, use it; otherwise, default to 'home'
$page = isset($_GET['page']) && in_array($_GET['page'], $allowed_pages) ? $_GET['page'] : 'home';

// Now check if the page file exists
if (file_exists("{$page}.php")) {
    include "{$page}.php";
} else {
    echo "<p>Error: File '{$page}.php' does not exist.</p>";
}


            ?>
            
    
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php?page=home"> <i class="fas fa-home"></i>  Home</a>
        <a href="admin_dashboard.php?page=inventory"> <i class="fas fa-box"></i>  Inventory</a>
        <!-- Add Menu -->
    <a href="javascript:void(0);" onclick="toggleAddMenu()">
        <i class="fas fa-plus-circle"></i> Add <i class="fas fa-caret-down"></i>
    </a>
    <div id="add-options" style="display: none; padding-left: 20px;">
    <a href="admin_dashboard.php?page=add_category"><i class="fas fa-plus"></i> Add Category</a>
   
    <a href="admin_dashboard.php?page=add_supplier"><i class="fas fa-plus"></i> Add Supplier</a>

    </div>
        <a href="admin_dashboard.php?page=add_drug"> <i class="fas fa-plus"></i>  Add drug</a>
        <a href="admin_dashboard.php?page=add_stock"> <i class="fas fa-archive"></i>  Add stock</a>
        <a href="admin_dashboard.php?page=users"> <i class="fas fa-users"></i>  Users</a>
        <a href="admin_dashboard.php?page=sales"> <i class="fas fa-chart-line"></i>  Sales</a>

        <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to log out?');"><i class="fas fa-sign-out-alt"></i>Logout</a>

        
        <!-- JavaScript for Toggle Function -->
<script>
    function toggleAddMenu() {
        const addMenu = document.getElementById('add-options');
        addMenu.style.display = (addMenu.style.display === 'none' || addMenu.style.display === '') ? 'block' : 'none';
    }
    
</script>

        

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
        <div class="dashboard-header">
    <!-- Logo and Title -->
    <div class="logo">
        <img src="images/ABC.png" alt="Logo">
        <h1>Pharmacy Inventory Management System</h1>
    </div>

    <!-- Right Section (Notification + Admin Dropdown) -->
    <div class="header-right">
        <!-- Notification Bell -->
        <div class="notification-dropdown">
            <button class="notification-btn" id="notification-btn">
                <i class="notification-icon fas fa-bell"></i>
                <span class="notification-count" id="notification-count">0</span>
            </button>
            <div class="notification-content" id="notification-content">
                <p>Loading notifications...</p>
            </div>
        </div>

        <!-- Administrator Dropdown -->
        <div class="dropdown">
        <button class="dropbtn">
        Administrator <i class="fas fa-caret-down"></i>
    </button>
            <div class="dropdown-content">
                <a href="profile.php">Profile</a>
                <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
            </div>
        </div>
    </div>
</div>

        

        </div>
    


<!-- Include JavaScript file -->
<script src="script.js"></script>

<script>
// Function to load notifications dynamically
function loadNotifications() {
    fetch('fetch_notification.php')
        .then(response => response.json())
        .then(data => {
            const notificationCount = document.getElementById("notification-count");
            const notificationContent = document.getElementById("notification-content");

            // Update notification count
            if (data.total > 0) {
                notificationCount.textContent = data.total;
                notificationCount.classList.remove("hidden"); // Show count
            } else {
                notificationCount.classList.add("hidden"); // Hide if no notifications
            }

            // Populate notifications list
            notificationContent.innerHTML = ""; 
            if (data.notifications.length > 0) {
                data.notifications.forEach(item => {
                    let p = document.createElement("p");
                    p.textContent = item;
                    notificationContent.appendChild(p);
                });
            } else {
                notificationContent.innerHTML = "<p>No new notifications</p>";
            }
        })
        .catch(error => console.error("Error fetching notifications:", error));
}

// Toggle Notification Dropdown on click
document.getElementById("notification-btn").addEventListener("click", function(event) {
    event.stopPropagation(); // Prevents immediate closing
    let notificationContent = document.getElementById("notification-content");

    // Toggle visibility
    notificationContent.style.display = 
        (notificationContent.style.display === "block") ? "none" : "block";
});

// Close dropdown when clicking outside
document.addEventListener("click", function(event) {
    let notificationContent = document.getElementById("notification-content");
    let notificationButton = document.getElementById("notification-btn");

    if (!notificationButton.contains(event.target)) {
        notificationContent.style.display = "none";
    }
});

// Refresh notifications every 30 seconds
setInterval(loadNotifications, 30000);

// Load notifications on page load
window.onload = loadNotifications;

</script>



</body>
</html>