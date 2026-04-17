<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "my_db";

// 1. Try connecting with 3306 first (Standard for both WAMP/XAMPP)
$conn = @mysqli_connect($host, $user, $pass, $db_name, 3306);

// 2. If 3306 fails, try 3307 (Teammate's special port)
if (!$conn) {
    $conn = mysqli_connect($host, $user, $pass, $db_name, 3307);
}

// 3. Final check
if (!$conn) {
    die("Database Connection failed. Please check if WAMP or XAMPP is running!");
}

mysqli_set_charset($conn, "utf8");
?>