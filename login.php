<?php
session_start();
include "db_config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = mysqli_prepare($conn, "SELECT id, username, pwd FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['pwd'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Wrong email or password";
        }
    } else {
        $error = "Wrong email or password";
    }
    mysqli_stmt_close($stmt);
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
<section id="login">
    <form action="login.php" method="post">
        <h2 class="section-title">🔐 Login</h2>
        <?php if($error): ?>
            <p style="color: #ff9999; background: #330000; padding: 10px; border-radius: 10px;"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="login-form">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
            <p style="margin-top:1rem">New user? <a href="register.php">Create account</a></p>
        </div>
    </form>
</section>
</body>
</html>