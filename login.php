<?php
session_start();
<<<<<<< HEAD
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
=======
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

>>>>>>> 629092c66f1d8e464ed0cdad86fd58a430acd849
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Ranesh Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<<<<<<< HEAD
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
=======
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
            
>>>>>>> 629092c66f1d8e464ed0cdad86fd58a430acd849
            <p style="margin-top:1rem">New user? <a href="register.php">Create account</a></p>
        </div>
    </form>
</section>
<<<<<<< HEAD
=======

>>>>>>> 629092c66f1d8e464ed0cdad86fd58a430acd849
</body>
</html>