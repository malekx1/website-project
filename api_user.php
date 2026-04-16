<?php

include "db_config.php";

session_start();

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Check authentication
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(array('status' => 'error', 'message' => 'User not authenticated'));
    exit;
}

$user_id = $_SESSION['user_id'];

switch($action){
    
    case 'get_profile':
        // Get user profile
        $query = "SELECT id, username, email, profile_pic, created_at FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($user = mysqli_fetch_assoc($result)){
            echo json_encode(array('status' => 'success', 'data' => $user));
        } else {
            http_response_code(404);
            echo json_encode(array('status' => 'error', 'message' => 'User not found'));
        }
        
        mysqli_stmt_close($stmt);
        break;
    
    case 'update_profile':
        // Update user profile
        $username = isset($_POST['username']) ? trim($_POST['username']) : null;
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        
        if(!$username && !$email){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'At least one field is required'));
            break;
        }
        
        if($username){
            $query = "UPDATE users SET username = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        if($email && filter_var($email, FILTER_VALIDATE_EMAIL)){
            // Check if email is already taken
            $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, "si", $email, $user_id);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);
            
            if(mysqli_num_rows($result) > 0){
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'message' => 'Email already in use'));
                mysqli_stmt_close($check_stmt);
                break;
            }
            
            $query = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_stmt_close($check_stmt);
        }
        
        echo json_encode(array('status' => 'success', 'message' => 'Profile updated successfully'));
        break;
    
    case 'change_password':
        // Change password
        if(!isset($_POST['old_password']) || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'All fields are required'));
            break;
        }
        
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($new_password !== $confirm_password){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'New passwords do not match'));
            break;
        }
        
        if(strlen($new_password) < 6){
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'Password must be at least 6 characters'));
            break;
        }
        
        // Get current password hash
        $query = "SELECT pwd FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        // Verify old password (using password_verify for hashed passwords)
        if(!password_verify($old_password, $user['pwd'])){
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'Current password is incorrect'));
            mysqli_stmt_close($stmt);
            break;
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET pwd = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);
        
        if(mysqli_stmt_execute($update_stmt)){
            echo json_encode(array('status' => 'success', 'message' => 'Password changed successfully'));
        } else {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => 'Failed to change password'));
        }
        
        mysqli_stmt_close($stmt);
        mysqli_stmt_close($update_stmt);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(array('status' => 'error', 'message' => 'Invalid action'));
        break;
}

?>
