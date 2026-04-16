<?php
include "db_config.php";

$error_message = "";

if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['pwd']) || $password === $user['pwd']) {
            if ($password === $user['pwd']) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET pwd = '$hashed' WHERE id = {$user['id']}");
            }
            session_start();
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
<section id="register">
    <form action="login.php" method="POST">
        <h2 class="section-title">🔑 Login</h2>
        <?php if($error_message != ""): ?>
            <p style="color: #ff9999; background: #330000; padding: 10px; border-radius: 10px;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <div class="login-form">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login_btn" class="btn">Login</button>
            <p style="margin-top:1rem">New user? <a href="register.php">Create account</a></p>
        </div>
    </form>
</section>
</body>
</html>