<?php
 
 
  $db_host = 'localhost';
  $db_port = '3307'; // Change this to your MySQL server port
  $db_name = 'hotel_db';
  $db_user_name = 'root';
  $db_user_pass = 'goodgirl';

  // Create connection
  $conn = new mysqli($db_host, $db_user_name, $db_user_pass, $db_name, $db_port);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  echo "Connected successfully";



function create_unique_id(){
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $rand = array();
    $length = strlen($str) - 1;


    for($i = 0; $i < 20; $i++){
        $n = mt_rand(0, $length);
        $rand[] = $str[$n];

    }
    return implode($rand);



}







?>