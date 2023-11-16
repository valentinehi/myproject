<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    setcookie('user_id', create_unique_id(), time() + 60 * 60 * 20 * 30, '/');
    header('location:index.php');
}

if (isset($_POST['cancel'])) {
    $booking_id = $_POST['booking_id'];
    $booking_id = filter_var($booking_id, FILTER_SANITIZE_STRING);

    $verify_booking = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
    $verify_booking->bind_param("s", $booking_id);
    $verify_booking->execute();
    $verify_result = $verify_booking->get_result();

    if ($verify_result->num_rows > 0) {
        $delete_bookings = $conn->prepare("DELETE FROM `bookings` WHERE booking_id = ?");
        $delete_bookings->bind_param("s", $booking_id);
        $delete_bookings->execute();
        $success_msg[] = 'Booking cancelled successfully!';
    } else {
        $warning_msg[] = 'Booking not found or already cancelled!';
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS file -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'components/user_header.php'; ?>
    <!-- Booking section starts -->
    
    <section class="booking">
        <h1 class="heading">my bookings</h1>
        <div class="box-container">
            <?php
            $select_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ?");
            $select_bookings->bind_param("s", $user_id);
            $select_bookings->execute();
            $result = $select_bookings->get_result();

            if ($result->num_rows > 0) {
                while ($fetch_bookings = $result->fetch_assoc()) {
            ?>
                    <div class="box">
                        <p>name: <span><?= $fetch_bookings['name']; ?></span></p>
                        <p>email: <span><?= $fetch_bookings['email']; ?></span></p>
                        <p>number: <span><?= $fetch_bookings['number']; ?></span></p>
                        <p>check-in: <span><?= $fetch_bookings['check_in']; ?></span></p>
                        <p>check-out: <span><?= $fetch_bookings['check_out']; ?></span></p>
                        <p>rooms: <span><?= $fetch_bookings['rooms']; ?></span></p>
                        <p>adults: <span><?= $fetch_bookings['adults']; ?></span></p>
                        <p>child: <span><?= $fetch_bookings['child']; ?></span></p>
                        <p>booking_id: <span><?= $fetch_bookings['booking_id']; ?></span></p>
                        <form action="" method="POST">
                            <input type="hidden" name="delete_id" value="<?= $fetch_bookings['booking_id']; ?>">
                            <input type="submit" value="cancel booking" name="cancel" class="btn" onclick="return confirm('Cancel this booking?');">
                        </form>
                    </div>
            <?php
                }
            } else {
            ?>
                <div class="box" style="text-align: center;">
                    <p style="padding-bottom: .5rem;  text-transform: capitalize">No bookings found!</p>
                    <a href="index.php#reservation" class="btn">Book New</a>
                </div>
            <?php
            }
            ?>
        </div>
    </section>
    <!-- Booking section ends -->










    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>


<!--custom js file link-->
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php';?>
    </body>
</html>

