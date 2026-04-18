<?php
header('Content-Type: application/json');
include '../db_config.php';

$query = "SELECT p.product_id, p.name, p.description, p.price, 
          p.image_url, p.video_url, p.created_at,
          c.category_name as category
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.category_id";

$result = mysqli_query($conn, $query);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['name'] = trim(preg_replace('/\s+/', ' ', $row['name']));
    $products[] = $row;
}
echo json_encode(["status" => "success", "data" => $products]);
?>