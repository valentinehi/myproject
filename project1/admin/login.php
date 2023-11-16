<?php 



include '../components/connect.php';

$warning_msg = array();
$success_msg = array();

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $password = trim($_POST['pass']);
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $select_admins = $conn->prepare("SELECT id, name, password FROM `admins` WHERE name = ? LIMIT 1");
    $select_admins->bind_param("s", $name);
    $select_admins->execute();
    $select_admins->store_result();
    $select_admins->bind_result($id, $name, $hashedPassword);

    if ($select_admins->num_rows > 0 && $select_admins->fetch()) {
        setcookie('admin_id', $id, time() + 60*60*24*30, '/');
        
        // Display success message using PHP
        $success_msg[] = 'Login successful!';
    } else {
        $warning_msg[] = 'Incorrect username or password!';
    }

    $select_admins->close();
}







?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>

         <!-----font awesome cdn link---->
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

         <!--custom css file link-->
         <link rel="stylesheet" href="../css/admin_style.css">


        </head>
    <body>
    
      
        <!--login section starts-->



        <section class="form-container" style="max-height: 100vh;">
            <form action="" method="POST">
                <h3>welcome back!</h3>
                
                <input type="text" name="name" placeholder="enter username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="password" name="pass" placeholder="enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
                
                <input type="submit" value="login now" name="submit" class="btn">

            </form>
        </section>
        <!--login section ends-->
        <?php
// PHP code to display warning messages
foreach ($warning_msg as $warning) {
    echo "<p style='color: red;'>Warning: $warning</p>";
}

// PHP code to display success message
foreach ($success_msg as $success) {
    echo "<script>";
    echo "alert('$success');";
    echo "window.location.href = 'dashboard.php';"; // Redirect to dashboard.php after successful login
    echo "</script>";
}
?>

        
        



















        

        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>



<?php include '../components/message.php';?>


    </body>
</html>

        