<?php
// Include the database connection
require_once 'connect.php'; // Adjust the path if necessary

// Handle form submission for adding user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    // Collect form data
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash password
    $role = trim($_POST['role']);

    // Insert data into the database
    $sql = "INSERT INTO users (firstName, lastName, email, password, role) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $role);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding user: " . $stmt->error]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.14.0/dist/sweetalert2.min.css"></script>
</head>
<body>
  <!-- Add User Button -->
  <div class="d-flex justify-content-end mb-3">
<button class="btn btn-primary w-auto" id="addUserBtn" data-toggle="modal" data-target="#addUserModal" style="padding: 10px 20px;">
    Add User
</button>
  </div>


    <!-- Script to handle SweetAlert modal -->
    <script>
        document.getElementById('addUserBtn').addEventListener('click', function() {
            // Open SweetAlert modal with Bootstrap form
            Swal.fire({
                title: 'Add User',
                html: ` 
                    <form id="addUserForm">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" placeholder="Enter first name">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" placeholder="Enter last name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password">
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role">
                                <option value="admin">Admin</option>
                                <option value="sales_person">Sales Person</option>

                            </select>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add User',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const firstName = document.getElementById('firstName').value;
                    const lastName = document.getElementById('lastName').value;
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const role = document.getElementById('role').value;

                    if (!firstName || !lastName || !email || !password || !role) {
                        Swal.showValidationMessage('Please fill in all fields');
                        return false;
                    }

                    // If all fields are filled, return the form data
                    return { firstName, lastName, email, password, role };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { firstName, lastName, email, password, role } = result.value;

                    // Send the data to PHP using AJAX (using fetch API here)
                    fetch('users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'add_user': 1,
                            'first_name': firstName,
                            'last_name': lastName,
                            'email': email,
                            'password': password,
                            'role': role,
                        })
                    }).then(response => response.json()).then(data => {
                        if (data.status === 'success') {
                            Swal.fire('User Added!', data.message, 'success').then(() => {
                                location.reload(); // Reload the page after successful addition
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    }).catch(error => {
                        Swal.fire('Error', 'There was an error adding the user.', 'error');
                    });
                }
            });
        });
    </script>

   <!-- Table showing existing users -->
<div class="table-responsive mt-3">
    <table class="table table-bordered" id="usersTable">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch existing users from the database
            $sql = "SELECT id, firstName, lastName, email, role FROM users";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                $displayRole = ($row['role'] === 'sales_person') ? 'Sales' : $row['role'];
                echo "<td>" . htmlspecialchars($displayRole) . "</td>";

                // Edit Button
                echo "<td class='text-center'>
                        <button class='btn btn-sm btn-success editBtn' data-id='" . $row['id'] . "' data-toggle='modal' data-target='#editModal'>
                            <i class='fas fa-edit'></i>
                        </button>
                      </td>";

                // Delete Button
                echo "<td class='text-center'>
                        <button class='btn btn-sm btn-danger deleteBtn' data-id='" . $row['id'] . "'>
                            <i class='fas fa-trash'></i>
                        </button>
                      </td>";

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

    </div>
    <!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label for="editFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="editFirstName" placeholder="Enter first name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="editLastName" placeholder="Enter last name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" placeholder="Enter email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" required>
                            <option value="admin">Admin</option>
                            <option value="sales_person">Sales Person</option>

                        </select>
                    </div>
                
          
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    </div>
    </form>
            </div>


        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Event delegation for both edit and delete buttons
    document.querySelector("tbody").addEventListener("click", function(event) {
        if (event.target && event.target.classList.contains('editBtn')) {
            const userId = event.target.getAttribute('data-id');
            console.log("Editing user with ID:", userId);

            // Fetch user data and show in modal
            fetch(`edit_users.php?action=get_user&id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.user) {
                        document.getElementById('editUserId').value = data.user.id;
                        document.getElementById('editFirstName').value = data.user.firstName;
                        document.getElementById('editLastName').value = data.user.lastName;
                        document.getElementById('editEmail').value = data.user.email;
                        document.getElementById('editRole').value = data.user.role;
                        const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                        editUserModal.show();
                    } else {
                        Swal.fire('Error', 'User data could not be retrieved.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', `An error occurred: ${error.message}`, 'error');
                });
        }

        // Event delegation for delete button
        if (event.target && event.target.classList.contains('deleteBtn')) {
            const userId = event.target.getAttribute('data-id');
            console.log("Deleting user with ID:", userId);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send request to delete user
                    fetch('delete_users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'delete_users': 1,
                            'user_id': userId
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success').then(() => {
                                    location.reload(); // Reload page after deletion
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'There was an error deleting the user.', 'error');
                        });
                }
            });
        }
    });

    // Handle the edit form submission
document.getElementById('editUserForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const userId = document.getElementById('editUserId').value;
    const firstName = document.getElementById('editFirstName').value;
    const lastName = document.getElementById('editLastName').value;
    const email = document.getElementById('editEmail').value;
    const role = document.getElementById('editRole').value;

    if (!firstName || !lastName || !email || !role) {
        Swal.fire('Error', 'Please fill in all fields.', 'error');
        return;
    }

    // Send the updated data to the server
    fetch('edit_users.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'update_user': 1, // Correct parameter name
            'user_id': userId,
            'first_name': firstName,
            'last_name': lastName,
            'email': email,
            'role': role
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Updated!', data.message, 'success').then(() => {
                location.reload(); // Reload the page after a successful update
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'There was an error updating the user.', 'error');
    });
});
})

</script>

    <!-- Bootstrap Bundle JS (includes Popper.js) -->
     
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
