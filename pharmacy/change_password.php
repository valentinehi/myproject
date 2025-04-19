<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['firstName'])) {
    die("Unauthorized access");
}

$firstName = $_SESSION['firstName'];
$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];

// Fetch stored password
$sql = "SELECT password FROM users WHERE firstName = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $firstName);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$message = "";  // Placeholder for SweetAlert message

if ($user) {
    // Compare MD5 hash of entered password with stored password
    if ($user['password'] === md5($currentPassword)) {
        // Hash new password with MD5
        $hashedNewPassword = md5($newPassword);
        
        $sql = "UPDATE users SET password = ? WHERE firstName = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hashedNewPassword, $firstName);
        $stmt->execute();

        // SweetAlert success message
        $message = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Password successfully changed!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'admin_dashboard.php'; // Redirect after success
                });
            </script>";
    } else {
        // SweetAlert error message for incorrect password
        $message = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Incorrect Password',
                    text: 'The current password entered is incorrect. Please try again!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.history.back(); // Go back to the form page
                });
            </script>";
    }
} else {
    // SweetAlert error message for user not found
    $message = "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'User Not Found',
                text: 'We could not find your account!',
                confirmButtonText: 'OK'
            }).then(() => {
                window.history.back();
            });
        </script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?= $message; ?>
</body>
</html>
