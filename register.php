<?php
include "db_config.php"; // Using your connection file name

$message = "";

if (isset($_POST['register_btn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Check if the email already exists in the database
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Email already registered!";
    } else {
        // 2. Insert the new user into the 'user' table
        // We use 'pwd' to match your database column name
        $sql = "INSERT INTO users (email, pwd) VALUES ('$email', '$password')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Account created successfully! <a href='login.php'>Login here</a>";
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
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Create Password" required>
            
            <button type="submit" name="register_btn" class="btn">Register</button>
            
            <p style="margin-top:1rem">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </form>
</section>

</body>
</html>