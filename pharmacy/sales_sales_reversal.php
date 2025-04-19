<?php

include 'connect.php'; // Database connection

$current_user = $_SESSION['firstName']; // Logged-in user
$user_role = $_SESSION['role']; // User role (admin or salesperson)

if ($user_role === 'admin') {
    // Admins see all reversal requests
    $query = "SELECT s.id AS sale_id, s.product_id, i.name AS product_name, 
                     s.quantity, s.selling_price, s.customer_name, s.sale_date, 
                     s.salesperson, sr.status
              FROM sales s
              JOIN inventory i ON s.product_id = i.drug_id
              LEFT JOIN sales_reversals sr ON s.id = sr.sale_id
              ORDER BY s.sale_date DESC";
} else {
    // Salespersons only see their own reversal requests
    $query = "SELECT s.id AS sale_id, s.product_id, i.name AS product_name, 
                     s.quantity, s.selling_price, s.customer_name, s.sale_date, 
                     s.salesperson, sr.status
              FROM sales s
              JOIN inventory i ON s.product_id = i.drug_id
              LEFT JOIN sales_reversals sr ON s.id = sr.sale_id
              WHERE s.salesperson = ?
              ORDER BY s.sale_date DESC";
    
    // Prepare the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $current_user);
    $stmt->execute();
    $result = $stmt->get_result();
} 

if (!isset($result)) {
    $result = mysqli_query($conn, $query); // For admin case
}


echo "<table border='1'>
<tr>
<th>Product ID</th>
<th>Quantity</th>
<th>Price</th>
<th>Customer</th>
<th>Date</th>
<th>Salesperson</th>
<th>Status</th>
<th>Action</th>
</tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['product_id'] ?? 'N/A') . "</td>";
    echo "<td>" . $row['quantity'] . "</td>";
    echo "<td>Ksh " . $row['selling_price'] . "</td>";
    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
    echo "<td>" . $row['sale_date'] . "</td>";
    echo "<td>" . htmlspecialchars($row['salesperson']) . "</td>";
    
    // Display status (default to Pending if no record found)
    $status = $row['status'] ?? 'Not Requested';
    echo "<td id='status-{$row['sale_id']}'>" . htmlspecialchars($status) . "</td>";

    // Show appropriate button based on status
    if ($status == 'Pending') {
        echo "<td><button class='btn btn-warning btn-sm' disabled>Pending</button></td>";
    } elseif ($status == 'Approved') {
        echo "<td>
            <button class='btn btn-success btn-sm' disabled>Approved</button> 
            <button class='btn btn-danger btn-sm' onclick='deleteReversal({$row['sale_id']})'>Delete</button>
        </td>";
    } elseif ($status == 'Rejected') {
        echo "<td>
            <button class='btn btn-danger btn-sm' disabled>Rejected</button> 
            <button class='btn btn-dark btn-sm' onclick='deleteReversal({$row['sale_id']})'>Delete</button>
        </td>";
    } else {
        echo "<td>
            <button class='btn btn-primary btn-sm' onclick=\"openReversalForm({$row['sale_id']}, {$row['product_id']}, {$row['quantity']}, '{$row['salesperson']}')\">Request Reversal</button>
        </td>";
    }
    
    echo "</tr>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reversal</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap JavaScript (optional, but needed for modals, tooltips, etc.) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 (Already included in your code) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>


<div id="reversal-form" style="display:none; position:fixed; top:30%; left:50%; transform:translate(-50%,-30%); padding:20px; background:white; border:1px solid black;">
    <h3>Request Sales Reversal</h3>
    <form id="reversalRequestForm">
        <input type="hidden" id="sale_id" name="sale_id">
        <input type="hidden" id="product_id" name="product_id">
        <input type="hidden" id="quantity" name="quantity">
        <input type="hidden" id="requested_by" name="requested_by">

        <label for="reason">Reason:</label><br>
        <textarea id="reason" name="reason" required></textarea><br><br>

        <button type="button" id="submitReversalBtn" onclick="submitReversalRequest()">Submit Request</button>
        <button type="button" onclick="closeReversalForm()">Cancel</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert Library -->
<script>
function openReversalForm(saleId, productId, quantity, salesperson) {
    document.getElementById('sale_id').value = saleId;
    document.getElementById('product_id').value = productId; 
    document.getElementById('quantity').value = quantity;
    document.getElementById('requested_by').value = salesperson;

    document.getElementById('reversal-form').style.display = 'block'; // Show form
}


function closeReversalForm() {
    document.getElementById('reversal-form').style.display = 'none';
}
function submitReversalRequest() {
    console.log("Submit button clicked");

    let saleId = document.getElementById('sale_id').value;
    let productId = document.getElementById('product_id').value;
    let quantity = document.getElementById('quantity').value;
    let reason = document.getElementById('reason').value.trim();

    if (!saleId || !productId || !quantity || reason === '') {
        Swal.fire("Error!", "All fields are required.", "error");
        return;
    }

    console.log("Captured Data:", { saleId, productId, quantity, reason });

    fetch('submit_reversal.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            sale_id: saleId,
            product_id: productId,  //  Corrected from drugId
            quantity: quantity,
            reason: reason
        })
    })
    .then(response => response.text())  // Change .json() to .text() for debugging
    .then(data => {
        console.log("Raw Response from PHP:", data);
        try {
            let jsonData = JSON.parse(data); // Try parsing JSON
            console.log("Parsed JSON:", jsonData);
            if (jsonData.status === 'success') {
                Swal.fire("Success!", "Reversal request submitted successfully.", "success");
            } else {
                Swal.fire("Error!", jsonData.message, "error");
            }
        } catch (error) {
            console.error("JSON Parse Error:", error, data);
            Swal.fire("Error!", "Invalid response from server. Check console for details.", "error");
        }
    })
    .catch(error => {
        console.error("Fetch error:", error);
        Swal.fire("Error!", "Something went wrong. Please try again.", "error");
    });
}
function checkNotifications() {
    fetch('fetch_notifications.php')
    .then(response => response.json())
    .then(data => {
        if (data.unread > 0) {
            Swal.fire({
                title: "Reversal Update",
                text: data.message,
                icon: data.status === "Approved" ? "success" : "error"
            });

            // Mark notifications as read after displaying
            fetch('mark_notifications_read.php', { method: 'POST' });
        }
    })
    .catch(error => console.error("Error fetching notifications:", error));
}

// Check for notifications every 10 seconds
setInterval(checkNotifications, 10000);


function deleteReversal(saleId) {
    Swal.fire({
        title: "Are you sure?",
        text: "This reversal request will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "delete_reversals.php",
                type: "POST",
                data: { sale_id: saleId },
                success: function (response) {
                    try {
                        let jsonResponse = JSON.parse(response);
                        if (jsonResponse.status === "success") {
                            Swal.fire("Deleted!", jsonResponse.message, "success");
                            location.reload(); // Refresh page
                        } else {
                            Swal.fire("Error!", jsonResponse.message, "error");
                        }
                    } catch (error) {
                        console.error("JSON Parse Error:", error, response);
                        Swal.fire("Error!", "Invalid response from server.", "error");
                    }
                },
                error: function () {
                    Swal.fire("Error!", "Failed to send delete request.", "error");
                }
            });
        }
    });
}


</script>
</body>
</html>

    