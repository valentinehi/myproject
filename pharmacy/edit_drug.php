<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $drugName = trim($_POST['drug_name']);
    $costPrice = trim($_POST['cost_price']);
    $manufacturingDate = $_POST['manufacturing_date'];
    $expiryDate = $_POST['expiry_date'];
    $quantity = intval($_POST['quantity']);
    $category = trim($_POST['category']);
    $sellingPrice = trim($_POST['selling_price']);
    $imageName = $_FILES['image']['name'];

    $imageUpdated = false;
    $targetDir = "uploads/";
    $targetFilePath = $targetDir . basename($imageName);

    if (!empty($imageName)) {
        $imageType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($imageType, $allowedTypes)) {
            echo json_encode(["status" => "error", "message" => "Invalid image format."]);
            exit;
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imageUpdated = true;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
            exit;
        }
    }

    // Update query
    $sql = "UPDATE drug_product 
            SET name = ?, cost_price = ?, manufacturing_date = ?, expiry_date = ?, quantity = ?, category = ?, selling_price = ?";
    if ($imageUpdated) {
        $sql .= ", image = ?";
    }
    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($imageUpdated) {
        $stmt->bind_param("ssdssissi", $drugName, $costPrice, $manufacturingDate, $expiryDate, $quantity, $category, $sellingPrice, $imageName, $id);
    } else {
        $stmt->bind_param("ssdssisi", $drugName, $costPrice, $manufacturingDate, $expiryDate, $quantity, $category, $sellingPrice, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Drug updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit drug</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
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
                    <input type="hidden" id="editDrugId" name="id">
                    <div class="form-group">
                        <label for="editDrugName">Drug Name</label>
                        <input type="text" class="form-control" id="editDrugName" name="drug_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editDrugImage">Drug Image</label>
                        <input type="file" class="form-control" id="editDrugImage" name="image">
                    </div>
                    <div class="form-group">
                        <label for="editCostPrice">Cost Price</label>
                        <input type="number" class="form-control" id="editCostPrice" name="cost_price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="editManufacturingDate">Manufacturing Date</label>
                        <input type="date" class="form-control" id="editManufacturingDate" name="manufacturing_date" required>
                    </div>
                    <div class="form-group">
                        <label for="editExpiryDate">Expiry Date</label>
                        <input type="date" class="form-control" id="editExpiryDate" name="expiry_date" required>
                    </div>
                    <div class="form-group">
                        <label for="editQuantity">Quantity</label>
                        <input type="number" class="form-control" id="editQuantity" name="quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="editCategory">Category</label>
                        <select class="form-control" id="editCategory" name="category" required>
                            <option value="Pain Relief">Pain Relief</option>
                            <option value="Antibiotics">Antibiotics</option>
                            <option value="Vitamins">Vitamins</option>
                            <option value="Cough & Cold">Cough & Cold</option>
                            <option value="Skin Care">Skin Care</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editSellingPrice">Selling Price</label>
                        <input type="number" class="form-control" id="editSellingPrice" name="selling_price" step="0.01" required>
                    </div>
                    <button type="submit" class="btn btn-success">Update Drug</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(document).on('click', '.edit-drug-button', function () {
    const row = $(this).closest('tr');
    $('#editDrugId').val(row.find('td:eq(0)').text());
    $('#editDrugName').val(row.find('td:eq(1)').text());
    $('#editCostPrice').val(row.find('td:eq(3)').text());
    $('#editManufacturingDate').val(row.find('td:eq(4)').text());
    $('#editExpiryDate').val(row.find('td:eq(5)').text());
    $('#editQuantity').val(row.find('td:eq(6)').text());
    $('#editCategory').val(row.find('td:eq(7)').text());
    $('#editSellingPrice').val(row.find('td:eq(8)').text());
    $('#editDrugModal').modal('show');
});

$('#editDrugForm').on('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);

    $.ajax({
        url: 'edit_drug.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                Swal.fire('Success', res.message, 'success');
                $('#editDrugModal').modal('hide');
                location.reload(); // Reload the page to show updated data
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        }
    });
});
</script>

</body>
</html>