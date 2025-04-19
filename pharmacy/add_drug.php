<?php
require_once 'connect.php';

// Handle form submission for adding a drug
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_drug'])) {
        // Collect form data for adding a drug
        $drugName = trim($_POST['drug_name']);
        $costPrice = trim($_POST['cost_price']);
    
        $category = trim($_POST['category']);
        $sellingPrice = trim($_POST['selling_price']);
        $baseUnit = $_POST['base_unit'] ?? ''; 
        $smallestUnit = $_POST['smallest_unit'] ?? ''; 
        $conversionFactor = isset($_POST['conversion_factor']) ? intval($_POST['conversion_factor']) : 1;

        $imageName = $_FILES['image']['name'];

        // Handle image upload
        $targetDir = "uploads/";
        $imageName = basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $imageName;
        $imageType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validate image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageType, $allowedTypes)) {
            echo json_encode(["status" => "error", "message" => "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed."]);
            exit;
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            // Insert drug data into drug_product table (without quantity)
            $sql = "INSERT INTO drug_product (name, image, cost_price, category, selling_price, base_unit, smallest_unit, conversion_factor, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsdsss", $drugName, $imageName, $costPrice, $category, $sellingPrice, $baseUnit, $smallestUnit, $conversionFactor);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Drug added successfully!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        }
        exit;
    }




    // Handle form submission for editing drug
if (isset($_POST['edit_product'])) {
    // Collect form data for editing drug
    $drugId = $_POST['id'];
    $drugName = trim($_POST['name']);
    $costPrice = trim($_POST['cost_price']);
    
    $category = trim($_POST['category']);
    $sellingPrice = trim($_POST['selling_price']);

    $imageColumn = "";
    $imageValue = "";

    // If a new image is uploaded, handle the file upload
    if ($_FILES['image']['name']) {
        $imageName = $_FILES['image']['name'];
        $targetDir = "uploads/";
        $targetFilePath = $targetDir . basename($imageName);
        $imageType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validate image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($imageType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $imageColumn = ", image = ?";
                $imageValue = $imageName;
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
                exit;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed."]);
            exit;
        }
    }

    // Update drug in database (without quantity)
    $sql = "UPDATE drug_product 
            SET name = ?, cost_price = ?, category = ?, selling_price = ? $imageColumn 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);

    if (!empty($imageColumn)) {
        $stmt->bind_param("sdsdi", $drugName, $costPrice,  $category, $sellingPrice, $imageValue, $drugId);
    } else {
        $stmt->bind_param("sdsdi", $drugName, $costPrice,  $category, $sellingPrice, $drugId);
    }

    // Execute the statement and handle the result
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Drug updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }
    exit;
}
}

    
    

// Fetch existing drugs for listing
$sql = "SELECT * FROM drug_product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Management</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Drug Management</h2>
        <!-- Add Drug Button -->
   
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary w-auto" data-toggle="modal" data-target="#addDrugModal" style="padding: 10px 20px;">
        Add Drug
    </button>
    
</div>



        <!-- Drug List Table -->
        <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Image</th>
            <th>Cost Price</th>
            
         
            <th>Category</th>
            <th>Selling Price</th>
            <th>Base Unit</th> 
            <th>Smallest Unit</th> 
            <th>Conversion Factor</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td> 
                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" 
                             alt="Product Image" 
                             style="width:50px; height:50px;"> 
                    </td>
                    <td><?php echo htmlspecialchars($row['cost_price']); ?></td>
                    
                   
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['selling_price']); ?></td>
                    <td><?php echo htmlspecialchars($row['base_unit']); ?></td>
                    <td><?php echo htmlspecialchars($row['smallest_unit']); ?></td>
                    <td><?php echo htmlspecialchars($row['conversion_factor']); ?></td>

                    <!-- Edit Button -->
                    <td class="text-center">
                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#editDrugModal" 
                                data-id="<?php echo $row['id']; ?>"
                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                data-cost_price="<?php echo htmlspecialchars($row['cost_price']); ?>"
                                
                                
                                data-category="<?php echo htmlspecialchars($row['category']); ?>"
                                data-selling_price="<?php echo htmlspecialchars($row['selling_price']); ?>"
                                data-image="<?php echo htmlspecialchars($row['image']); ?>"
                                data-base_unit="<?php echo htmlspecialchars($row['base_unit']); ?>" 
                                data-smallest_unit="<?php echo htmlspecialchars($row['smallest_unit']); ?>"
                                data-conversion_factor="<?php echo htmlspecialchars($row['conversion_factor']); ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>

                    <!-- Delete Button -->
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $row['id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="14">No drugs found.</td> <!-- Adjusted colspan for new columns -->
            </tr>
        <?php endif; ?>
    </tbody>
</table>





        <!-- Add Drug Modal -->
<div class="modal fade" id="addDrugModal" tabindex="-1" role="dialog" aria-labelledby="addDrugModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDrugModalLabel">Add Drug</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addDrugForm" enctype="multipart/form-data">
                    <input type="hidden" name="add_drug" value="1">
                    
                    <!-- Drug Name -->
                    <div class="form-group">
                        <label for="drugName">Drug Name</label>
                        <input type="text" class="form-control" id="drugName" name="drug_name" placeholder="Enter drug name" required>
                    </div>

                    <!-- Drug Image -->
                    <div class="form-group">
                        <label for="drugImage">Drug Image</label>
                        <input type="file" class="form-control" id="drugImage" name="image" required>
                    </div>

                    <!-- Cost Price -->
                    <div class="form-group">
                        <label for="costPrice">Cost Price</label>
                        <input type="number" class="form-control" id="costPrice" name="cost_price" step="0.01" placeholder="Enter cost price" required>
                    </div>

                   

                   

                    <div class="form-group">
                    <label for="editCategory">Category</label>
                    <select id="editCategory" name="category" class="form-control">
                    <option value="">Select Category</option>
                    <option value="pain killer">Pain killer</option>
                    <option value="antibiotic">Antibiotic</option>
                    <option value="dermatology">Dermatology</option>
                    <option value="respiratory">Respiratory</option>
                    <option value="Sedative">Sedative</option>
                    <option value="antihistamine">Antihistamine</option>
                    <option value="cough suppressant">Cough suppressant</option>
                    <option value="supplement">supplement</option>
                    <option value="tube">Tube</option>
                    <option value="Inhaler">Inhaler</option>
                    <option value="vial">Vial</option>
                    
                   </select>
                  </div>


                    <!-- Selling Price -->
                    <div class="form-group">
                        <label for="sellingPrice">Selling Price</label>
                        <input type="number" class="form-control" id="sellingPrice" name="selling_price" step="0.01" placeholder="Enter selling price" required>
                    </div>

                    <!-- Base Unit -->
                    <div class="form-group">
                        <label for="baseUnit">Base Unit</label>
                        <select name="base_unit" id="baseUnit" class="form-control" required>
                            <option value="">Select Base Unit</option>
                            <option value="bottle">Bottle</option>
                            <option value="box">Box</option>
                            <option value="strip">Strip</option>
                            <option value="vial">Vial</option>
                            <option value="tube">Tube</option>
                        </select>
                    </div>

                    <!-- Smallest Unit -->
                    <div class="form-group">
                        <label for="smallestUnit">Smallest Unit</label>
                        <select name="smallest_unit" id="smallestUnit" class="form-control" required>
                            <option value="">Select Smallest Unit</option>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="g">Gram (g)</option>
                            <option value="patch">Patch</option>
                        </select>
                    </div>

                    <!-- Conversion Factor -->
                    <div class="form-group">
                        <label for="conversionFactor">Conversion Factor</label>
                        <input type="number" class="form-control" id="conversionFactor" name="conversion_factor" placeholder="How many smallest units in base unit?" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Drug</button>
                </form>
            </div>
        </div>
    </div>
</div>

       


       <!-- Edit Drug Modal -->
       <div class="modal fade" id="editDrugModal" tabindex="-1" role="dialog" aria-labelledby="editDrugModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDrugModalLabel">Edit Drug</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editDrugForm" enctype="multipart/form-data">
                    <input type="hidden" name="edit_product" value="1">
                    <input type="hidden" name="id" id="editDrugId">

                    <div class="form-group">
                        <label for="editDrugName">Drug Name</label>
                        <input type="text" class="form-control" id="editDrugName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="editCostPrice">Cost Price</label>
                        <input type="number" class="form-control" id="editCostPrice" name="cost_price" step="0.01" required>
                    </div>
                   

                    <!-- Base Unit Dropdown -->
                    <div class="form-group">
                        <label for="editBaseUnit">Base Unit</label>
                        <select name="base_unit" id="editBaseUnit" class="form-control" required>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="bottle">Bottle</option>
                            <option value="vial">Vial</option>
                            <option value="tube">Tube</option>
                        </select>
                    </div>

                    <!-- Smallest Unit Dropdown -->
                    <div class="form-group">
                        <label for="editSmallestUnit">Smallest Unit</label>
                        <select name="smallest_unit" id="editSmallestUnit" class="form-control" required>
                            <option value="tablet">Tablet</option>
                            <option value="capsule">Capsule</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="g">Gram (g)</option>
                            <option value="piece">Piece</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editConversionFactor">Conversion Factor</label>
                        <input type="number" class="form-control" id="editConversionFactor" name="conversion_factor" step="0.01" required>
                    </div>

                    <div class="form-group">
                   <label for="editCategory">Category</label>
                   <select id="editCategory" name="category" class="form-control" required>
                   <option value="">Select Category</option>
                     <option value="pain killer">Pain killer</option>
                    <option value="antibiotic">Antibiotic</option>
                    <option value="dermatology">Dermatology</option>
                    <option value="respiratory">Respiratory</option>
                    <option value="Sedative">Sedative</option>
                    <option value="antihistamine">Antihistamine</option>
                    <option value="cough suppressant">Cough suppressant</option>
                    <option value="supplement">supplement</option>
                    <option value="tube">Tube</option>
                    <option value="Inhaler">Inhaler</option>
                    <option value="vial">Vial</option>
    </select>
</div>

                    <div class="form-group">
                        <label for="editSellingPrice">Selling Price</label>
                        <input type="number" class="form-control" id="editSellingPrice" name="selling_price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editImage">Drug Image</label>
                        <input type="file" class="form-control" id="editImage" name="image">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Drug</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        $(document).ready(function () {
    // Handle Edit Button Click to Populate Edit Modal
    $('#editDrugModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal

        // Extract data attributes from the button
        var id = button.data('id');
        var name = button.data('name');
        var costPrice = button.data('cost_price');
        var manufacturingDate = button.data('manufacturing_date');
        var expiryDate = button.data('expiry_date');
       
        var baseUnit = button.data('base_unit');
        var smallestUnit = button.data('smallest_unit');
        var conversionFactor = button.data('conversion_factor');
        var category = button.data('category');
        var sellingPrice = button.data('selling_price');

        var modal = $('#editDrugModal');
        modal.find('#editDrugId').val(id);
        modal.find('#editDrugName').val(name);
        modal.find('#editCostPrice').val(costPrice);
       
      
        modal.find('#editCategory').val(category);
        modal.find('#editSellingPrice').val(sellingPrice);

        // Populate dropdowns for Base Unit and Smallest Unit
        modal.find('#editBaseUnit').val(baseUnit);  // Set selected base unit
        modal.find('#editSmallestUnit').val(smallestUnit); // Set selected smallest unit

        // Populate Conversion Factor
        modal.find('#editConversionFactor').val(conversionFactor);
    });

    // Handle Form Submission for Edit Drug
    $('#editDrugForm').on('submit', function(event) {
        event.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'add_drug.php',  // Same file for both adding and editing
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Success',
                        text: res.message,
                        icon: 'success',
                        timer: 5000,
                        showConfirmButton: true
                    });
                    $('#editDrugModal').modal('hide');
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: res.message,
                        icon: 'error',
                        timer: 5000,
                        showConfirmButton: true
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred while processing your request. Please try again.',
                    icon: 'error',
                    timer: 5000,
                    showConfirmButton: true
                });
            }
        });
    });

    // Handle Delete Button Click
    $(".btn-delete").click(function () {
        let productId = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_product.php",
                    type: "POST",
                    data: { id: productId },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire("Deleted!", response.message, "success").then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire("Error!", response.message, "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    },
                });
            }
        });
    });
});



    </script>
</body>
</html>
