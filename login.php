<?php

include "db_config.php";

$response = array();

if(isset($_POST['email']) && isset($_POST['password'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validate email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $response['status'] = 'error';
        $response['message'] = 'Invalid email format';
    } else {
        // Use prepared statement to prevent SQL injection
        $query = "SELECT id, username, pwd FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) > 0){
            $user = mysqli_fetch_assoc($result);
            // Verify password - supports both hashed (bcrypt) and plain text for backwards compatibility
            $password_match = password_verify($password, $user['pwd']) || ($password === $user['pwd']);
            if($password_match){
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $response['status'] = 'success';
                $response['message'] = 'Login successful';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Incorrect password';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Email not found';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Email and password required'));
}

?>

