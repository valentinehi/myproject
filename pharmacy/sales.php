<?php
include 'connect.php';
$query = "SELECT sr.id, sr.sale_id, sr.product_id, sr.quantity, sr.reason, sr.status, s.customer_name, s.sale_date 
          FROM sales_reversals sr 
          JOIN sales s ON sr.sale_id = s.id ORDER BY s.sale_date DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reversal Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        thead {
            background-color: white !important;
        }
        .btn-reject {
            background-color: #ffcccb !important;
            color: black !important;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Sales Reversal Requests</h2>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['sale_id']) ?></td>
                        <td><?= htmlspecialchars($row['product_id']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['sale_date']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] == 'Pending') { ?>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-success btn-sm" onclick="approveReversal(<?= $row['id'] ?>)">Approve</button>
                                    <button class="btn btn-reject btn-sm" onclick="rejectReversal(<?= $row['id'] ?>)">Reject</button>
                                </div>
                            <?php } else { ?>
                                <button class="btn btn-danger btn-sm mt-2" onclick="deleteReversal(<?= $row['id'] ?>)">Delete</button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
    function approveReversal(reversalId) {
        Swal.fire({
            title: "Are you sure?",
            text: "You are about to approve this reversal request.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, approve!"
        }).then((result) => {
            if (result.isConfirmed) {
                processReversal(reversalId, "approve");
            }
        });
    }

    function rejectReversal(reversalId) {
        Swal.fire({
            title: "Are you sure?",
            text: "You are about to reject this reversal request.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ffcccb",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, reject!"
        }).then((result) => {
            if (result.isConfirmed) {
                processReversal(reversalId, "reject");
            }
        });
    }

    function deleteReversal(reversalId) {
        Swal.fire({
            title: "Are you sure?",
            text: "This action will permanently delete the reversal request!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                processReversal(reversalId, "delete");
            }
        });
    }

    function processReversal(reversalId, action) {
        fetch("process_reversal.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `reversal_id=${reversalId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire("Success!", "Action completed successfully.", "success").then(() => {
                    location.reload();
                });
            } else {
                Swal.fire("Error!", data.message, "error");
            }
        })
        .catch(error => console.error("Error:", error));
    }
    </script>
</body>
</html>
