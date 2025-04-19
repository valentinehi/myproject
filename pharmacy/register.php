<?php 
session_start(); // Start session at the beginning
include 'header.php';
include 'connect.php';

if (isset($_POST['signUp'])) {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password); // Hash password (consider using password_hash in the future)
    $role = 'sales_person'; // Ensure consistency in role naming

    // Check if the email already exists
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);
    if ($result->num_rows > 0) {
        echo "Email Address Already Exists!";
    } else {
        // Insert the new user with the default role
        $insertQuery = "INSERT INTO users (firstName, lastName, email, password, role)
                        VALUES ('$firstName', '$lastName', '$email', '$password', '$role')";
        if ($conn->query($insertQuery) === TRUE) {
            header("Location: index.php");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

if (isset($_POST['signIn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password);

    // Check the email and password
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        session_start(); // Ensure session is started
        $row = $result->fetch_assoc();

        // Set session variables
        $_SESSION['firstName'] = $row['firstName'];
        $_SESSION['role'] = $row['role']; // Store user role in session

        // Redirect based on role (fix incorrect variable reference)
        if ($row['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($row['role'] === 'sales_person') { // Match role naming
            header("Location: sales_dashboard.php");
        } else {
            header("Location: unauthorized.php");
        }
        exit;
    } else {
        echo "<p>Invalid username or password.</p>";
    }
}
?>
