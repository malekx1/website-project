<?php
header('Content-Type: application/json');
include '../db_config.php';

$sql = "SELECT p.product_id, p.name, p.price, p.image_url, COUNT(l.products_id) as purchase_count
        FROM products p
        JOIN library l ON p.product_id = l.products_id
        GROUP BY p.product_id
        ORDER BY purchase_count DESC
        LIMIT 3";
$result = mysqli_query($conn, $sql);

$top = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['image_url'] = 'assets/images/' . $row['image_url'];
    $top[] = $row;
}
echo json_encode($top);
?>
