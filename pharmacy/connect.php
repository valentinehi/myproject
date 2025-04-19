<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'localhost';
$db_port = '3307'; //  MySQL server port
$db_name = 'login';
$db_user_name = 'root';
$db_user_pass = 'goodgirl';
$conn = new mysqli($db_host, $db_user_name, $db_user_pass, $db_name, $db_port);
if($conn->connect_error){
    echo "Failed to connect DB".$conn->connect_error;
}
?>