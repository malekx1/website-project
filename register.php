<?php
include "db_config.php";
session_start();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_btn'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters";
    } else {
        // Check if email exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        
        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Insert only the columns that exist in your table
            $insert = mysqli_prepare($conn, "INSERT INTO users (username, email, pwd) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($insert, "sss", $username, $email, $hashed_password);
            
            if (mysqli_stmt_execute($insert)) {
                $message = "Account created successfully! <a href='login.php'>Login here</a>";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($insert);
        }
        mysqli_stmt_close($check);
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
        <?php if($message): ?>
            <p style="color: #aaffaa; background: #004400; padding: 10px; border-radius: 10px;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if($error): ?>
            <p style="color: #ffaaaa; background: #440000; padding: 10px; border-radius: 10px;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="login-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password (min 4 chars)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="register_btn" class="btn">Register</button>
            <p style="margin-top:1rem">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </form>
</section>
</body>
</html>