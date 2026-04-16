
<?php

include "db_config.php";

session_start();

header('Content-Type: application/json');

// Handle CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
        
        // Fixed: Changed 'review' table to 'reviews' (based on your image schema)
        // Also fixed: Added proper table name and column references
        $query = "SELECT r.*, u.username FROM reviews r 
                  JOIN users u ON r.users_id = u.id 
                  WHERE r.products_id = ? 
                  ORDER BY r.created_at DESC";
        
        $stmt = mysqli_prepare($conn, $query);
        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $reviews = array();
            while($row = mysqli_fetch_assoc($result)){
                $reviews[] = $row;
            }
            
            echo json_encode(array('status' => 'success', 'data' => $reviews));
            mysqli_stmt_close($stmt);
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Database query failed: ' . mysqli_error($conn)));
        }
        break;
    
    case 'add_review':
        // Add a review (requires authentication)
        if(!isset($_SESSION['user_id'])){
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'User not authenticated'));
            break;
        }
        
        // Check for JSON input as well as form data
        $input_data = $_POST;
        if(empty($_POST) && file_get_contents('php://input')) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            if($json_data) {
                $input_data = $json_data;
            }
        }
        
        if(!isset($input_data['product_id']) || !isset($input_data['rating']) || !isset($input_data['review_text'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Missing required fields: product_id, rating, review_text'));
            break;
        }
        
        $user_id = $_SESSION['user_id'];
        $product_id = intval($input_data['product_id']);
        $rating = intval($input_data['rating']);
        $review_text = trim(mysqli_real_escape_string($conn, $input_data['review_text']));
        
        // Validate rating
        if($rating < 1 || $rating > 5){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Rating must be between 1 and 5'));
            break;
        }
        
        // Validate review text
        if(strlen($review_text) < 3){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Review text must be at least 3 characters'));
            break;
        }
        
        // Check if product exists - Fixed column name
        $check_query = "SELECT id FROM products WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        
        if(!$check_stmt) {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)));
            break;
        }
        
        mysqli_stmt_bind_param($check_stmt, "i", $product_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($result) === 0){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Product not found'));
            mysqli_stmt_close($check_stmt);
            break;
        }
        mysqli_stmt_close($check_stmt);
        
        // Check if user has already reviewed this product
        $check_review_query = "SELECT id FROM reviews WHERE users_id = ? AND products_id = ?";
        $check_review_stmt = mysqli_prepare($conn, $check_review_query);
        mysqli_stmt_bind_param($check_review_stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($check_review_stmt);
        $review_result = mysqli_stmt_get_result($check_review_stmt);
        
        if(mysqli_num_rows($review_result) > 0){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'You have already reviewed this product'));
            mysqli_stmt_close($check_review_stmt);
            break;
        }
        mysqli_stmt_close($check_review_stmt);
        
        // Insert review - Fixed table name to 'reviews'
        $insert_query = "INSERT INTO reviews (users_id, products_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        
        if(!$insert_stmt) {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)));
            break;
        }
        
        mysqli_stmt_bind_param($insert_stmt, "iiis", $user_id, $product_id, $rating, $review_text);
        
        if(mysqli_stmt_execute($insert_stmt)){
            $review_id = mysqli_insert_id($conn);
            echo json_encode(array(
                'status' => 'success', 
                'message' => 'Review added successfully',
                'review_id' => $review_id
            ));
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Failed to add review: ' . mysqli_error($conn)));
        }
        
        mysqli_stmt_close($insert_stmt);
        break;
    
    case 'delete_review':
        // Delete a review (admin or review owner only)
        if(!isset($_SESSION['user_id'])){
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'User not authenticated'));
            break;
        }
        
        if(!isset($_GET['review_id'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Review ID required'));
            break;
        }
        
        $review_id = intval($_GET['review_id']);
        $user_id = $_SESSION['user_id'];
        
        // Check if user is admin or review owner
        $check_query = "SELECT users_id FROM reviews WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $review_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($result) === 0){
            http_response_code(404);
            echo json_encode(array('status' => 'error', 'message' => 'Review not found'));
            mysqli_stmt_close($check_stmt);
            break;
        }
        
        $review_data = mysqli_fetch_assoc($result);
        $is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;
        
        if($review_data['users_id'] != $user_id && !$is_admin){
            http_response_code(403);
            echo json_encode(array('status' => 'error', 'message' => 'You do not have permission to delete this review'));
            mysqli_stmt_close($check_stmt);
            break;
        }
        mysqli_stmt_close($check_stmt);
        
        // Delete the review
        $delete_query = "DELETE FROM reviews WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $review_id);
        
        if(mysqli_stmt_execute($delete_stmt)){
            echo json_encode(array('status' => 'success', 'message' => 'Review deleted successfully'));
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Failed to delete review'));
        }
        
        mysqli_stmt_close($delete_stmt);
        break;
    
    case 'update_review':
        // Update a review (owner only)
        if(!isset($_SESSION['user_id'])){
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'User not authenticated'));
            break;
        }
        
        $input_data = $_POST;
        if(empty($_POST) && file_get_contents('php://input')) {
            $json_data = json_decode(file_get_contents('php://input'), true);
            if($json_data) {
                $input_data = $json_data;
            }
        }
        
        if(!isset($input_data['review_id']) || !isset($input_data['rating']) || !isset($input_data['review_text'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Missing required fields: review_id, rating, review_text'));
            break;
        }
        
        $review_id = intval($input_data['review_id']);
        $user_id = $_SESSION['user_id'];
        $rating = intval($input_data['rating']);
        $review_text = trim(mysqli_real_escape_string($conn, $input_data['review_text']));
        
        // Validate rating
        if($rating < 1 || $rating > 5){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Rating must be between 1 and 5'));
            break;
        }
        
        // Check ownership
        $check_query = "SELECT users_id FROM reviews WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $review_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($result) === 0){
            http_response_code(404);
            echo json_encode(array('status' => 'error', 'message' => 'Review not found'));
            mysqli_stmt_close($check_stmt);
            break;
        }
        
        $review_data = mysqli_fetch_assoc($result);
        if($review_data['users_id'] != $user_id){
            http_response_code(403);
            echo json_encode(array('status' => 'error', 'message' => 'You do not have permission to update this review'));
            mysqli_stmt_close($check_stmt);
            break;
        }
        mysqli_stmt_close($check_stmt);
        
        // Update review
        $update_query = "UPDATE reviews SET rating = ?, review = ?, created_at = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "isi", $rating, $review_text, $review_id);
        
        if(mysqli_stmt_execute($update_stmt)){
            echo json_encode(array('status' => 'success', 'message' => 'Review updated successfully'));
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update review'));
        }
        
        mysqli_stmt_close($update_stmt);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'Invalid action. Valid actions: get_reviews, add_review, delete_review, update_review'));
        break;
}

// Close database connection
if(isset($conn) && $conn) {
    mysqli_close($conn);
}

?>
