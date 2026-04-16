<?php
session_start();
include "db_config.php"; 

$error_message = "";

// FIX 1: This IF statement stops the orange "Undefined array key" errors.
// It tells PHP: "Only look for email/password IF the button was clicked."
if (isset($_POST['login_btn'])) {
    
    // FIX 2: Check if the keys actually exist in the POST array before using them
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : '';

    if (!empty($email) && !empty($password)) {
        // FIX 3: Query using your 'email' and 'pwd' columns
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Checking plain text password to match your registration
            if ($password === $user['pwd']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header("Location: profile.php");
                exit();
            } else {
                $error_message = "Wrong password!";
            }
        } else {
            $error_message = "No user found with that email.";
        }
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
<body class="aesthetic-profile">

<?php include 'includes/header.php'; ?>

<section id="register">
    <form action="login.php" method="POST">
        <h2 class="section-title">🔑 Login</h2>
        
        <?php if($error_message != ""): ?>
            <p style="color: #ff4d4d; background: rgba(0,0,0,0.6); padding: 10px; text-align: center; border-radius: 5px;">
                <?php echo $error_message; ?>
            </p>
        <?php endif; ?>

        <div class="login-form">
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            
            <button type="submit" name="login_btn" class="btn">Login</button>
            
            <p style="margin-top:1rem">New user? <a href="register.php">Create account</a></p>
        </div>
    </form>
</section>

</body>
</html>