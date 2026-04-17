<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "my_db";

if ($_SERVER['SERVER_PORT'] == '8080') {
    $db_port = 3306; // Your port
} else {
    $db_port = 3307; // Your teammate's port
}

$conn = mysqli_connect($host, $user, $pass, $db_name, $db_port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>