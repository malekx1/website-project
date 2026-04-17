<?php
error_reporting(0);
ini_set('display_errors', 0);

include "db_config.php";

header('Content-Type: application/json');

// Check connection
if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$query = "SELECT p.product_id, p.name, p.description, p.price, 
          p.image_url, p.video_url, p.created_at,
          c.category_name as category
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.category_id";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    exit;
}

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Clean name
    $row['name'] = trim(preg_replace('/\s+/', ' ', $row['name']));
    $products[] = $row;
}

echo json_encode(["status" => "success", "data" => $products]);
?>