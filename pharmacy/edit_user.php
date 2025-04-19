<?php
// Include the database connection
require_once 'connect.php'; // Adjust the path if necessary

// Handle AJAX request to fetch user details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_user'])) {
    $userId = intval($_POST['user_id']);

    $sql = "SELECT id, firstName, lastName, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
    exit;
}

// Handle AJAX request to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = intval($_POST['user_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $sql = "UPDATE users SET firstName = ?, lastName = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $role, $userId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User details updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating user: " . $stmt->error]);
    }
    exit;
}
?>

<!-- Include SweetAlert and Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.14.0/dist/sweetalert2.all.min.js"></script>

<!-- Table showing existing users with Edit button -->
<div class="table-responsive mt-3">
    <table class="table table-bordered" id="usersTable">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
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
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>
                        <button class='btn btn-warning editBtn' data-id='" . $row['id'] . "'>Edit</button>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Script to handle editing user details -->
<script>
    document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.getAttribute('data-id');

            // Fetch user details using AJAX
            fetch('edit_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'fetch_user': 1,
                    'user_id': userId,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const user = data.data;

                    // Open SweetAlert modal with user details
                    Swal.fire({
                        title: 'Edit User',
                        html: `
                            <form id="editUserForm">
                                <input type="hidden" id="userId" value="${user.id}">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" class="form-control" id="firstName" value="${user.firstName}">
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" value="${user.lastName}">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" value="${user.email}">
                                </div>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select class="form-control" id="role">
                                        <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                        <option value="sales" ${user.role === 'sales' ? 'selected' : ''}>Sales</option>
                                    </select>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Save Changes',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const userId = document.getElementById('userId').value;
                            const firstName = document.getElementById('firstName').value;
                            const lastName = document.getElementById('lastName').value;
                            const email = document.getElementById('email').value;
                            const role = document.getElementById('role').value;

                            if (!firstName || !lastName || !email || !role) {
                                Swal.showValidationMessage('Please fill in all fields');
                                return false;
                            }

                            return { userId, firstName, lastName, email, role };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const { userId, firstName, lastName, email, role } = result.value;

                            // Send updated data to the server using AJAX
                            fetch('edit_users.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams({
                                    'update_user': 1,
                                    'user_id': userId,
                                    'first_name': firstName,
                                    'last_name': lastName,
                                    'email': email,
                                    'role': role,
                                })
                            }).then(response => response.json()).then(data => {
                                if (data.status === 'success') {
                                    Swal.fire('Updated!', data.message, 'success').then(() => {
                                        location.reload(); // Reload the page after successful update
                                    });
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            }).catch(error => {
                                Swal.fire('Error', 'There was an error updating the user.', 'error');
                            });
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            }).catch(error => {
                Swal.fire('Error', 'There was an error fetching the user details.', 'error');
            });
        });
    });
</script>
