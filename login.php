
<?php
session_start();
include "db_config.php";

// SAFETY CHECK: Only run if the button was actually clicked
if (isset($_POST['login_btn'])) {
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check database
    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Match the column name 'pwd' from your database
        if ($password === $user['pwd']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to profile
            header("Location: profile.php");
            exit();
        } else {
            header("Location: index.php?error=Incorrect Password");
            exit();
        }
    } else {
        header("Location: index.php?error=User Not Found");
        exit();
    }
} else {
    // If someone tries to visit login.php directly without clicking login
    header("Location: index.php");
    exit();
}
?>