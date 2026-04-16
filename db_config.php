<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "my_db";

// Remove port 3307 – use default 3306 (XAMPP)
// Check if we are on your computer (8080) or theirs
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