<?php
// Include the database connection
require_once 'connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supplier_name'])) {
    try {
        // Collect form data
        $supplierName = trim($_POST['supplier_name']);
        $productSupplied = trim($_POST['product_supplied']); // Name of the product
        $numberSupplied = intval($_POST['number_supplied']); // Entered in dispensable units
        $dateSupplied = $_POST['date_supplied'];
        $manufacturingDate = $_POST['manufacturing_date']; 
        $expiryDate = $_POST['expiry_date']; 
        $baseUnit = trim($_POST['base_unit']); 
        $smallestUnit = trim($_POST['smallest_unit']);  // Smallest unit (e.g., tablet, capsule)
        
        error_log("Received: Supplier - $supplierName, Product - $productSupplied, Units - $numberSupplied");

        // Retrieve the product details from the database
        $get_product_details = $conn->prepare("SELECT id, conversion_factor, base_unit, smallest_unit FROM drug_product WHERE name = ?");
        $get_product_details->bind_param("s", $productSupplied);
        $get_product_details->execute();
        $result = $get_product_details->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $productID = $row['id']; 
            $dbConversionFactor = floatval($row['conversion_factor']); 
            $dbBaseUnit = $row['base_unit'];
            $dbSmallestUnit = $row['smallest_unit'];

            error_log("Product Found: ID - $productID, Conversion Factor - $dbConversionFactor");

            // Validate base unit
            if ($baseUnit !== $dbBaseUnit) {
                echo json_encode(["status" => "error", "message" => "Base unit mismatch!"]);
                exit;
            }

            // Validate smallest unit
            if ($smallestUnit !== $dbSmallestUnit) {
                echo json_encode(["status" => "error", "message" => "Smallest unit mismatch!"]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid product name!"]);
            exit;
        }

        // Convert dispensable units to bulk units using the conversion factor
        $bulkQuantity = floatval($numberSupplied); // Assuming the input is already in base units

        error_log("Calculated bulkQuantity: $bulkQuantity");

        // Insert into stock table
        $sql = "INSERT INTO stock (supplier_name, product_supplied, number_supplied, date_supplied, manufacturing_date, expiry_date, base_unit, smallest_unit, conversion_factor) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisssssd", $supplierName, $productSupplied, $numberSupplied, $dateSupplied, $manufacturingDate, $expiryDate , $baseUnit, $smallestUnit, $dbConversionFactor);

        if ($stmt->execute()) {
            error_log("Stock successfully inserted.");
        
            // Check if the product exists in inventory
            $check_inventory = $conn->prepare("SELECT quantity FROM inventory WHERE drug_id = ? AND type = 1");
            $check_inventory->bind_param("i", $productID);
            $check_inventory->execute();
            $result_inventory = $check_inventory->get_result();
        
            if ($result_inventory->num_rows == 0) {
                // If not found, insert new inventory record with bulk units (without manufacturing_date & expiry_date)
                $insert_inventory = $conn->prepare("INSERT INTO inventory (drug_id, quantity, type) VALUES (?, ?, 1)");
                $insert_inventory->bind_param("id", $productID, $bulkQuantity);
                if ($insert_inventory->execute()) {
                    error_log("New inventory record inserted.");
                } else {
                    error_log("Error inserting inventory: " . $insert_inventory->error);
                }
            } else {
                // If found, update inventory quantity using bulk units (without manufacturing_date & expiry_date)
                error_log("Adding stock: bulkQuantity=$bulkQuantity, Existing Quantity={GET FROM DB}");
                $update_inventory = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE drug_id = ?");
                $update_inventory->bind_param("di", $bulkQuantity, $productID);
                
                if ($update_inventory->execute()) {
                    error_log("Inventory successfully updated. Added: $bulkQuantity");
                } else {
                    error_log("Error updating inventory: " . $update_inventory->error);
                }
            }
        
            echo json_encode(["status" => "success", "message" => "Stock added and inventory updated successfully!"]);
        } else {
            error_log("Database error: " . $stmt->error);
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }

    } catch (Exception $e) {
        error_log("Server error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    }
    exit;
}
?>




<!-- Include SweetAlert and Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<!-- Add Stock Button (left side) -->
<div class="d-flex justify-content-end mb-3">
<button class="btn btn-primary" id="addStockBtn" data-bs-toggle="modal" data-bs-target="#addStockModal" style="padding: 10px 20px; width: auto;">
    Add Stock
</button>
</div>

<!-- Modal for Adding Stock -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStockForm">
                    <div class="mb-3">
                        <label for="supplierName" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="supplierName" name="supplier_name" placeholder="Enter supplier name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productSupplied" class="form-label">Product Supplied</label>
                        <input type="text" class="form-control" id="productSupplied" name="product_supplied" placeholder="Enter product supplied" required>
                    </div>
                    <div class="mb-3">
                        <label for="numberSupplied" class="form-label">Number Supplied</label>
                        <input type="number" class="form-control" id="numberSupplied" name="number_supplied" placeholder="Enter number supplied" required>
                    </div>
                    <div class="mb-3">
                        <label for="dateSupplied" class="form-label">Date Supplied</label>
                        <input type="date" class="form-control" id="dateSupplied" name="date_supplied" required>
                    </div>
                    <div class="mb-3">
                        <label for="manufacturingDate" class="form-label">Manufacturing Date</label>
                        <input type="date" class="form-control" id="manufacturingDate" name="manufacturing_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiryDate" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiryDate" name="expiry_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="baseUnit" class="form-label">Base Unit</label>
                        <select class="form-control" id="baseUnit" name="base_unit" required>
                            <option value="bottle">Bottle</option>
                            <option value="vial">Vial</option>
                            <option value="tube">Tube</option>
                            <option value="strip">Strip</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="smallestUnit" class="form-label">Smallest Unit</label>
                        <select class="form-control" id="smallestUnit" name="smallest_unit" required>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="pill">Pill</option>
                            <option value="ml">Mililiter</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="conversionFactor" class="form-label">Conversion Factor</label>
                        <input type="number" class="form-control" id="conversionFactor" name="conversion_factor" placeholder="Enter conversion factor" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="table-responsive mt-3">
    <table class="table table-bordered" id="stockTable">
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Product Supplied</th>
                <th>Number Supplied</th>
                <th>Date Supplied</th>
                <th>Manufacturing Date</th>
                <th>Expiry Date</th>
                <th>Base Unit</th>
                <th>Smallest Unit</th>
                <th>Conversion Factor</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch existing stock from the database
            $sql = "SELECT id, supplier_name, product_supplied, number_supplied, date_supplied, manufacturing_date, expiry_date, base_unit, smallest_unit, conversion_factor FROM stock";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['supplier_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['product_supplied']) . "</td>";
                echo "<td>" . htmlspecialchars($row['number_supplied']) . "</td>";
                echo "<td>" . htmlspecialchars($row['date_supplied']) . "</td>";
                echo "<td>" . htmlspecialchars($row['manufacturing_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['expiry_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['base_unit']) . "</td>";
                echo "<td>" . htmlspecialchars($row['smallest_unit']) . "</td>";
                echo "<td>" . htmlspecialchars($row['conversion_factor']) . "</td>";

                // Edit button in its own column
                echo "<td class='text-center'>
                        <button class='btn btn-success editBtn' 
                                data-id='" . $row['id'] . "' 
                                data-supplier_name='" . htmlspecialchars($row['supplier_name']) . "'
                                data-product_supplied='" . htmlspecialchars($row['product_supplied']) . "'
                                data-number_supplied='" . htmlspecialchars($row['number_supplied']) . "'
                                data-date_supplied='" . htmlspecialchars($row['date_supplied']) . "'
                                data-manufacturing_date='" . htmlspecialchars($row['manufacturing_date']) . "'
                                data-expiry_date='" . htmlspecialchars($row['expiry_date']) . "'
                                data-base_unit='" . htmlspecialchars($row['base_unit']) . "'
                                data-smallest_unit='" . htmlspecialchars($row['smallest_unit']) . "'
                                data-conversion_factor='" . htmlspecialchars($row['conversion_factor']) . "'
                                data-toggle='modal' 
                                data-target='#editModal'>
                            <i class='fas fa-edit'></i>
                        </button>
                      </td>";
            
          


                // Delete button in its own column
                echo "<td class='text-center'>
                        <button class='btn btn-danger deleteBtn' data-id='" . $row['id'] . "'>
                            <i class='fas fa-trash'></i>
                        </button>
                      </td>";

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editStockForm">
                    <input type="hidden" id="editStockId" name="id">
                    
                    <div class="mb-3">
                        <label for="editSupplierName" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" id="editSupplierName" name="supplier_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProductSupplied" class="form-label">Product Supplied</label>
                        <input type="text" class="form-control" id="editProductSupplied" name="product_supplied" required>
                    </div>
                    <div class="mb-3">
                        <label for="editNumberSupplied" class="form-label">Number Supplied</label>
                        <input type="number" class="form-control" id="editNumberSupplied" name="number_supplied" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDateSupplied" class="form-label">Date Supplied</label>
                        <input type="date" class="form-control" id="editDateSupplied" name="date_supplied" required>
                    </div>
                    <div class="mb-3">
                        <label for="editManufacturingDate" class="form-label">Manufacturing Date</label>
                        <input type="date" class="form-control" id="editManufacturingDate" name="manufacturing_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editExpiryDate" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="editExpiryDate" name="expiry_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editBaseUnit" class="form-label">Base Unit</label>
                        <select class="form-control" id="editBaseUnit" name="base_unit" required>
                            <option value="bottle">Bottle</option>
                            <option value="vial">Vial</option>
                            <option value="tube">Tube</option>
                            <option value="strip">Strip</option>
                            <!-- Add more units as necessary -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editSmallestUnit" class="form-label">Smallest Unit</label>
                        <select class="form-control" id="editSmallestUnit" name="smallest_unit" required>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="pill">Pill</option>
                            <option value="ml">ml</option>
                            <!-- Add more smallest units as necessary -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editConversionFactor" class="form-label">Conversion Factor</label>
                        <input type="number" class="form-control" id="editConversionFactor" name="conversion_factor" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Handle Edit Button
    $('#stockTable').on('click', '.editBtn', function () {
        const stockId = $(this).data('id');
        
        // Fetch stock data to populate in the modal
        fetch(`edit_stock.php?id=${stockId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.stock) {
                    $('#editStockId').val(data.stock.id);
                    $('#editSupplierName').val(data.stock.supplier_name);
                    $('#editProductSupplied').val(data.stock.product_supplied);
                    $('#editNumberSupplied').val(data.stock.number_supplied);
                    $('#editDateSupplied').val(data.stock.date_supplied);
                    $('#editManufacturingDate').val(data.stock.manufacturing_date); // New field
                    $('#editExpiryDate').val(data.stock.expiry_date); // New field
                    $('#editBaseUnit').val(data.stock.base_unit);
                    $('#editSmallestUnit').val(data.stock.smallest_unit);
                    $('#editConversionFactor').val(data.stock.conversion_factor);
                    $('#editModal').modal('show');
                } else {
                    Swal.fire('Error', 'Failed to fetch stock details', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while fetching stock data', 'error');
            });
    });

    // Handle Delete Button
    $('#stockTable').on('click', '.deleteBtn', function () {
        const stockId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform delete operation
                fetch('delete_stock.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ 'delete_stock': 1, 'stock_id': stockId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            location.reload(); // Reload the page after deletion
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'An error occurred while deleting the stock', 'error');
                });
            }
        });
    });

    // Handle Edit Stock Form Submission
    $('#editStockForm').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(this); // Collect form data
        // Debugging: Log form data before sending
        console.log("Form Data:");
        for (let pair of formData.entries()) {
            console.log(pair[0] + ": " + pair[1]);
        }

        fetch('edit_stock.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data); // Debugging

            if (data.status === 'success') {
                Swal.fire('Success', data.message, 'success').then(() => {
                    location.reload(); // Reload the page after successful update
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Update Error:', error);
            Swal.fire('Error', 'An unexpected error occurred while updating stock', 'error');
        });
    });
});
</script>
