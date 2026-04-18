<?php
header('Content-Type: application/json');
include 'db_config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_reviews' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $query = "SELECT r.*, u.username FROM review r JOIN users u ON r.users_id = u.id WHERE r.products_id = $product_id ORDER BY r.created_at DESC";
    $result = mysqli_query($conn, $query);
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $reviews]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
