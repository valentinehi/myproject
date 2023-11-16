<?php
include '../components/connect.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location:login.php');
}


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>

         <!-----font awesome cdn link---->
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

         <!--custom css file link-->
         <link rel="stylesheet" href="../css/admin_style.css">


        </head>
    <body>
        <!--header section starts-->

        <?php include '../components/admin_header.php'; ?>
        <!--header section ends-->
        <!-- dashboard section starts -->

<section class="dashboard">
    <h1 class="heading">dashboard</h1>
    <div class="box-container">
        <div class="box">
            <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
            $select_profile->bind_param("i", $admin_id);
            $select_profile->execute();
            $result_profile = $select_profile->get_result();
            $fetch_profile = $result_profile->fetch_assoc();
            ?>
            <h3>welcome</h3>
            <p><?= $fetch_profile['name']; ?></p>
            <a href="update.php" class="btn">update profile</a>
        </div>

        <div class="box">
            <?php
            $select_bookings = $conn->prepare("SELECT * FROM `bookings`");
            $select_bookings->execute();
            $result_bookings = $select_bookings->get_result();
            $count_bookings = $result_bookings->num_rows;
            ?>
            <h3><?= $count_bookings; ?></h3>
            <p>total bookings</p>
            <a href="bookings.php" class="btn">view bookings</a>
        </div>

        <div class="box">
            <?php
            $select_admins = $conn->prepare("SELECT * FROM `admins`");
            $select_admins->execute();
            $result_admins = $select_admins->get_result();
            $count_admins = $result_admins->num_rows;
            ?>
            <h3><?= $count_admins; ?></h3>
            <p>total admins</p>
            <a href="admins.php" class="btn">view admins</a>
        </div>

        <div class="box">
            <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $result_messages = $select_messages->get_result();
            $count_messages = $result_messages->num_rows;
            ?>
            <h3><?= $count_messages; ?></h3>
            <p>total messages</p>
            <a href="messages.php" class="btn">view messages</a>
        </div>

        <div class="box">
            <h3>quick select</h3>
            <p>login or register</p>
            <a href="login.php" class="btn" style="margin-right: 1rem;">login</a>
            <a href="register.php" class="btn" style="margin-left: 1rem;">register</a>
        </div>

    </div>
</section>
<!--dashboard section ends-->






















<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>

<!--custom js file link-->
<script src="../js/admin_script.js"></script>
    </body>
</html>

        