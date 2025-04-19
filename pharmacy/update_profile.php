<?php
session_start();
require 'connect.php';

// Ensure user is logged in
if (!isset($_SESSION['firstName'])) {
    die("Unauthorized access");
}

$firstName = $_SESSION['firstName'];

// Fetch user ID
$sql = "SELECT id FROM users WHERE firstName = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $firstName);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found!");
}

$userId = $user['id'];
$alertMessage = "";  // Placeholder for alert message

// Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
            // Update profile picture in the database
            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $fileName, $userId);
            if ($stmt->execute()) {
                $alertMessage = "
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile Picture Updated!',
                            text: 'Your profile picture has been successfully updated.',
                            confirmButtonText: 'OK'
                        }).then(() => { window.location.href = 'profile.php'; });
                    </script>";
            } else {
                $alertMessage = "
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Database update failed!',
                            confirmButtonText: 'OK'
                        });
                    </script>";
            }
        } else {
            $alertMessage = "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Error',
                        text: 'File upload failed. Please try again!',
                        confirmButtonText: 'OK'
                    });
                </script>";
        }
    } else {
        $alertMessage = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Format',
                    text: 'Only JPG, JPEG, and PNG formats are allowed!',
                    confirmButtonText: 'OK'
                });
            </script>";
    }
}

// Update firstName, lastName, and email if provided
if (!empty($_POST['firstName']) && !empty($_POST['lastName']) && !empty($_POST['email'])) {
    $newFirstName = $_POST['firstName'];
    $newLastName = $_POST['lastName'];
    $newEmail = $_POST['email'];

    $sql = "UPDATE users SET firstName = ?, lastName = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $newFirstName, $newLastName, $newEmail, $userId);

    if ($stmt->execute()) {
        $_SESSION['firstName'] = $newFirstName;
        $alertMessage = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated!',
                    text: 'Your profile details have been successfully updated.',
                    confirmButtonText: 'OK'
                }).then(() => { window.location.href = 'profile.php'; });
            </script>";
    } else {
        $alertMessage = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: 'Error updating profile details!',
                    confirmButtonText: 'OK'
                });
            </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?= $alertMessage; ?>
</body>
</html>
