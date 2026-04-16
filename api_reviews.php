<?php

include "db_config.php";

session_start();

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch($action){
    
    case 'get_reviews':
        // Get reviews for a product
        if(!isset($_GET['product_id'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Product ID required'));
            break;
        }
        
        $product_id = intval($_GET['product_id']);
        $query = "SELECT r.*, u.username FROM review r 
                  JOIN users u ON r.users_id = u.id 
                  WHERE r.products_id = ? 
                  ORDER BY r.created_at DESC";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $reviews = array();
        while($row = mysqli_fetch_assoc($result)){
            $reviews[] = $row;
        }
        
        echo json_encode(array('status' => 'success', 'data' => $reviews));
        mysqli_stmt_close($stmt);
        break;
    
    case 'add_review':
        // Add a review (requires authentication)
        if(!isset($_SESSION['user_id'])){
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'User not authenticated'));
            break;
        }
        
        if(!isset($_POST['product_id']) || !isset($_POST['rating']) || !isset($_POST['review_text'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Missing required fields'));
            break;
        }
        
        $user_id = $_SESSION['user_id'];
        $product_id = intval($_POST['product_id']);
        $rating = intval($_POST['rating']);
        $review_text = trim($_POST['review_text']);
        
        // Validate rating
        if($rating < 1 || $rating > 5){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Rating must be between 1 and 5'));
            break;
        }
        
        // Check if product exists
        $check_query = "SELECT id FROM products WHERE product_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $product_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($result) === 0){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Product not found'));
            mysqli_stmt_close($check_stmt);
            break;
        }
        
        // Insert review
        $insert_query = "INSERT INTO review (users_id, products_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "iiis", $user_id, $product_id, $rating, $review_text);
        
        if(mysqli_stmt_execute($insert_stmt)){
            echo json_encode(array('status' => 'success', 'message' => 'Review added successfully'));
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Failed to add review'));
        }
        
        mysqli_stmt_close($insert_stmt);
        mysqli_stmt_close($check_stmt);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'Invalid action'));
        break;
}

?>
