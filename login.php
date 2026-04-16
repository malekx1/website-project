<?php
session_start();
include "db_config.php"; 

$error_message = "";

// 1. PHP LOGIC - Only runs when the button is clicked
if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query using your specific columns: email and pwd
    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Plain text check to match your register.php
        if ($password === $user['pwd']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: profile.php");
            exit();
        } else {
            $error_message = "Incorrect password!";
        }
    } else {
        $error_message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Ranesh Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section id="register"> <form action="login.php" method="POST">
        <h2 class="section-title">🔑 Member Login</h2>
        
        <?php if($error_message != ""): ?>
            <p style="color: #ff4d4d; background: rgba(0,0,0,0.5); padding: 10px; border-radius: 5px; text-align: center;">
                <?php echo $error_message; ?>
            </p>
        <?php endif; ?>

        <div class="login-form">
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            
            <button type="submit" name="login_btn" class="btn">Login</button>
            
            <p style="margin-top:1rem">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </form>
</section>

</body>
</html>