<?php

include "db_config.php";

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action){

case 'get_all':

$query = "SELECT p.product_id, p.name, p.description, p.price, 
p.image_url, p.video_url, p.created_at,
c.category_name as category
FROM products p
LEFT JOIN categories c
ON p.category_id = c.category_id";

$result = mysqli_query($conn,$query);

$products = [];

while($row = mysqli_fetch_assoc($result)){
$products[] = $row;
}

echo json_encode([
"status"=>"success",
"data"=>$products
]);

break;

case 'get_by_category':

if(!isset($_GET['category'])){
echo json_encode(["status"=>"error","message"=>"Category required"]);
break;
}

$category = $_GET['category'];

$query = "SELECT p.product_id, p.name, p.description, p.price,
p.image_url, p.video_url, p.created_at,
c.category_name as category
FROM products p
JOIN categories c
ON p.category_id = c.category_id
WHERE c.category_name = ?";

$stmt = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param($stmt,"s",$category);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$products = [];

while($row = mysqli_fetch_assoc($result)){
$products[] = $row;
}

echo json_encode([
"status"=>"success",
"data"=>$products
]);

mysqli_stmt_close($stmt);

break;

case 'get_categories':

$query = "SELECT * FROM categories";

$result = mysqli_query($conn,$query);

$categories = [];

while($row = mysqli_fetch_assoc($result)){
$categories[] = $row;
}

echo json_encode([
"status"=>"success",
"data"=>$categories
]);

break;

case 'search':

if(!isset($_GET['q'])){
echo json_encode(["status"=>"error","message"=>"Search query required"]);
break;
}

$search = "%".$_GET['q']."%";

$query = "SELECT p.product_id, p.name, p.description, p.price,
p.image_url, p.video_url, p.created_at,
c.category_name as category
FROM products p
LEFT JOIN categories c
ON p.category_id = c.category_id
WHERE p.name LIKE ? OR p.description LIKE ?";

$stmt = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param($stmt,"ss",$search,$search);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$products = [];

while($row = mysqli_fetch_assoc($result)){
$products[] = $row;
}

echo json_encode([
"status"=>"success",
"data"=>$products
]);

mysqli_stmt_close($stmt);

break;

default:

http_response_code(400);

echo json_encode([
"status"=>"error",
"message"=>"Invalid action"
]);

}
