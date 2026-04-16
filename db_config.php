<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db_name = "my_db";

// simple check
$conn = mysqli_connect($host, $user, $pass, $db_name , 3307);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character
mysqli_set_charset($conn, "utf8");
?>