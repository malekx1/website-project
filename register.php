<?php
session_start();
include "db_config.php"; 
include 'includes/header.php'; 

$message = "";

if (isset($_POST['register_btn'])) {
    // 1. Collect all three fields
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 2. Check if the email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Email already registered!";
    } else {
        // 3. Insert including the username column
        // Make sure your table has a column named 'username'
        $sql = "INSERT INTO users (username, email, pwd) VALUES ('$username', '$email', '$password')";
        
        if (mysqli_query($conn, $sql)) {
            // 4. Get the ID of the user we just created
            $new_user_id = mysqli_insert_id($conn);

            // 5. Start the session automatically
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['username'] = $username;

            // 6. Redirect straight to profile
            header("Location: profile.php");
            exit();
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Ranesh Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<section id="register">
    <form action="register.php" method="POST">
        <h2 class="section-title">📝 Create Account</h2>
        
        <?php if($message != ""): ?>
            <p style="color: yellow; background: #333; padding: 10px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <div class="login-form">
            <input type="text" name="username" placeholder="Choose Username" required>
            
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Create Password" required>
            
            <button type="submit" name="register_btn" class="btn">Register</button>
            
            <p style="margin-top:1rem">Already have an account? <a href="index.php#login">Login</a></p>
        </div>
    </form>
</section>

</body>
</html>