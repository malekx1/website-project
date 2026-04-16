<?php
include "db_config.php";

// Top selling based on library purchases (most purchased)
$query = "SELECT p.product_id, p.name, p.price, p.image_url, COUNT(l.order_id) as sales
          FROM products p
          LEFT JOIN library l ON p.product_id = l.products_id
          GROUP BY p.product_id
          ORDER BY sales DESC
          LIMIT 4";

$result = mysqli_query($conn, $query);
$topGames = [];

while($row = mysqli_fetch_assoc($result)){
    $topGames[] = $row;
}

echo json_encode($topGames);
?>