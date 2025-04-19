<?php
session_start();
require 'connect.php';

// Ensure user is logged in
if (!isset($_SESSION['firstName'])) {
    header("Location: login.php");
    exit();
}

$firstName = $_SESSION['firstName'];

// Fetch Admin Details
$sql = "SELECT firstName, lastName, email, profile_picture FROM users WHERE firstName = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $firstName);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc() ?: [
    'firstName' => '',
    'lastName' => '',
    'email' => '',
    'profile_picture' => 'default.png'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome Icons -->
    <style>
        .profile-icon {
            font-size: 100px;
            color: gray;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Administrator Profile</h2>
    <div class="card">
        <div class="card-body text-center">
            <?php if (!empty($admin['profile_picture']) && file_exists("uploads/" . $admin['profile_picture'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($admin['profile_picture']); ?>" class="rounded-circle" width="150" alt="Profile Picture">
            <?php else: ?>
                <i class="fas fa-user-circle profile-icon"></i> <!-- Profile Icon -->
            <?php endif; ?>
            
            <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="mt-3">
                <input type="file" name="profile_picture" class="form-control">
                <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-upload"></i> Upload New Picture</button>
            </form>
        </div>

        <div class="card-body">
            <form action="update_profile.php" method="POST">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstName" class="form-control" value="<?php echo htmlspecialchars($admin['firstName']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastName" class="form-control" value="<?php echo htmlspecialchars($admin['lastName']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Profile</button>
            </form>
        </div>

        <hr>
        <div class="card-body">
            <h4>Change Password</h4>
            <form action="change_password.php" method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-warning"><i class="fas fa-lock"></i> Change Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
