<?php



include '../components/connect.php';

$warning_msg = array();
$success_msg = array();

if(isset($_COOKIE['admin_id'])){
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location:login.php');
    exit(); // Make sure to exit after a header redirect
}

$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
$select_profile->bind_param("i", $admin_id); // Bind the parameter
$select_profile->execute();
$result_profile = $select_profile->get_result();
$fetch_profile = $result_profile->fetch_assoc();

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    if(!empty($name)){
        $verify_name = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
        $verify_name->bind_param("s", $name); // Bind the parameter
        $verify_name->execute();
        $result_verify = $verify_name->get_result();
        
        if($result_verify->num_rows > 0){
            $warning_msg[] ='Username already taken!';
        } else {
            $update_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
            $update_name->bind_param("si", $name, $admin_id); // Bind the parameters
            $update_name->execute();
            $success_msg[] = 'Username updated!';
        }
    }

    $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
    $prev_pass = $fetch_profile['password'];
    $old_pass = sha1($_POST['old_pass']);
    $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
    $new_pass = sha1($_POST['new_pass']);
    $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
    $c_pass = sha1($_POST['c_pass']);
    $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING);

    if($old_pass != $empty_pass){
        if($old_pass != $prev_pass){
            $warning_msg[] = 'Old password not matched!';
        } elseif($c_pass != $new_pass){
            $warning_msg[] = 'New password not matched!';
        } else {
            if($new_pass != $empty_pass){
                $update_password = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
                $update_password->bind_param("si", $c_pass, $admin_id); // Bind the parameters
                $update_password->execute();
                $success_msg[] = 'Password updated!';
            } else {
                $warning_msg[] = 'Please enter a new password!';
            }
        }
    }
}

// Close the prepared statements
$select_profile->close();
if (isset($verify_name)) {
    $verify_name->close();
}
if (isset($update_name)) {
    $update_name->close();
}
if (isset($update_password)) {
    $update_password->close();
}

// Output JavaScript code for displaying messages in a dialog box
echo '<script>';
foreach ($warning_msg as $warning) {
    echo "alert('Warning: $warning');";
}
foreach ($success_msg as $success) {
    echo "alert('$success');";
}
echo '</script>';


?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Update</title>

         <!-----font awesome cdn link---->
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

         <!--custom css file link-->
         <link rel="stylesheet" href="../css/admin_style.css">


        </head>
    <body>
        <!--header section starts-->

        <?php include '../components/admin_header.php'; ?>
        <!--header section ends-->
        <!--update section starts-->



        <section class="form-container">
            <form action="" method="POST">
                <h3>update profile</h3>
                <input type="text" name="name" placeholder="<?= $fetch_profile['name']; ?>" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="password" name="old_pass" placeholder="enter old password" maxlength="20" class="box"oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="password" name="new_pass" placeholder="enter new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="text" name="c_pass" placeholder="confirm new password" maxlength="20" class="box"  oninput="this.value = this.value.replace(/\s/g, '')">
                <input type="submit" value="update now" name="submit" class="btn">

            </form>
        </section>
        <!--update section ends-->
        



















        <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>



<!--custom js file link-->
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php';?>


    </body>
</html>
