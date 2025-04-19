<?php
// Connect to the database
include 'connect.php';

// Fetch all suppliers
$suppliers = $conn->query("SELECT * FROM suppliers");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adding a new supplier
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $created_at = date('Y-m-d H:i:s');

    if (!empty($name) && !empty($address) && !empty($phone)) {
        // Find the smallest unused ID
        $result = $conn->query("SELECT MIN(t1.id + 1) AS next_id FROM suppliers t1 
                                LEFT JOIN suppliers t2 ON t1.id + 1 = t2.id 
                                WHERE t2.id IS NULL");
        $row = $result->fetch_assoc();
        $next_id = $row['next_id'] ?? 1; // Default to 1 if no gaps exist

        // Insert the supplier with the manually specified ID
        $stmt = $conn->prepare("INSERT INTO suppliers (id, name, address, phone, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $next_id, $name, $address, $phone, $created_at);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Supplier added successfully!',
                'data' => [
                    'id' => $next_id,
                    'name' => $name,
                    'address' => $address,
                    'phone' => $phone
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error adding supplier: ' . $stmt->error
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert CDN -->
    
</head>
<body>

<div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-primary w-auto" data-toggle="modal" data-target="#addSupplierModal">
            Add Supplier
        </button>
</div>


<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Supplier Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter supplier name" required>
                    </div>
                    <div class="form-group">
                        <label for="address" class="font-weight-bold">Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Enter supplier address" required>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="font-weight-bold">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter supplier phone number" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Supplier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Existing Suppliers Table -->
<h4 class="mt-4"><b>Existing Suppliers</b></h4>
<?php if ($suppliers->num_rows > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $suppliers->fetch_assoc()): ?>
                <tr id="supplier-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>

                    <!-- Edit Button -->
                    <td class="text-center">
                        <button class="btn btn-sm btn-success edit-supplier" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                data-address="<?php echo htmlspecialchars($row['address']); ?>" 
                                data-phone="<?php echo htmlspecialchars($row['phone']); ?>" 
                                data-toggle="modal" 
                                data-target="#editSupplierModal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>

                    <!-- Delete Button -->
                    <td class="text-center">
                        <button class="btn btn-sm btn-danger delete-supplier" data-id="<?php echo $row['id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No suppliers found.</p>
<?php endif; ?>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Edit Supplier Form -->
                <form id="editSupplierForm" method="POST">
                    <input type="hidden" name="id" id="supplier_id">

                    <div class="form-group">
                        <label for="supplier_name">Supplier Name:</label>
                        <input type="text" name="name" id="supplier_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="supplier_address">Supplier Address:</label>
                        <input type="text" name="address" id="supplier_address" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="supplier_phone">Supplier Phone:</label>
                        <input type="text" name="phone" id="supplier_phone" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="script.js"></script>
</body>
</html>