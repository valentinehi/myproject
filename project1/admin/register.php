<?php



include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
    $admin_id = $_COOKIE['admin_id'];
}else{
    $admin_id = '';
    header('location:login.php');
}

$warning_msg = array();
$success_msg = array();

if (isset($_POST['submit'])) {
    $id = uniqid();
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $password = $_POST['pass'];
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Hash the password using password_hash()
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
    $select_admins->bind_param("s", $name);
    $select_admins->execute();
    $select_admins->store_result();

    if ($select_admins->num_rows > 0) {
        $warning_msg[] = 'Username already taken!';
    } else {
        $insert_admin = $conn->prepare("INSERT INTO `admins` (id, name, password) VALUES (?, ?, ?)");
        $insert_admin->bind_param("sss", $id, $name, $hashedPassword);
        $insert_admin->execute();
        $success_msg[] = 'Registered successfully!';
    }
}

// Display Messages using JavaScript for Alert Dialogs
foreach ($warning_msg as $warning) {
    echo "<script>alert('Warning: $warning');</script>";
}

foreach ($success_msg as $success) {
    echo "<script>alert('Success: $success');</script>";
}

?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>

         <!-----font awesome cdn link---->
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

         <!--custom css file link-->
         <link rel="stylesheet" href="../css/admin_style.css">


        </head>
    <body>
        <!--header section starts-->

        <?php include '../components/admin_header.php'; ?>
        <!--header section ends-->
        <!--register section starts-->



        <section class="form-container">
            <form action="" method="POST">
                <h3>register new</h3>
                <input type="text" name="name" placeholder="enter username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="password" name="pass" placeholder="enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="text" name="c_pass" placeholder="confirm password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="submit" value="register now" name="submit" class="btn">

            </form>
        </section>
        <!--register section ends-->
        


















        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>



<!--custom js file link-->
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php';?>


    </body>
</html>

        