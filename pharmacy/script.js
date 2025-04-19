document.addEventListener('DOMContentLoaded', function () {
    // Sign-up and Sign-in toggle functionality
    const signUpButton = document.getElementById('signUpButton');
    const signInButton = document.getElementById('signInButton');
    const signInForm = document.getElementById('signIn');
    const signUpForm = document.getElementById('signup');

    if (signUpButton && signInButton && signInForm && signUpForm) {
        signUpButton.addEventListener('click', function () {
            signInForm.style.display = "none";
            signUpForm.style.display = "block";
        });

        signInButton.addEventListener('click', function () {
            signInForm.style.display = "block";
            signUpForm.style.display = "none";
        });
    }

    // Handling Category Table for Editing and Deleting
    const categoryTable = document.querySelector('.category-table');

    if (categoryTable) {
        categoryTable.addEventListener('click', function (event) {
            if (event.target.closest('.edit-btn')) {
                const row = event.target.closest('tr');
                const categoryId = row.dataset.categoryId;
                const categoryName = row.dataset.categoryName;

                // Open SweetAlert modal for editing
                Swal.fire({
                    title: 'Edit Category',
                    html: `
                        <input type="hidden" id="modal-category-id" value="${categoryId}">
                        <label for="modal-category-name">Category Name</label>
                        <input type="text" id="modal-category-name" class="swal2-input" value="${categoryName}" required>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    preConfirm: () => {
                        const updatedName = document.getElementById('modal-category-name').value.trim();
                        if (!updatedName) {
                            Swal.showValidationMessage('Category name cannot be empty.');
                        }
                        return updatedName;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const updatedName = result.value;

                        // AJAX request to update category
                        fetch('edit_category.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `category_id=${categoryId}&category_name=${encodeURIComponent(updatedName)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire('Success', data.message, 'success');
                                // Update the category name in the table
                                row.querySelector('td:nth-child(2)').textContent = updatedName;
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'An unexpected error occurred.', 'error');
                        });
                    }
                });
            }

            if (event.target.closest('.delete-btn')) {
                const row = event.target.closest('tr');
                const categoryId = row.dataset.categoryId;

                // Confirm delete action
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX request to delete category
                        fetch('delete_category.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `category_id=${categoryId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success');
                                // Remove the row from the table
                                row.remove();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'An unexpected error occurred.', 'error');
                        });
                    }
                });
            }
        });
    }
   // Add Category Modal Functionality
const addCategoryButton = document.getElementById('addCategoryButton');
const addCategoryModal = document.getElementById('addCategoryModal');
const closeModal = addCategoryModal?.querySelector('.close-modal');
const addCategoryForm = document.getElementById('addCategoryForm');

if (addCategoryButton && addCategoryModal && closeModal && addCategoryForm) {
    addCategoryButton.addEventListener('click', function () {
        addCategoryModal.style.display = 'flex';
    });

    closeModal.addEventListener('click', function () {
        addCategoryModal.style.display = 'none';
        addCategoryForm.reset();
    });

    addCategoryForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(addCategoryForm);

        fetch('add_category.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Category added successfully")) {
                // Success SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Category added successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                addCategoryForm.reset();
                setTimeout(() => {
                    addCategoryModal.style.display = 'none';
                    location.reload();
                }, 2000);
            } else if (data.includes("Category name cannot be empty")) {
                // Validation Error SweetAlert
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Category name cannot be empty.',
                });
            } else {
                // General Error SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding the category.',
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again.',
            });
        });
    });

    window.addEventListener('click', function (event) {
        if (event.target === addCategoryModal) {
            addCategoryModal.style.display = 'none';
            addCategoryForm.reset();
        }
    });
}
//adding a supplier
$(document).ready(function () {
    $('#addSupplierForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        $.ajax({
            url: 'add_supplier.php', // Current file
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // SweetAlert Success Message
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                    // Append the new supplier row to the table
                    $('tbody').append(`
                        <tr>
                            <td>${response.data.id}</td>
                            <td>${response.data.name}</td>
                            <td>${response.data.address}</td>
                            <td>${response.data.phone}</td>
                            <td>
                                <a href="edit_supplier.php?id=${response.data.id}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger delete-supplier" data-id="${response.data.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    `);

                    // Reset form and hide modal
                    $('#addSupplierForm')[0].reset();
                    $('#addSupplierModal').modal('hide');
                } else {
                    // SweetAlert Error Message
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function () {
                // SweetAlert Error Message for unexpected issues
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
$(document).ready(function () {
    // Populate modal with supplier data
    $(document).on('click', '.edit-supplier', function () {
        var supplierId = $(this).data('id');
        var supplierName = $(this).data('name');
        var supplierAddress = $(this).data('address');
        var supplierPhone = $(this).data('phone');

        $('#supplier_id').val(supplierId);
        $('#supplier_name').val(supplierName);
        $('#supplier_address').val(supplierAddress);
        $('#supplier_phone').val(supplierPhone);
    });

    // Handle form submission via AJAX
    $('#editSupplierForm').on('submit', function (e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: 'edit_supplier.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        showConfirmButton: true
                    }).then(() => {
                        // Option 1: Refresh the page
                        location.reload();

                        // Option 2: Dynamically update table row
                        // Uncomment to refresh the specific row dynamically
                        /*
                        var supplierId = response.data.id;
                        $('#supplier-' + supplierId + ' .supplier-name').text(response.data.name);
                        $('#supplier-' + supplierId + ' .supplier-address').text(response.data.address);
                        $('#supplier-' + supplierId + ' .supplier-phone').text(response.data.phone);
                        */

                        // Close modal
                        $('#editSupplierModal').modal('hide');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        showConfirmButton: true
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: 'Something went wrong. Please try again later.',
                    showConfirmButton: true
                });
            }
        });
    });
});

$(document).on('click', '.delete-supplier', function (e) {
    e.preventDefault();
    var supplierId = $(this).data('id'); 

    if (!supplierId) {
        console.error('No supplier ID found');
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'delete_supplier.php',
                type: 'POST',
                data: { id: supplierId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Deleted!', response.message, 'success');
                        $('#supplier-' + supplierId).remove();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
});

// adding product 
// Handle Form Submission for Add Drug
$('#addDrugForm').on('submit', function(event) {
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
                    timer: 5000, // Adjust the time (5 seconds in this case)
                    showConfirmButton: true  // Allows user to manually close the alert
                });
                // Delay before reloading to ensure success message is seen
                setTimeout(() => {
                    $('#addDrugModal').modal('hide');
                    location.reload();
                }, 1000);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: res.message,
                    icon: 'error',
                    timer: 5000, // Adjust the time (5 seconds in this case)
                    showConfirmButton: true  // Allows user to manually close the alert
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while processing your request. Please try again.',
                icon: 'error',
                timer: 5000, // Adjust the time (5 seconds in this case)
                showConfirmButton: true  // Allows user to manually close the alert
            });
        }
    });
});
// Handle Form Submission for Uploading Excel File
$('#uploadExcelForm').on('submit', function(event) {
    event.preventDefault();
    
    var formData = new FormData(this);
    
    $.ajax({
        url: 'upload_excel.php',  // The PHP file that processes the Excel upload
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
                    timer: 5000, // Adjust the time (5 seconds in this case)
                    showConfirmButton: true  // Allows user to manually close the alert
                });
                $('#uploadExcelModal').modal('hide');
                location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: res.message,
                    icon: 'error',
                    timer: 5000, // Adjust the time (5 seconds in this case)
                    showConfirmButton: true  // Allows user to manually close the alert
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while processing your request. Please try again.',
                icon: 'error',
                timer: 5000, // Adjust the time (5 seconds in this case)
                showConfirmButton: true  // Allows user to manually close the alert
            });
        }
    });
});


                    
                         
//adding stock
$(document).ready(function () {
    // Handle Add Stock Form Submission
    $('#addStockForm').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Collect form data
        const formData = new FormData(this);

        // Send form data via fetch
        fetch('add_stock.php', { // Submit to the same file
            method: 'POST',
            body: new URLSearchParams(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Success', data.message, 'success').then(() => {
                        location.reload(); // Reload the page to show updated data
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
            });
    });
});
})