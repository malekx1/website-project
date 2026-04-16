<?php

include "db_config.php";

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action){
    
    case 'get_all':
        // Get all products
        $query = "SELECT p.*, c.category_name as category FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
        $result = mysqli_query($conn, $query);
        $products = array();
        
        while($row = mysqli_fetch_assoc($result)){
            $products[] = $row;
        }
        
        echo json_encode(array('status' => 'success', 'data' => $products));
        break;
    
    case 'get_by_category':
        // Get products by category
        if(!isset($_GET['category']) || $_GET['category'] === 'all'){
            $query = "SELECT p.*, c.category_name as category FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
        } else {
            $category = $_GET['category'];
            $query = "SELECT p.*, c.category_name as category FROM products p JOIN categories c ON p.category_id = c.category_id WHERE c.category_name = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $category);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $products = array();
            
            while($row = mysqli_fetch_assoc($result)){
                $products[] = $row;
            }
            
            echo json_encode(array('status' => 'success', 'data' => $products));
            break;
        }
        
        $result = mysqli_query($conn, $query);
        $products = array();
        
        while($row = mysqli_fetch_assoc($result)){
            $products[] = $row;
        }
        
        echo json_encode(array('status' => 'success', 'data' => $products));
        break;
    
    case 'get_categories':
        // Get all categories
        $query = "SELECT * FROM categories";
        $result = mysqli_query($conn, $query);
        $categories = array();
        
        while($row = mysqli_fetch_assoc($result)){
            $categories[] = $row;
        }
        
        echo json_encode(array('status' => 'success', 'data' => $categories));
        break;
    
    case 'search':
        // Search products by name or description
        if(!isset($_GET['q'])){
            echo json_encode(array('status' => 'error', 'message' => 'Search query required'));
            break;
        }
        
        $search = '%' . $_GET['q'] . '%';
        $query = "SELECT p.*, c.category_name as category FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.name LIKE ? OR p.description LIKE ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $search, $search);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $products = array();
        
        while($row = mysqli_fetch_assoc($result)){
            $products[] = $row;
        }
        
        echo json_encode(array('status' => 'success', 'data' => $products));
        mysqli_stmt_close($stmt);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'Invalid action'));
        break;
}

?>
