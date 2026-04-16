
<?php

include "db_config.php";

session_start();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// check login
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode([
        "status"=>"error",
        "message"=>"User not authenticated"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

switch($action){

/* ================= GET PROFILE ================= */

case 'get_profile':

$query = "SELECT id, username, email, profile_pic, created_at 
          FROM users 
          WHERE id = ?";

$stmt = mysqli_prepare($conn,$query);
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if($user = mysqli_fetch_assoc($result)){

echo json_encode([
"status"=>"success",
"data"=>$user
]);

}else{

http_response_code(404);

echo json_encode([
"status"=>"error",
"message"=>"User not found"
]);

}

mysqli_stmt_close($stmt);

break;


/* ================= UPDATE PROFILE ================= */

case 'update_profile':

$username = $_POST['username'] ?? null;
$email = $_POST['email'] ?? null;

if(!$username && !$email){

http_response_code(400);

echo json_encode([
"status"=>"error",
"message"=>"Provide username or email"
]);

break;

}

/* update username */

if($username){

$query = "UPDATE users SET username=? WHERE id=?";

$stmt = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param($stmt,"si",$username,$user_id);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

/* update email */

if($email){

if(!filter_var($email,FILTER_VALIDATE_EMAIL)){

echo json_encode([
"status"=>"error",
"message"=>"Invalid email"
]);

break;

}

$query = "UPDATE users SET email=? WHERE id=?";

$stmt = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param($stmt,"si",$email,$user_id);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

echo json_encode([
"status"=>"success",
"message"=>"Profile updated"
]);

break;


/* ================= CHANGE PASSWORD ================= */

case 'change_password':

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if(!$old || !$new || !$confirm){

echo json_encode([
"status"=>"error",
"message"=>"All fields required"
]);

break;

}

if($new !== $confirm){

echo json_encode([
"status"=>"error",
"message"=>"Passwords do not match"
]);

break;

}

/* get current password */

$query = "SELECT pwd FROM users WHERE id=?";

$stmt = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

/* verify password */

if(!password_verify($old,$user['pwd'])){

echo json_encode([
"status"=>"error",
"message"=>"Wrong current password"
]);

break;

}

/* update password */

$hash = password_hash($new,PASSWORD_BCRYPT);

$query = "UPDATE users SET pwd=? WHERE id=?";

$stmt2 = mysqli_prepare($conn,$query);

mysqli_stmt_bind_param($stmt2,"si",$hash,$user_id);

mysqli_stmt_execute($stmt2);

echo json_encode([
"status"=>"success",
"message"=>"Password changed"
]);

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt2);

break;


/* ================= DEFAULT ================= */

default:

echo json_encode([
"status"=>"error",
"message"=>"Invalid action"
]);

}
?>
