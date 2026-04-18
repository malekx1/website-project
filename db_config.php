<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "my_db";

// Try common ports
$ports = [3306, 3307];
$conn = null;
foreach ($ports as $port) {
    $conn = @mysqli_connect($host, $user, $pass, $db_name, $port);
    if ($conn) break;
}
if (!$conn) {
    die("Database Connection failed. Please start MySQL (XAMPP/WAMP).");
}
mysqli_set_charset($conn, "utf8");
?>