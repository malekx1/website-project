<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "my_db";

// Remove port 3307 – use default 3306 (XAMPP)
$conn = mysqli_connect($host, $user, $pass, $db_name, 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>