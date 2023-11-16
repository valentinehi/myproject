<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'components/connect.php';

if (isset($_POST['check'])) {
     $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

    $total_rooms = 0;

    // Assuming $conn is your MySQLi connection object
    $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
    $check_bookings->bind_param("s", $check_in); // Bind the parameter
    $check_bookings->execute();
    $result = $check_bookings->get_result();

    while ($fetch_bookings = $result->fetch_assoc()) {
        $total_rooms += $fetch_bookings['rooms'];
    }

    // Close the statement
    $check_bookings->close();

    // If the hotel has a total of 30 rooms
    $max_rooms = 30;

    // Determine the message
    $message = ($total_rooms >= $max_rooms) ? 'Rooms are not available' : 'Rooms are available';

    // Use JavaScript to display a dialog box
    echo "<script>
            window.onload = function() {
                alert('$message');
            };
          </script>";
}




if (isset($_POST['book'])) {
    // Get form fields
    $name = isset($_POST['your_name']) ? $_POST['your_name'] : null;
    $email = isset($_POST['your_email']) ? $_POST['your_email'] : null;
    $number = isset($_POST['your_number']) ? $_POST['your_number'] : null;
    $rooms = isset($_POST['rooms']) ? $_POST['rooms'] : null;
    $check_in = isset($_POST['check_in']) ? $_POST['check_in'] : null;
    $check_out = isset($_POST['check_out']) ? $_POST['check_out'] : null;
    $adults = isset($_POST['adults']) ? $_POST['adults'] : null;
    $child = isset($_POST['child']) ? $_POST['child'] : null;

    // Validate input data
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && is_numeric($number) && is_numeric($rooms) && $check_in && $check_out && is_numeric($adults) && is_numeric($child)) {

        // Generate a unique booking_id
        $booking_id = uniqid();

        // Assuming $conn is a valid database connection
        if ($conn) {
            // Perform the booking insertion logic using prepared statement
            $insert_booking = $conn->prepare("INSERT INTO `bookings` (user_id, booking_id, name, email, number, rooms, check_in, check_out, adults, child) VALUES (?,?,?,?,?,?,?,?,?,?)");
            
            // Assuming $user_id needs to be set from some source
            $user_id = "some_user_id"; // Replace with actual user_id

            // Bind parameters
            $insert_booking->bind_param("sssssissii", $user_id, $booking_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $child);

            // Execute the insertion query
            if ($insert_booking->execute()) {
                // Room booked successfully
                echo '<script>alert("Room booked successfully!");</script>';
            } else {
                // Error during room booking
                echo '<script>alert("Error during room booking: ' . $insert_booking->error . '");</script>';
            }

            $insert_booking->close();
        } else {
            // Database connection error
            echo '<script>alert("Database connection error!");</script>';
        }

    } else {
        // Some form fields are not set or invalid
        echo '<script>alert("Some form fields are not set or invalid!");</script>';
    }
}





if (isset($_POST['send'])) {
    echo "Your message is sent, expect feedback any minute from now!"; // Check if the form submission is detected

    // Get form data
    $id = create_unique_id();
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $number = isset($_POST['number']) ? $_POST['number'] : null;
    $message = isset($_POST['message'])? $_POST['msg'] : null;

    
    

    

    
    // Verify if the message already exists
    $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
    $verify_message->bind_param("ssss", $name, $email, $number, $message);
    $verify_message->execute();
    $result = $verify_message->get_result();

    if ($result->num_rows > 0) {
        $warning_msg[] = 'Message sent already!';
    } else {
        $verify_message->close();

        // Insert the new message
        $insert_message = $conn->prepare("INSERT INTO `messages` (id, name, email, number, message) VALUES (?, ?, ?, ?, ?)");
        $insert_message->bind_param("sssss", $id, $name, $email, $number, $message);

        // Execute the query and handle errors
        if ($insert_message->execute()) {
            $success_msg[] = 'Message sent successfully!';

            // Display JavaScript alert
            echo "<script>
                    window.onload = function() {
                        alert('Message sent successfully!');
                    };
                </script>";
        } else {
            $error_msg[] = 'Error during message insertion: ' . $insert_message->error;
        }

        $insert_message->close();
    }
}







?>




<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <!-----font awesome cdn link---->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

       <!--custom css file--> 
       <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        
    <?php include 'components/user_header.php';?>
        <!--home section starts-->
        <section class="home" id="home">
            <div class="swiper home-slider">
                <div class="swiper-wrapper">
                    <div class="box swiper-slide">
                        <img src="images/home3.jpg" alt="">
                        <div class="flex">
                            <h3>luxurius rooms</h3>
                            <a href="# availability" class="btn">check availability</a>

                        </div>
                        

                    </div>

                    <div class="box swiper-slide">
                        <img src="images/home4.jpg" alt="">
                        <div class="flex">
                            <h3>foods and drinks</h3>
                            <a href="#reservation" class="btn">make a reservation</a>

                        </div>
                    </div>
                    <div class="box swiper-slide">
                        <img src="images/home.jpg" alt="">
                        <div class="flex">
                            <h3>luxurius spaces</h3>
                            <a href="# contact" class="btn">contact us</a>

                        </div>
                    </div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>

            </div>
        </section>

         <!--home section ends-->
         
         <!--availability section starts-->

         <section class="availability" id="availability">
            <form action=""  method="post">
                <div class="flex">
                    <div class="box">
                        <p>check in<span>*</span></p>
                        <input type="date" name="check_in" class="input"required>

                        
                    </div>
                    <div class="box">
                        <p>check out<span>*</span></p>
                        <input type="date" name="check_out" class="input" required>
                    </div>
                    <div class="box">
                        <p>adults<span>*</span></p>
                        <select name="adults" class="input" required>
                            <option value="1">1 adult</option>
                            <option value="2">2 adult</option>
                            <option value="3">3 adult</option>
                            <option value="4">4 adult</option>
                            <option value="5">5 adult</option>
                            <option value="6">6 adult</option>
                        </select>
                        

                        
                    </div>
                    <div class="box">
                        <p>adults<span>*</span></p>
                        <select name="child" class="input" required>
                            <option value="0">0 child</option>
                            <option value="1">1 child</option>
                            <option value="2">2 child</option>
                            <option value="3">3 child</option>
                            <option value="4">4 child</option>
                            <option value="5">5 child</option>
                            <option value="6">6 child</option>
                        </select>
                        

                        
                    </div>
                    <div class="box">
                        <p>adults<span>*</span></p>
                        <select name="rooms" class="input" required>
                            <option value="1">1 room</option>
                            <option value="2">2 rooms</option>
                            <option value="3">3 rooms</option>
                            <option value="4">4 rooms</option>
                            <option value="5">5 rooms</option>
                            <option value="6">6 rooms</option>
                        </select>
                        

                        
                    </div>
                </div>
                <input type="submit" value="check availability" name="check" class="btn">
                
                
            </form>

         </section>

         <!--availabilitysection ends-->
         <!--about section starts-->
         <section class="about" id="about">
            <div class="row">
                <div class="image">
                    <img src="images/about.jpg" alt="">

                </div>
                <div class="content">
                    <h3>best staff</h3>
                    <p>We are at your service. You are most welcome to join us. Have a wonderful stay</p>
                    <a href="#reservation" class="btn"> make a reservation</a>
                </div>
            </div>

            <div class="row revers">
                <div class="image">
                    <img src="images/about2.jpg" alt="">

                </div>
                <div class="content">
                    <h3>best foods</h3>
                    <p>We are at your service. You are most welcome to join us. Have a wonderful stay</p>
                    <a href="contact" class="btn"> contact us</a>
                </div>
            </div>
            <div class="row">
                <div class="image">
                    <img src="images/about3.jpg" alt="">

                </div>
                <div class="content">
                    <h3>swimming pool</h3>
                    <p>We are at your service. You are most welcome to join us. Have a wonderful stay</p>
                    <a href="#availability" class="btn"> check availability</a>
                </div>
            </div>

            
         </section>

         <!--about section ends-->

         <!--service section starts-->
         <section class="services">
            <div class="box-container">
                <div class="box">
                    <img src="images/1.png" alt="">
                    <h3>Food and drinks</h3>
                    <p>Zuri hostels has a restaurant to make it convenient to our customers</p>
                </div>
                <div class="box">
                    <img src="images/2.png" alt="">
                    <h3>chilling zone</h3>
                    <p> Our customers have a chill area to relax</p>
                </div>
                <div class="box">
                    <img src="images/3.png" alt="">
                    <h3>swimming</h3>
                    <p>A recreational activity </p>
                </div>
                
            </div>
         </section>



         <!--service section ends-->
         <!--reservation section starts-->
         <section class="reservation" id="reservation">
            
            <form action=""  method="post">
                <h3> Book your home now </h3>
                <div class="flex">
                    <div class="box">
                        <p>your name<span>*</span></p>
                        <input type="name" name="your name" class="input" required>

                        
                    </div>
                    <div class="flex">
                    <div class="box">
                        <p>your number<span>*</span></p>
                        <input type="number" name="your number" class="input" required>

                        
                    </div>
                    <div class="flex">
                    <div class="box">
                        <p>your email<span>*</span></p>
                        <input type="email" name="your email" class="input" required>

                        
                    </div>
                   

                <div class="flex">
                    <div class="box">
                        <p>check in<span>*</span></p>
                        <input type="date" name="check_in" class="input" required>

                        
                    </div>
                    <div class="box">
                        <p>check out<span>*</span></p>
                        <input type="date" name="check_out" class="input" required>
                    </div>
                    <div class="box">
                        <p>adults<span>*</span></p>
                        <select name="adults" class="input" required>
                            <option value="1">1 adult</option>
                            <option value="2">2 adult</option>
                            <option value="3">3 adult</option>
                            <option value="4">4 adult</option>
                            <option value="5">5 adult</option>
                            <option value="6">6 adult</option>
                        </select>
                        

                        
                    </div>
                    <div class="box">
                        <p>adults<span>*</span></p>
                        <select name="child" class="input" required>
                            <option value="0">0 child</option>
                            <option value="2">2 child</option>
                            <option value="3">3 child</option>
                            <option value="4">4 child</option>
                            <option value="5">5 child</option>
                            <option value="6">6 child</option>
                        </select>
                        

                        
                    </div>
                    <div class="box">
                        <p>rooms<span>*</span></p>
                        <select name="rooms" class="input" required>
                            <option value="1">1 room</option>
                            <option value="2">2 rooms</option>
                            <option value="3">3 rooms</option>
                            <option value="4">4 rooms</option>
                            <option value="5">5 rooms</option>
                            <option value="6">6 rooms</option>
                        </select>
                        

                        
                    </div>
                </div>
                <input type="submit" value="Book now" name="book" class="btn">
                
                
            </form>



         </section>
         <!--reservation section ends-->
         <!--gallery section starts-->
         <section class="gallery" id="gallery">

            
                <div class="swiper gallery-slider">
                    <div class="swiper-wrapper">

                    
        
                
                    <img src="images/gallery1.jpg" class="swiper-slide"  alt="">
                    <img src="images/gallery2.jpg" class="swiper-slide"  alt="">
                    <img src="images/home2.jpg" class="swiper-slide"  alt="">
                    
                </div>
         
                <div class="swiper-pagination"></div>

            </div>
        




         </section>
         <!--gallery section ends-->
         <!--contact section starts-->
         <section class="contact" id="contact">

            <div class="row">
                <form action="" method="post">
                    <h3>send us message</h3>
                    <input type="text" name="name" required maxlength="50" placeholder="enter your name" class="box">
                    <input type="email" name="email" required maxlength="50" placeholder="enter your email" class="box">
                    <input type="number" name="number" required maxlength="10" min="0" max="9999999999" placeholder="enter your number" class="box">
                    <textarea name="msg" class="box" required maxlenght="1000" placeholder="enter your message" cols="30" rows="10"></textarea>
                    <input type="submit" value="send message" name="send" class="btn">
                </form>
                
            </div>
         </section>
         <!--contact section ends-->
         <!--reviews section starts-->
         <section class="reviews" id="reviews">

            <div class="swiper reviews-slider">
                <div class="swiper-wrapper">
                    <div class="swiper-slide box">
                        <img src="images/c.jpg"alt="">
                        <h3>Anabelle Shan</h3>
                        <p> I had a wonderful stay and  cannot forget the customer servive, it was top notch</p>


                    </div>
                    
                        <div class="swiper-slide box">
                            <img src="images/c2.jpg"alt="">
                            <h3>Shanice</h3>
                            <p>Thankyou to zuri hostel for making my school ife extremely easy. I would definetely recommend</p>
    
    
                        </div>
                        
                            <div class="swiper-slide box">
                                <img src="images/c3.jpg"alt="">
                                <h3>Grace</h3>
                                <p>You people should definetely try and book a stay here, the service is topnotch</p>
        
        
                            </div>
                            
                            
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-pagination"></div>
                
            </div>





         </section>


        
         <!--reviews section ends-->
         <!-- mybookings section starts-->
    
<section class="mybookings" id="mybookings">
    <h2 class="heading">Your Bookings</h2>

    <div class="box-container">
        <?php
        // Assuming $conn is your database connection object

        // Fetch user bookings from the database
        $select_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ?");
        $select_bookings->bind_param("i", $user_id);
        $select_bookings->execute();
        $result = $select_bookings->get_result();

        if ($result->num_rows > 0) {
            while ($fetch_booking = $result->fetch_assoc()) {
                ?>
                <div class="box">
                    <p>Name: <span><?= $fetch_booking['name']; ?></span></p>
                    <p>Email: <span><?= $fetch_booking['email']; ?></span></p>
                    <!-- Add other booking details as needed -->
                    <p>Booking ID: <span><?= $fetch_booking['booking_id']; ?></span></p>
                    <form action="" method="POST">
                        <input type="hidden" name="booking_id" value="<?= $fetch_booking['booking_id']; ?>">
                        <input type="submit" value="Cancel Booking" name="cancel" class="btn" onclick="return confirm('Cancel this booking?');">
                    </form>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="box" style="text-align: center;">
                <p>No bookings found!</p>
            </div>
            <?php
        }
        ?>
    </div>
</section>


         
         <!--mybokings section ends-->


         
         <?php include 'components/footer.php';?>







         <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-sweetalert/1.1.2/SweetAlert.min.js"></script>




        <!--custom js file-->
        <script src="js/script.js"></script>


        <?php include 'components/message.php';?>










    </body>
    </html>