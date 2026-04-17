<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "my_db";
$port = 3307;   // ← CRITICAL

$conn = mysqli_connect($host, $user, $pass, $db_name, $port);
if (!$conn) die("Connection failed: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");
?>