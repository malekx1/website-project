<?php
session_start();
include '../db_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid cart data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$success = true;

foreach ($data['cart'] as $item) {
    $product_id = $item['id'];
    $price = $item['price'];
    $quantity = $item['quantity'];
    
    // Insert each item into library (or you could store quantity in library if you add a qty column)
    for ($i = 0; $i < $quantity; $i++) {
        $sql = "INSERT INTO library (users_id, products_id, price, purchase_date) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iid", $user_id, $product_id, $price);
        if (!mysqli_stmt_execute($stmt)) {
            $success = false;
        }
        mysqli_stmt_close($stmt);
    }
}

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Purchase completed!']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
