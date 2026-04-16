<?php

include "db_config.php";

session_start();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

// Check if user is logged in for protected actions
if(in_array($action, array('add_to_library', 'get_purchases')) && !isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(array('status' => 'error', 'message' => 'User not authenticated'));
    exit;
}

switch($action){
    
    case 'add_to_library':
        // Add purchase to library
        if(!isset($_POST['product_id']) || !isset($_POST['price'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Product ID and price required'));
            break;
        }
        
        $user_id = $_SESSION['user_id'];
        $product_id = intval($_POST['product_id']);
        $price = floatval($_POST['price']);
        
        // Insert into library
        $query = "INSERT INTO library (users_id, products_id, price, purchase_date) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iid", $user_id, $product_id, $price);
        
        if(mysqli_stmt_execute($stmt)){
            echo json_encode(array('status' => 'success', 'message' => 'Game added to library'));
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Failed to add to library'));
        }
        
        mysqli_stmt_close($stmt);
        break;
    
    case 'get_purchases':
        // Get user's library (purchases)
        $user_id = $_SESSION['user_id'];
        $query = "SELECT p.*, l.purchase_date, l.price FROM library l 
                  JOIN products p ON l.products_id = p.product_id 
                  WHERE l.users_id = ? 
                  ORDER BY l.purchase_date DESC";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $purchases = array();
        while($row = mysqli_fetch_assoc($result)){
            $purchases[] = $row;
        }
        
        echo json_encode(array('status' => 'success', 'data' => $purchases));
        mysqli_stmt_close($stmt);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'Invalid action'));
        break;
}

?>
