<?php
header('Content-Type: application/json');
include '../db_config.php';

$sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url, p.video_url, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Build full image URL (adjust path if needed)
    $row['image_url'] = 'assets/images/' . $row['image_url'];
    $products[] = $row;
}
echo json_encode($products);
?>
