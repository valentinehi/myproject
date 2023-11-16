<?php
include '../components/connect.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location:login.php');
}
if (isset($_POST['delete']) && isset($_POST['booking_id'])) {
    $delete_id = $_POST['booking_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

    $verify_delete = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
    $verify_delete->bind_param("s", $delete_id);
    $verify_delete->execute();
    $verify_delete->store_result();

    if ($verify_delete->num_rows > 0) {
        $delete_bookings = $conn->prepare("DELETE FROM `bookings` WHERE booking_id = ?");
        $delete_bookings->bind_param("s", $delete_id);
        $delete_bookings->execute();
        $success_msg[] = 'Booking deleted';
    } else {
        $warning_msg[] = 'Booking not found or already deleted!';
    }
}





?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bookings</title>

         <!-----font awesome cdn link---->
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

         <!--custom css file link-->
         <link rel="stylesheet" href="../css/admin_style.css">


        </head>
    <body>
        <!--header section starts-->

        <?php include '../components/admin_header.php'; ?>
        <!--header section ends-->

        <!-- booking section starts-->

<div class="grid">
    <h1 class="heading"> bookings</h1>
    <div class="box-container">
        <?php
        $select_bookings = $conn->query("SELECT * FROM `bookings`");
        if ($select_bookings->num_rows > 0) {
            while ($fetch_bookings = $select_bookings->fetch_assoc()) {
        ?>
                <div class="box">
                    <p>name : <span><?= $fetch_bookings['name']; ?></span></p>
                    <p>email : <span><?= $fetch_bookings['email']; ?></span></p>
                    <p>number : <span><?= $fetch_bookings['number']; ?></span></p>
                    <p>check in : <span><?= $fetch_bookings['check_in']; ?></span></p>
                    <p>check out : <span><?= $fetch_bookings['adults']; ?></span></p>
                    <p>adults : <span><?= $fetch_bookings['name']; ?></span></p>
                    <p>child : <span><?= $fetch_bookings['child']; ?></span></p>
                    <p>booking id : <span><?= $fetch_bookings['booking_id']; ?></span></p>
                    <form action="" method="POST">
                        <input type="hidden" name="booking_id" value="<?= $fetch_bookings['booking_id']; ?>">
                        <input type="submit" value="delete booking" onclick="return confirm('delete this booking?');" name="delete" class="btn">
                    </form>
                </div>
        <?php
            }
        } else {
        ?>
            <div class="box" style="text-align: center;">
                <p style="padding-bottom: .5rem;">no bookings found!</p>
                <a href="dashboard.php" class="btn">go to home</a>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<!-- booking section ends-->








<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>


<!--custom js file link-->
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php';?>
    </body>
</html>

       